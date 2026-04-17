(function ($) {
    document.addEventListener('DOMContentLoaded', function () {
        var el = document.getElementById('addetional-app');
        if (!el) return;

        var addetionalApp = new Vue({
            el: '#addetional-app',
            data: {
                additionals: [],
            },
            mounted() {
                var vm = this;

                $('#addetional-app').on('click', '.save-period', function () {
                    let row = $(this).closest('tr');
                    let additionalId = row.data('id');

                    let data = {
                        name: row.find('input[name="name"]').val(),
                        price: row.find('input[name="price"]').val(),
                        calculation_type: row.find('select[name="calculation_type"]').val(),
                        count: row.find('input[name="count"]').val(),
                        _token: $('meta[name="csrf-token"]').attr('content')
                    };

                    let url = additionalId ? '/additionals/' + additionalId + '/update' : '/additionals/store';

                    $.ajax({
                        url: url,
                        type: 'POST',
                        dataType: 'json',
                        data: data,
                        success: function (res) {

                            if (res.status) {
                                if (!additionalId && res.html) {
                                    row.replaceWith(res.html);
                                }
                            }
                        },
                        error: function (xhr) {
                            if (xhr.status === 422) {
                                let errors = xhr.responseJSON.errors;

                                let messages = Object.values(errors)
                                    .flat()
                                    .join('\n');

                                bookingCoreApp.showError({ message: messages });
                                return;
                            }
                        }
                    });
                });

                $('#addetional-app').on('click', '.remove-period', function () {
                    let btn = $(this);
                    let row = btn.closest('tr');
                    let additionalId = row.data('id');
                    let name = row.find('input[name="name"]').val();
                    if (name === 'Питание') {
                        bookingCoreApp.showAjaxMessage({
                            status: false,
                            message: 'Эту услугу удалить нельзя'
                        });
                        return;
                    }

                    if (!additionalId) {
                        row.remove();
                        return;
                    }

                    bookingCoreApp.showConfirm({
                        message: 'Вы уверены, что хотите удалить услугу?',
                        callback: (result) => {
                            if (!result) return;

                            $.ajax({
                                url: '/additionals/' + additionalId,
                                type: 'DELETE',
                                dataType: 'json',
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: (res) => {
                                    if (res.status) {
                                        window.location.reload();
                                    }
                                },
                                error: function (e) {
                                     if (e.responseJSON && e.responseJSON.message) {
                                         bookingCoreApp.showError({ message: e.responseJSON.message });
                                    }
                                }
                            });
                        }
                    });
                });
            },

            methods: {
                addAdditional() {
                    let tbody = $('#addetional-app table tbody');
                    let newRow = `
                        <tr data-id="">
                            <td><input type="text" name="name" class="form-control" value=""></td>
                            <td><input type="number" name="price" step="0.01" class="form-control" value="0"></td>
                            <td class="text-center">
                                <button class="btn btn-success btn-sm save-period" data-id="">
                                    Сохранить
                                </button>
                                <button class="btn btn-danger btn-sm remove-period" data-id="">
                                    Удалить
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.append(newRow);
                }
            }

        });
    });
})(jQuery);
