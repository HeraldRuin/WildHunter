
window.FormBuilderEventBus = createApp({})

const FIELD_TYPES = [
    { id: 'input', 'label': 'Input', 'icon':'fa fa-input'},
    { id: 'textarea', 'label': 'Textarea', 'icon':'fa fa-textarea'},
    { id: 'select', 'label': 'Select', 'icon':'fa fa-select'},
    { id: 'radio', 'label': 'Radio', 'icon':'fa fa-radio'},
    { id: 'checkbox', 'label': 'Checkbox', 'icon':'fa fa-checkbox'},
    { id: 'section', 'label': 'Section', 'icon':'fa fa-section'},
]

window.VisaFormBuiderApp = createApp({
    data: ()=>({
        items:{
            ROOT:{
                nodes:[]
            }
        },
        types:FIELD_TYPES,
    }),
    created () {
        window.FormBuilderEventBus.$on('add-node', (parentId, type) => {
            this.addNode(parentId, type);
        })
    },
    methods:{
        addNode(parentId, type){
            parentId = parentId || 'ROOT';
            type = type || 'input';
            const parent = this.items[parentId];
            const mayAddNode = ['section'].includes(parent.type) || parentId === 'ROOT';
            // Only add node if parent is section or parentId is ROOT
            if (!parent || !mayAddNode) {
                return;
            }

            const newId = this.makeId();
            const node = {
                parentId,
                type,
            }
            this.items[newId] = node;
            this.items[parentId].nodes.push(newId);
        },
        makeId: function(length) {
            length = length || 10;
            let result = '';
            const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            const charactersLength = characters.length;
            let counter = 0;
            while (counter < length) {
                result += characters.charAt(Math.floor(Math.random() * charactersLength));
                counter += 1;
            }
            return result.toLowerCase();
        },
    }
}).mount('#VisaFormBuider')

window.VisaFormBuiderApp.component('form-node', {
    props: ['node','tree'],
    data: function(){
        return {
            types:FIELD_TYPES,
        }
    },
    template: '#form-node-template',
    methods:{
        addNode(){
            window.FormBuilderEventBus.$emit('add-node', this.node.id);
        }
    },
    computed:{
        typeObject(){
            return this.types.find(type => type.id === this.node.type);
        }
    }

})