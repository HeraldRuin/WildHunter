import { createApp } from "vue";
import Form from "./components/form.vue";
import LayerItem from "./components/layers/item.vue";
import draggable from "vuedraggable";
import mitt from "mitt";
import VueFormGenerator from "@/libs/vue-form-generator/src/formGenerator.vue";
import {install} from "../custom-fields"

window.LiveEditorEventBus = mitt();

window.LiveEditor = createApp({
  data: () => ({
    items: current_template_items,
    blocks: [],
    message: {
      content: "",
      type: false,
    },
    onSaving: false,
    s: "",
    selectedBlockId: "",
    frame: null,
    showAddBlock: false,
    lastSaved: current_last_saved,
    addBlockForId: null, // id of parent block
    model:{},
    options:{},
    schema: [
        {
          "id": "title",
          "type": "input",
          "inputType": "text",
          "label": "Title",
          "adminLabel": true,
          "model": "title"
        },
      ]
  }),
  mounted() {
    this.reloadBlocks();

    this.$nextTick(() => {
      window.addEventListener("message", (messageStr) => {
        try {
          const message = JSON.parse(messageStr.data);
          if (message?.action) {
            switch (message?.action) {
              case "select-item":
                this.selectItemFromFrame(message?.data.id);
                break;
            case "show-add-layer":
              this.showAddBlock = true;
              break;
            }
          }
        } catch (error) {
          console.log(error);
        }
      });

      this.sendToFrame({
        action: "set_items",
        data: this.items,
      });
    });
    window.LiveEditorEventBus.on("delete-item", (data) => {
      this.deleteItem(data);
    });
    window.LiveEditorEventBus.on("show-add-block", (data) => {
      this.showAddBlock = true;
      this.addBlockForId = data?.id;
    });

    // allow children to trigger event to add block
    window.LiveEditorEventBus.on("add-block", (data) => {
      if (Boolean(data?.children?.length) && data.to) {
        data?.children?.forEach((item) => {
          // clone the bloc
          this.addBlockTo(data.to, { ...item });
        });
      }
    });
    window.LiveEditorEventBus.on("select-block", (id) => {
      this.selectBlock(id);
    });
  },
  computed: {
    filteredBlocks: function () {
      const res = {};
      if (!this.s) return this.blocks;

      Object.entries(this.blocks).forEach(([groupId, group]) => {
        res[groupId] = Object.assign({}, group);
        res[groupId].items =
          group.items.filter((item) => {
            return item.name.toLowerCase().includes(this.s.toLowerCase());
          }) || [];
      });
      return res;
    },
    blocksMapById() {
      let res = {};
      Object.entries(this.blocks).forEach(([, group]) => {
        group.items.forEach((item) => {
          res[item.id] = item;
        });
      });
      return res;
    },
    currentModel() {
      return this.items[this.selectedBlockId].model ?? {};
    },
    currentBlockSetting() {
      return this.blocksMapById[this.items[this.selectedBlockId].type] ?? {};
    },
  },
  watch: {
    currentModel(val) {
      console.log(val)
    },
  },
  methods: {
    sendToFrame(data) {
      const iframeEl = document.getElementById("frame-preview");
      if (iframeEl && iframeEl.contentWindow) {
        iframeEl.contentWindow.postMessage(JSON.stringify(data), "*");
      }
    },
    reloadBlocks() {
      var me = this;

      jQuery.ajax({
        url: bookingCore.admin_url + "/module/template/getBlocks",
        dataType: "json",
        type: "get",
        success: function (res) {
          if (res.status) {
            me.blocks = res.data;
          }
        },
        error: function (e) {
          console.log(e);
        },
      });
    },
    selectBlock(id) {
      this.selectedBlockId = id;
      this.sendToFrame({
        action: "select-item",
        data: id,
      });
    },
    selectItemFromFrame(id) {
      this.selectedBlockId = id;
    },
    saveTemplate() {
      var me = this;

      this.onSaving = true;

      $.ajax({
        url: bookingCore.admin_url + "/module/template/store",
        dataType: "json",
        type: "post",
        data: {
          id: template_id,
          content: JSON.stringify(this.items),
          title: this.title,
          lang: current_menu_lang,
        },
        success: function (res) {
          me.onSaving = false;
          me.lastSaved = res.lastSaved;
          if (res.message) {
            me.message.content = res.message;
            me.message.type = res.status;
          }
          if (res.url) {
            window.location.href = res.url;
          }

          window.setTimeout(() => {
            me.message.content = "";
          }, 3000);
        },
        error: function (e) {
          me.onSaving = false;

          if (e.responseJSON.message) {
            me.message.content = e.responseJSON.message;
            me.message.type = false;
          } else {
            me.message.content = "Can not save menu";
            me.message.type = false;
          }
        },
      });
    },
    cancelEdit() {
      this.selectedBlockId = "";
    },
    saveBlock(model) {
      if (this.selectedBlockId && this.items[this.selectedBlockId]) {
        this.items[this.selectedBlockId].model = model;
        this.sendToFrame({
          action: "save_block",
          data: {
            id: this.selectedBlockId,
            model,
            tree: this.getTreeForNodeId(this.selectedBlockId),
          },
        });
        this.saveTemplate();
      }
    },
    sortEnd(val) {
      this.saveTemplate();
      this.sendToFrame({
        action: "sort-end",
        data: val.moved,
      });
    },
    deleteItem(data) {
      const itemId = data.id;
      const itemObj = this.items[itemId];
      if (typeof itemObj !== "undefined") {
        this.selectedBlockId = "";

        const parentNodes = this.items[itemObj.parent].nodes;
        if (typeof parentNodes !== "undefined" && parentNodes.indexOf(itemId) !== -1) {
          this.items[itemObj.parent].nodes.splice(parentNodes.indexOf(itemId), 1);
        }
        delete this.items[itemId];

        this.sendToFrame({
          action: "delete-item",
          data,
        });
        this.saveTemplate();
      }
    },
    addBlock(block) {
      this.addBlockTo(this.addBlockForId || "ROOT", block);
    },
    addBlockTo(parentId, block, options) {
      const newId = this.makeid(20);

      // Make sure that block exists
      // Find a again by type
      const findBlock = this.blocksMapById[block.id];
      if (!findBlock) {
        console.log("No findBlock", findBlock, block);
        return;
      }
      // Clone to make sure no issue
      const blockParams = this.getBlockParams({ ...findBlock });

      blockParams.parent = parentId;
      if (!this.items[parentId]) {
        console.log("No parentId" + parentId, findBlock, block);
        return;
      }
      // make sure block.nodes is not empty
      this.items[parentId].nodes = this.items[parentId].nodes || [];

      this.items[parentId].nodes.push(newId);
      this.items[newId] = blockParams;

      if (parentId !== "ROOT") {
        // trigger update event to preview iframe with no new props to parent
        // so that parent will show new child
        this.sendToFrame({
          action: "save_block",
          data: {
            id: parentId,
            model: {},
            tree: this.getTreeForNodeId(this.parentId),
          },
        });
      } else {
        // Trigger add block event
        this.sendToFrame({
          action: "add_block",
          data: {
            id: newId,
            model: block.model,
            blockType: block.id,
          },
        });
      }

      // Support preset_children
      // Eg section will have some default columns
      if (Boolean(block.preset_children?.length)) {
        // add each items to newId
        // NOTE: Only support 1 level
        // TODO: Support for nested preset_children
        block.preset_children.forEach((preset) => {
          const newBlock = { ...preset }; // Clone preset

          this.addBlockTo(newId, newBlock, {
            noActiveBlock: false, // this flag mean we dont want to auto select this block after add it
          });
        });
      }

      this.$nextTick(() => {
        // Check if there is no flag
        // If parent is not ROOT, we will auto select block
        if (!Boolean(options?.noActiveBlock) && parentId !== "ROOT") {
          this.selectBlock(newId);
        }
        this.showAddBlock = false;
      });
    },
    getBlockParams(block) {
      let res = {
        type: block.id,
        name: block.name,
        model: block.model,
        component: block.component,
        open: true,
      };

      if (block.is_container) {
        res.is_container = true;
        res.children = [];
      }

      return res;
    },
    makeid(length) {
      let result = "";
      const characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
      const charactersLength = characters.length;
      let counter = 0;
      while (counter < length) {
        result += characters.charAt(Math.floor(Math.random() * charactersLength));
        counter += 1;
      }
      return result.toLowerCase();
    },
    // Return tree data related to node id
    getTreeForNodeId(nodeId, level = 0) {
      let res = {};
      if (level === 0 && this.items[nodeId]) {
        // add current item
        res[nodeId] = this.items[nodeId];
      }
      if (Boolean(this.items[nodeId]?.nodes)) {
        this.items[nodeId].nodes.forEach((childNodeId) => {
          if (this.items[childNodeId]) {
            res[childNodeId] = this.items[childNodeId];
          }

          // Support nested
          res = { ...res, ...this.getTreeForNodeId(childNodeId, level + 1) };
        });
      }
      return res;
    },
  },
  components: {
    BlockForm: Form,
    LayerItem,
    draggable,
    VueFormGenerator
  },
});

window.LiveEditor.mount("#live-editor");

install(window.LiveEditor);
