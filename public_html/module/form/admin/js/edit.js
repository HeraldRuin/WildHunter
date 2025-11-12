function BC_Form_Edit() {
    let self;
    return {
        fields:[],
        selectedFieldId:null,
        init: function() {
            self = this;
        },
        getShortId: function() {
            return Math.random().toString(16).slice(2)
        },
        handleSort: (item,position)=>{

            // NOTE: Can't use this here, dont know why
            // So use "self" as a hack
            if(item.isNew) {
                const itemObject = {
                    ...item,
                    id: self.getShortId(),
                    label: item.name
                }

                delete itemObject.isNew; // Remove the isNew property

                // push item at position
                // This is adding new item at position
                self.fields.splice(position, 0, itemObject);
            } else {
                // TODO: update item at position
            }
        },
        addField: (item)=>{
            const itemObject = {
                ...item,
                id: self.getShortId(),
                label: item.name
            }

            self.fields.push(itemObject);
        }
    }
}