(function ($) {
    var hotelAnimalForm = new Vue({
        el:'#animal-app',
        data:{
            id:'',
            periods: [],
            animalIdToAttach: ''
        },
        mounted() {
            var vm = this;

            $('#animal-app').on('click', '.remove-period', function() {
                let periodId = $(this).data('id');

                if (!confirm('Хотите удалить период?')) return;

                $.ajax({
                    url: '/animal/period/' + periodId,
                    type: 'post',
                    dataType: 'json',
                    data: { _token: $('meta[name="csrf-token"]').attr('content') },
                    success: function(res) {
                        if(res.status){
                            $(this).closest('tr').remove();
                        }
                    }.bind(this)
                });
            });

            $('#animal-app').on('click', '.save-period', function() {
                let periodId = $(this).data('id');
                let row = $(this).closest('tr');

                let data = {
                    start_date: row.find('input[name="start_date"]').val(),
                    end_date: row.find('input[name="end_date"]').val(),
                    amount: row.find('input[name="amount"]').val(),
                    _token: $('meta[name="csrf-token"]').attr('content')
                };

                $.ajax({
                    url: '/animal/period/' + periodId + '/update',
                    type: 'post',
                    dataType: 'json',
                    data: data,
                    success: function(res) {
                        if(res.status){
                            if(res.message){
                                bookingCoreApp.showAjaxMessage(res);
                            }
                        }
                    },
                });
            });
        },

        methods: {
            addPeriod(animalId, url) {
                $.ajax({
                    url: url,
                    data: {
                        animal_id: animalId,
                    },
                    dataType:'json',
                    type:'post',
                    success: function (res) {
                        if (res.status && res.html) {
                            $('#periods-' + res.animal_id).append(res.html);
                        }
                    },
                })
            },
            attachAnimal() {
                if (!this.animalIdToAttach) return;

                let url = $('#animal-app').data('bulk-url');
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'add',
                        animal_id: this.animalIdToAttach
                    },
                    success: function(res) {
                        if(res.success){
                            this.animalIdToAttach = '';
                            location.reload(); // или динамически обновить список
                        }
                    }.bind(this),
                    error: function(err) {
                        console.error(err);
                    }
                });
            }


        }

    });

})(jQuery);
