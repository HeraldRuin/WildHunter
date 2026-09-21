<template>
  <div class="blog-text-editor">
    <Editor
      v-model="content"
      :init="editorInit"
      :tinymce-script-src="tinymceSrc"
    />
  </div>
</template>

<script>
import Editor from "@tinymce/tinymce-vue";

export default {
  name: "TextBlockEditor",
  components: { Editor },
  props: {
    modelValue: {
      type: String,
      default: "",
    },
  },
  emits: ["update:modelValue"],
  computed: {
    content: {
      get() {
        return this.modelValue;
      },
      set(value) {
        this.$emit("update:modelValue", value);
      },
    },
    tinymceSrc() {
      if (typeof bookingCore !== "undefined" && bookingCore.url) {
        return bookingCore.url + "/libs/tinymce/js/tinymce/tinymce.min.js";
      }
      return "/libs/tinymce/js/tinymce/tinymce.min.js";
    },
    editorInit() {
      const lang =
        typeof bookingCore !== "undefined" ? bookingCore.language : "ru";

      return {
        language: lang,
        menubar: false,
        plugins:
          "preview searchreplace autolink code fullscreen image link media codesample table charmap hr toc advlist lists wordcount textpattern help",
        toolbar:
          "formatselect | bold italic strikethrough forecolor backcolor | link image media table | alignleft aligncenter alignright alignjustify | numlist bullist outdent indent | removeformat code",
        image_advtab: false,
        image_caption: true,
        relative_urls: false,
        remove_script_host: true,
        height: 420,
        branding: false,
        promotion: false,
        file_picker_callback: (callback, value, meta) => {
          if (meta.filetype === "file") {
            uploaderModal.show({
              multiple: false,
              file_type: "video",
              onSelect(files) {
                if (files.length) callback(files[0].full_size);
              },
            });
          }

          if (meta.filetype === "image") {
            uploaderModal.show({
              multiple: false,
              file_type: "image",
              onSelect(files) {
                if (files.length) callback(files[0].full_size);
              },
            });
          }

          if (meta.filetype === "media") {
            uploaderModal.show({
              multiple: false,
              file_type: "video",
              onSelect(files) {
                if (files.length) callback(files[0].full_size);
              },
            });
          }
        },
      };
    },
  },
};
</script>
