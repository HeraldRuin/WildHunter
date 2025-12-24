new Vue({
    el: '#weapon-app',
    data: {
        weapons: window.initialWeapons.length
            ? window.initialWeapons
            : [{
                hunter_license_number: '',
                hunter_license_date: '',
                weapon_type_id: '',
                caliber: ''
            }]
    },
    computed: {
        hasUnsavedWeapon() {
            return this.weapons.some(w => !w.id);
        }
    },
    methods: {
        addNewRow() {
            this.weapons.push({
                hunter_license_number: '',
                hunter_license_date: '',
                weapon_type_id: '',
                caliber: ''
            });
        },
        removeWeapon(id) {
            var me  = this;
            const index = this.weapons.findIndex(w => w.id === id);

            if(index !== -1) {
                this.weapons.splice(index, 1);
            }
            const url = `/vendor/weapons/${id}`;
            $.ajax({
                url: url,
                data:{
                    weapon_id:id,
                },
                method:'post',
                success:function (json) {
                    // me.onLoadAvailability = false;
                    // me.firstLoad = false;
                    if(json.rooms){
                        me.rooms = json.rooms;
                        me.$nextTick(function () {
                            me.initJs();
                        })
                    }
                    if(json.message){
                        bookingCoreApp.showAjaxMessage(json);
                    }
                },
                error:function (e) {
                    me.firstLoad = false;
                    bookingCoreApp.showAjaxError(e);
                }
            })
        },
        cancelLastWeapon() {
            for (let i = this.weapons.length - 1; i >= 0; i--) {
                if (!this.weapons[i].id) {
                    this.weapons.splice(i, 1);
                    break;
                }
            }
        },
    }
});
