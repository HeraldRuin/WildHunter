import { createApp } from "vue";
import draggable from "vuedraggable";
import TextBlockEditor from "./components/TextBlockEditor.vue";
import "../scss/blog-editor.scss";

function makeId() {
  return "blk_" + Math.random().toString(36).substring(2, 11);
}

function normalizeBlock(block) {
  if (block.type === "image" && !block.settings) {
    block.settings = { width: "100%", align: "center" };
  }
  if (block.type === "table" && !block.settings) {
    block.settings = { headerRow: true };
  }
  if (block.type === "table" && !block.rows) {
    block.rows = [["", ""]];
  }
  return block;
}

function defaultBlock(type) {
  const id = makeId();
  switch (type) {
    case "text":
      return { id, type: "text", content: "<p></p>" };
    case "image":
      return {
        id,
        type: "image",
        image_id: null,
        image_url: null,
        caption: "",
        settings: { width: "100%", align: "center" },
      };
    case "table":
      return {
        id,
        type: "table",
        rows: [
          ["", ""],
          ["", ""],
        ],
        settings: { headerRow: true },
      };
    default:
      return { id, type: "text", content: "" };
  }
}

const app = createApp({
  components: { draggable, TextBlockEditor },
  data() {
    const contentJson = blogEditorData.content_json || { blocks: [] };
    return {
      blogId: blogEditorData.id,
      title: blogEditorData.title || "",
      slug: blogEditorData.slug || "",
      status: blogEditorData.status || "draft",
      image_id: blogEditorData.image_id,
      coverUrl: blogEditorData.cover_url || null,
      excerpt: blogEditorData.excerpt || "",
      blocks: (contentJson.blocks || []).map((b) => normalizeBlock(b)),
      selectedBlockId: null,
      saving: false,
      lastSaved: blogEditorData.last_saved || "",
      message: { content: "", type: false },
    };
  },
  computed: {
    selectedBlock() {
      return this.blocks.find((b) => b.id === this.selectedBlockId) || null;
    },
  },
  mounted() {
    if (!this.coverUrl && this.image_id) {
      this.loadCoverUrl(this.image_id);
    }
  },
  methods: {
    loadCoverUrl(id) {
      $.ajax({
        url: bookingCore.media.get_file + "?id=" + id,
        dataType: "json",
        success: (json) => {
          if (json.id) {
            this.coverUrl = json.thumb_size || json.max_large_size;
          }
        },
      });
    },
    pickCover() {
      uploaderModal.show({
        multiple: false,
        file_type: "image",
        onSelect: (files) => {
          if (files.length) {
            this.image_id = files[0].id;
            this.coverUrl = files[0].thumb_size || files[0].max_large_size;
          }
        },
      });
    },
    clearCover() {
      this.image_id = null;
      this.coverUrl = null;
    },
    blockIcon(type) {
      const icons = {
        text: "fa fa-font",
        image: "fa fa-image",
        table: "fa fa-table",
      };
      return icons[type] || "fa fa-cube";
    },
    blockLabel(block) {
      const labels = {
        text: blogEditorI18n.text,
        image: blogEditorI18n.image,
        table: blogEditorI18n.table,
      };
      const base = labels[block.type] || block.type;
      if (block.type === "text" && block.content) {
        const text = block.content.replace(/<[^>]+>/g, "").trim();
        if (text) return base + ": " + text.substring(0, 30);
      }
      if (block.type === "image" && block.caption) {
        return base + ": " + block.caption.substring(0, 30);
      }
      return base;
    },
    selectBlock(id) {
      this.selectedBlockId = id;
    },
    addBlock(type) {
      const block = defaultBlock(type);
      this.blocks.push(block);
      this.selectedBlockId = block.id;
    },
    insertBlockAfter(index) {
      const block = defaultBlock("text");
      this.blocks.splice(index + 1, 0, block);
      this.selectedBlockId = block.id;
    },
    deleteBlock(index) {
      const block = this.blocks[index];
      this.blocks.splice(index, 1);
      if (this.selectedBlockId === block.id) {
        this.selectedBlockId = null;
      }
    },
    onSort() {
      // blocks reordered via v-model
    },
    pickImage() {
      const block = this.selectedBlock;
      if (!block || block.type !== "image") return;

      uploaderModal.show({
        multiple: false,
        file_type: "image",
        onSelect: (files) => {
          if (files.length) {
            block.image_id = files[0].id;
            block.image_url = files[0].thumb_size || files[0].max_large_size;
          }
        },
      });
    },
    clearImage() {
      const block = this.selectedBlock;
      if (!block || block.type !== "image") return;
      block.image_id = null;
      block.image_url = null;
    },
    setTableRows(count) {
      const block = this.selectedBlock;
      if (!block || block.type !== "table") return;
      count = Math.max(1, Math.min(50, parseInt(count) || 1));
      const cols = block.rows[0]?.length || 2;
      while (block.rows.length < count) {
        block.rows.push(Array(cols).fill(""));
      }
      while (block.rows.length > count) {
        block.rows.pop();
      }
    },
    setTableCols(count) {
      const block = this.selectedBlock;
      if (!block || block.type !== "table") return;
      count = Math.max(1, Math.min(20, parseInt(count) || 2));
      block.rows.forEach((row, ri) => {
        while (row.length < count) row.push("");
        while (row.length > count) row.pop();
      });
    },
    showMessage(text, success) {
      this.message = { content: text, type: success };
      setTimeout(() => {
        this.message = { content: "", type: false };
      }, 3000);
    },
    flushTextEditors() {
      if (typeof tinymce === "undefined" || !tinymce.editors) return;
      tinymce.editors.forEach((editor) => editor.save());
    },
    async save() {
      this.flushTextEditors();
      this.saving = true;
      try {
        const body = new FormData();
        body.append("_token", blogEditorData.csrf_token);
        body.append("title", this.title);
        body.append("slug", this.slug);
        body.append("status", this.status);
        body.append("image_id", this.image_id || "");
        body.append("excerpt", this.excerpt);
        body.append(
          "content_json",
          JSON.stringify({ blocks: this.blocks })
        );

        const res = await fetch(blogEditorData.save_url, {
          method: "POST",
          body,
          headers: { Accept: "application/json" },
        });

        const data = await res.json();
        if (data.success) {
          this.showMessage(data.message || blogEditorI18n.saved, true);
          this.lastSaved = new Date().toLocaleString();
          if (!this.blogId && data.id) {
            this.blogId = data.id;
            if (data.save_url) {
              blogEditorData.save_url = data.save_url;
            }
            if (data.url) {
              window.history.replaceState({}, "", data.url);
            }
          }
        } else {
          this.showMessage(data.message || blogEditorI18n.error, false);
        }
      } catch (e) {
        this.showMessage(blogEditorI18n.error, false);
      } finally {
        this.saving = false;
      }
    },
  },
});

app.mount("#blog-editor");
