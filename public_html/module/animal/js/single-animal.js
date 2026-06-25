(function ($) {
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('animal-app');
        if (!el) return;

        const hotelAnimalForm = new Vue({
            el: '#animal-app',
            data: {
                id: '',
                periods: [],
                animalIdToAttach: ''
            },
            mounted() {
                $('#animal-app').on('click', '.remove-period', function () {
                    let periodId = $(this).data('id');

                    bookingCoreApp.showConfirm({
                        message: 'Вы удаляете период. Продолжить?',
                        callback: (result) => {
                            if (!result) return;

                            $.ajax({
                                url: '/animal/period/' + periodId,
                                type: 'post',
                                dataType: 'json',
                                data: {_token: $('meta[name="csrf-token"]').attr('content')},
                                success: function (res) {
                                    if (res.success) {
                                        $(this).closest('tr').remove();
                                    }
                                }.bind(this)
                            });
                        }
                    });
                });

                $('#animal-app').on('click', '.save-period', function () {
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
                        success: function (res) {
                            if (res.success) {
                                if (res.message) {
                                    bookingCoreApp.showAjaxMessage(res);
                                }
                            }
                        },
                        error: function (e) {
                            bookingCoreApp.showAjaxError(e);
                        }
                    });
                });
            },

            methods: {
                addPeriod(animalId, url) {
                    postRequest(url, {
                        animal_id: animalId,
                    })
                        .then((res) => {
                            if (res?.html) {
                                $('#periods-' + animalId).append(res.html);
                            }
                        });
                },
                attachAnimal() {
                    if (!this.animalIdToAttach) return;
                    let url = $('#animal-app').data('bulk-url');

                    postRequest(url, {
                        action: 'add',
                        animal_id: this.animalIdToAttach
                    })
                        .then((res) => {
                            this.animalIdToAttach = '';
                            location.reload();
                        });
                }
            }
        });
    });

})(jQuery);
