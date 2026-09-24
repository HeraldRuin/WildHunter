import { createApp } from "vue";
import draggable from "vuedraggable";
import TextBlockEditor from "./components/TextBlockEditor.vue";
import "../scss/blog-editor.scss";

function makeId() {
  return "blk_" + Math.random().toString(36).substring(2, 11);
}

function emptyColumn() {
  return { id: makeId(), blocks: [] };
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
  if (block.type === "columns") {
    if (!block.settings) {
      block.settings = { count: 2, ratio: "50-50" };
    }
    if (!block.settings.ratio) {
      block.settings.ratio = block.settings.count === 3 ? "33-33-33" : "50-50";
    }
    if (!Array.isArray(block.columns) || !block.columns.length) {
      const count = block.settings.count === 3 ? 3 : 2;
      block.columns = Array.from({ length: count }, emptyColumn);
    }
    block.columns.forEach((col) => {
      col.blocks = (col.blocks || []).map((child) => normalizeBlock(child));
    });
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
    case "columns":
      return {
        id,
        type: "columns",
        settings: { count: 2, ratio: "50-50" },
        columns: [emptyColumn(), emptyColumn()],
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
      return this.findBlockById(this.selectedBlockId);
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
    findBlockById(id, list) {
      if (!id) return null;
      const items = list || this.blocks;
      for (const block of items) {
        if (block.id === id) return block;
        if (block.type === "columns") {
          for (const col of block.columns || []) {
            const found = this.findBlockById(id, col.blocks || []);
            if (found) return found;
          }
        }
      }
      return null;
    },
    blockIcon(type) {
      const icons = {
        text: "fa fa-font",
        image: "fa fa-image",
        table: "fa fa-table",
        columns: "fa fa-columns",
      };
      return icons[type] || "fa fa-cube";
    },
    blockLabel(block) {
      const labels = {
        text: blogEditorI18n.text,
        image: blogEditorI18n.image,
        table: blogEditorI18n.table,
        columns: blogEditorI18n.columns_layout,
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
    addBlockToColumn(parent, colIndex, type) {
      if (!parent || parent.type !== "columns") return;
      const col = parent.columns[colIndex];
      if (!col) return;
      const block = defaultBlock(type);
      col.blocks.push(block);
      this.selectedBlockId = block.id;
    },
    deleteNestedBlock(parent, colIndex, childId) {
      if (!parent || parent.type !== "columns") return;
      const col = parent.columns[colIndex];
      if (!col) return;
      const i = col.blocks.findIndex((b) => b.id === childId);
      if (i < 0) return;
      col.blocks.splice(i, 1);
      if (this.selectedBlockId === childId) {
        this.selectedBlockId = parent.id;
      }
    },
    setColumnCount(count) {
      const block = this.selectedBlock;
      if (!block || block.type !== "columns") return;
      count = Math.max(2, Math.min(4, parseInt(count, 10) || 2));
      block.settings.count = count;
      while (block.columns.length < count) {
        block.columns.push(emptyColumn());
      }
      while (block.columns.length > count) {
        const removed = block.columns.pop();
        const last = block.columns[block.columns.length - 1];
        if (last && removed?.blocks?.length) {
          last.blocks.push(...removed.blocks);
        }
      }
      if (count === 2 && (block.settings.ratio === "33-33-33" || block.settings.ratio === "25-25-25-25")) {
        block.settings.ratio = "50-50";
      } else if (count === 3) {
        block.settings.ratio = "33-33-33";
      } else if (count === 4) {
        block.settings.ratio = "25-25-25-25";
      }
    },
    addColumn() {
      const block = this.selectedBlock;
      if (!block || block.type !== "columns") return;
      this.setColumnCount((block.columns?.length || 2) + 1);
    },
    removeColumn() {
      const block = this.selectedBlock;
      if (!block || block.type !== "columns") return;
      this.setColumnCount((block.columns?.length || 2) - 1);
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
