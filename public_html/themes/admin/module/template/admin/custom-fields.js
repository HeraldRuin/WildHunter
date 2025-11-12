import fieldEditor from "./fields/field-editor.vue";
import fieldSelect2 from "./fields/field-select2.vue";
import fieldListItem from "./fields/field-listItem.vue";
import fieldUpload from "./fields/field-upload.vue";
import fieldRadio from "./fields/field-radio-images.vue";
import fieldSpacing from './fields/field-spacing.vue';

export function install(app){
        app.component("fieldEditor", fieldEditor);
        app.component("fieldSelect2", fieldSelect2);
        app.component("fieldListItem", fieldListItem);
        app.component("fieldUploader", fieldUpload);
        app.component("fieldRadioImages", fieldRadio);
        app.component('fieldSpacing', fieldSpacing);
    }
