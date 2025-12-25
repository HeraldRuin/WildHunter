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
        },
        isFormValid() {
            return this.weapons.every(w =>
                w.hunter_license_number &&
                w.hunter_license_date &&
                w.weapon_type_id &&
                w.caliber
            );
        }
    },
    methods: {
        isWeaponTouched(w) {
            return w.hunter_license_number ||
                w.hunter_license_date ||
                w.weapon_type_id ||
                w.caliber;
        },

        isWeaponValid(w) {
            return w.hunter_license_number &&
                w.hunter_license_date &&
                w.weapon_type_id &&
                w.caliber;
        },
        beforeSubmit() {

            // берём только те оружия, которые начали заполнять
            const touchedWeapons = this.weapons.filter(w => this.isWeaponTouched(w));

            // если есть начатые, но невалидные — блокируем
            const hasInvalid = touchedWeapons.some(w => !this.isWeaponValid(w));

            if (hasInvalid) {
                alert('Если вы добавили оружие — заполните все его поля');
                return;
            }

            // всё ок — отправляем форму
            this.$el.querySelector('form').submit();
        },
        addNewRow() {
            const hasInvalid = this.weapons.some(w => !this.isWeaponValid(w));

            if (hasInvalid) {
                alert('Заполните все поля текущего оружия');
                return;
            }

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
