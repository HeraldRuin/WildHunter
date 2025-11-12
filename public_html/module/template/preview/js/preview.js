import '../scss/live-preview.scss';

window.LivePreview = new Vue({
    data: {
        message: {
            content: '',
            type: false
        },
        onSaving: false,
        s: '',
        selectedBlockId: ''
    },
    watch: {
        selectedBlockId(newId) {
            // add selected class when state changed
            const elements = document.getElementsByClassName('live-block-preview');
            for (let i = 0; i < elements.length; i++) {
                elements[i].classList.remove('selected');
            }
            const nodeId = newId.replace('.', '')
            document.getElementById('block-' + nodeId).classList.add('selected');
        }
    },
    methods: {
        init() {
            this.$nextTick(() => {
                window.addEventListener('message', msgObject => {
                    let message = null;
                    try{
                        message = JSON.parse(msgObject.data);
                    }catch(e){
                        console.log(e);
                        return;
                    }
                    // ...

                    console.log(message);
                    if (message?.action) {
                        switch (message?.action) {
                            case "set_items":
                                this.setItems(message?.data)
                                break;
                            case "select-item":
                                const nodeId = message.data.replace('.', '')
                                $('body,html').animate({
                                    scrollTop: $("#block-" + nodeId).offset().top
                                }, 'fast');
                                this.selectedBlockId = message.data;
                                break;
                            case "save_block":
                                this.updateBlock(message.data.id, message.data.model, message.data.tree)
                                break;
                            case 'sort-end':
                                this.sortEndFromParent(message.data)
                                break;
                            case 'delete-item':
                                this.deleteItemFromParent(message.data)
                                break;
                            case 'add_block':
                                this.addItemFromParent(message.data.id, message.data.model, message.data.blockType)
                                break;
                        }
                    }
                });

            })
        },
        setItems(items) {
            this[items] = items;
        },
        selectItem(id) {
            this.selectedBlockId = id;
            this.sendToParent({
                action: 'select-item',
                data: {
                    id
                }
            })
        },
        updateBlock(id, model, tree) {
            const nodeId = id.replace('.', '')

            // save data to livewire and trigger component refresh
            const livewireId = $('#block-' + nodeId).attr('wire:id');

            const find = Livewire.find(livewireId);
            if (find) {
                // Set component data
                if (Boolean(Object.keys(model)?.length)) {
                    for (const key in model) {
                        find.$set(key, model[key], false); // False for not live update, we trigger re-render after all
                    }
                }
                // Then call preview method with tree data
                // We need tree for nested re-render
                find.preview(tree)
            }

        },
        sortEndFromParent(data) {
            const itemId = data.element;
            const movedTo = data.newIndex;
            const moveFrom = data.oldIndex;

            const parent = this.items.ROOT.nodes;
            parent.splice(moveFrom, 1);
            parent.splice(movedTo, 0, itemId);

            this.items.ROOT.nodes = parent;
        },
        deleteItemFromParent(data) {
            const itemId = data.id.replace('.', '');
            
            document.getElementById(`block-${itemId}`).remove();
        },
        addItemFromParent(id, model, blockType) {
            // Call a preview method
            $.ajax({
                url: bookingCore.admin_url + '/module/template/live/block-preview',
                dataType: 'json',
                type: 'post',
                data: {
                    block: blockType,
                    model: model,
                    nodeId: id
                },
                success: (res) => {
                   if(res.preview){
                       // append to body
                       $('.page-template-content').append(res.preview);

                       // scroll to node after 100ms
                       setTimeout(() => {
                           this.scrollToItem(id);
                       }, 100);
                   }
                },
                error: (e) => {
                    console.log(e);
                }
            })

        },
        scrollToItem(id) {
            const nodeId = id.replace('.', '')
            $('body,html').animate({
                scrollTop: document.getElementById("block-" + nodeId).offsetTop
            }, 'fast')
        },
        showAddLayer(){
            this.sendToParent({
                action: 'show-add-layer'
            })
        },
        sendToParent(data){
            window.parent.postMessage(JSON.stringify(data), "*");
        }

    }
})

window.LivePreview.init();


$('a').on('click', function (e) {
    e.preventDefault();
})
