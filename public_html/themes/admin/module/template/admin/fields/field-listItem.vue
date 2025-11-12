<template>
    <div class="bravo-template-list-item b-grow">
        <div class="list-item-wraps">
            <draggable v-model="value" :options="{handle:'.handle'}">
                <template #item="{element: item, index: k}">
                    <div class="list-item">
                        <div class="list-item-header handle">
                            <span v-if="title_field && typeof item[title_field] != 'undefined' && item[title_field]">{{item[title_field]}}</span>
                            <span v-else >#{{k + 1}}</span>

                            <span >
                                <span @click="item._active = item._active == false ? true : false ">
                                    <i v-show="item._active" class="icon ion-ios-arrow-dropdown"></i>
                                    <i v-show="!item._active" class="icon ion-ios-arrow-dropright"></i>
                                </span>
                                <span @click="deleteItem(k)">
                                    <i class="icon ion-ios-trash"></i>
                                </span>
                            </span>
                        </div>
                        <div class="list-item-settings" v-show="item._active">
                            <vue-form-generator :schema="{fields:schema.settings}" :model="item" ></vue-form-generator>
                        </div>
                    </div>
                </template>
            </draggable>
        </div>
        <span class="btn btn-primary btn-sm" @click="addNew">{{template_i18n.add_new}}</span>
    </div>
</template>
<script>
    import abstractField from "@/libs/vue-form-generator/src/fields/abstractField";
    import VueFormGenerator from '@/libs/vue-form-generator/src/formGenerator.vue';
    import draggable from 'vuedraggable';

    export default {
        mixins: [ abstractField ],
        data(){
            return {
                options:[],
                fakeModel:{
                    _active:false
                },
                template_i18n:template_i18n,
            }
        },
        computed:{
            title_field(){
                if(typeof this.schema.title_field =='undefined') return false;
                return this.schema.title_field;
            }
        },
        created: function () {
            for(var i = 0; i < this.schema.settings.length ; i++){
                this.schema.settings[i].model = this.schema.settings[i].id;
                this.fakeModel[this.schema.settings[i].id] = null;
            }
        },
        methods:{
            addNew(){
                // this.fakeModel['_index'] =
                this.value.push(Object.assign({},this.fakeModel));
            },
            deleteItem(k){
                var c = confirm(this.template_i18n.delete_confirm);

                if(c){
                    this.value.splice(k,1);
                }
            }
        },
        components: {
            "vue-form-generator": VueFormGenerator,
            draggable:draggable
        },
    };
</script>