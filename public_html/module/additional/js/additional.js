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
                        _token: $('meta[name="csrf-token"]').attr('content')
                    };

                    let url = additionalId ? '/additionals/' + additionalId + '/update' : '/additionals/store';

                    $.post(url, data, function(res) {
                        if (res.status) {
                            if (!additionalId && res.html) {
                                row.replaceWith(res.html);
                            }
                        } else {
                            alert(res.message || 'Ошибка при сохранении');
                        }
                    }, 'json');
                });

                $('#addetional-app').on('click', '.remove-period', function () {
                    let btn = $(this);
                    let row = btn.closest('tr');
                    let additionalId = row.data('id');
                    let name = row.find('input[name="name"]').val();
                    if (name === 'Питание') {
                        alert('Эту услугу удалить нельзя.');
                        return;
                    }

                    if (!additionalId) {
                        row.remove();
                        return;
                    }

                    if (!confirm('Вы точно хотите удалить услугу?')) return;

                    $.post('/additionals/' + additionalId + '/delete', {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    }, function(res){
                        if (res.success) {
                            row.remove();
                        } else {
                            alert(res.message || 'Ошибка при удалении');
                        }
                    }, 'json');
                });
            },

            methods: {
                // ----------------------------
                // Добавление новой услуги
                // ----------------------------
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
