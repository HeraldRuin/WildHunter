$(document).ready(function () {
    $('[id^="bookingAddServiceModal"]').on('show.bs.modal', function () {
        const bookingId = $(this).attr('id').replace('bookingAddServiceModal', '');
        const block = $('#trophies-block-' + bookingId);
        const trophiesList = block.find('.trophies-list');

        trophiesList.empty();

        $.get(`/booking/${bookingId}/saved-services`, function (data) {
            const savedTrophies = data.trophies || [];

            if (!savedTrophies.length) return;

            // Добавляем заголовки, если их ещё нет
            if (trophiesList.find('.trophy-header').length === 0) {
                const headerHtml = `
            <div class="d-flex fw-bold mb-2 trophy-header">
                <span class="flex-fill">Животное</span>
                <span class="flex-fill">Тип</span>
                <span class="flex-fill">Количество</span>
            </div>`;
                trophiesList.append(headerHtml);
            }

            savedTrophies.forEach(t => {
                const html = `
<div class="d-flex align-items-center gap-2 mb-2 trophy-item"
     data-service-id="${t.id}"
     style="border: 2px solid #6c757d; padding: 8px; border-radius: 6px; background-color: #fff;">
    <span class="flex-fill animal-name">${t.animal}</span>
    <span class="flex-fill type-name">${t.type}</span>
    <span class="flex-fill count-span">${t.count}</span>
    <button type="button" class="btn btn-sm btn-outline-danger remove-trophy-btn">x</button>
</div>
`;
                trophiesList.append(html);
            });
        });
        if (!block.data('loaded')) {
            $.get(`/booking/${bookingId}/trophy-services`, function (data) {
                block.data('allTrophies', data.trophies || []);
                block.data('loaded', true);
            });
        }
    });


    // Открытие списка всех трофеев при нажатии «Добавить»
    $(document).on('click', '.add-trophy-btn', function (e) {
        e.stopPropagation(); // чтобы клик не закрывал сразу оверлей
        const block = $(this).closest('.service-block');
        const overlay = block.find('.all-trophies-overlay');
        const allTrophies = block.data('allTrophies') || [];

        if (!allTrophies.length) {
            alert('Сначала подождите, пока список трофеев загрузится');
            return;
        }

        overlay.empty();

        allTrophies.forEach((t, index) => {
            overlay.append(`<div class="trophy-item-select p-2 mb-2 rounded" data-index="${index}">
            <strong>Животное:</strong> ${t.animal} &nbsp;
            <strong>Тип трофея:</strong> ${t.type}
        </div>`);
        });

        // Показываем и позиционируем
        overlay.css({
            display: 'block',
            top: $(this).position().top + $(this).outerHeight() + 5,
            left: $(this).position().left,
            width: '400px'  // ширина оверлея
        });

        overlay.find('.trophy-item-select').hover(
            function () {
                $(this).css('background-color', '#e2e6ea');
            },
            function () {
                $(this).css('background-color', '');
            }
        ).css('cursor', 'pointer');
    });

// Скрываем оверлей при клике вне блока
    $(document).on('click', function (e) {
        $('.all-trophies-overlay').hide();
    });


// Выбор записи из списка
    $(document).on('click', '.trophy-item-select', function () {
        const block = $(this).closest('.service-block');
        const index = $(this).data('index');
        const allTrophies = block.data('allTrophies') || [];
        const trophy = allTrophies[index];

        const trophiesList = block.find('.trophies-list');

        // Если заголовки ещё не добавлены, добавляем их
        if (trophiesList.find('.trophy-header').length === 0) {
            const headerHtml = `
            <div class="d-flex fw-bold mb-2 trophy-header">
                <span class="flex-fill">Животное</span>
                <span class="flex-fill">Тип</span>
                <span class="flex-fill">Количество</span>
            </div>`;
            trophiesList.append(headerHtml);
        }

        // Проверяем, есть ли уже такой трофей в списке
        let existing = null;
        trophiesList.find('.trophy-item').each(function () {
            const animal = $(this).find('.animal-name').text();
            const type = $(this).find('.type-name').text();
            if (animal === trophy.animal && type === trophy.type) {
                existing = $(this);
                return false; // прерываем each
            }
        });

        if (existing) {
            // Если есть — увеличиваем количество на 1
            const countSpan = existing.find('.count-span');
            const currentCount = parseInt(countSpan.text());
            countSpan.text(currentCount + 1);
        } else {
            // Если нет — добавляем новый блок
            const html = `
            <div class="d-flex align-items-center gap-2 mb-2 trophy-item" 
                 style="border: 2px solid #6c757d; padding: 8px; border-radius: 6px; background-color: #fff;">
                <span class="flex-fill animal-name">${trophy.animal}</span>
                <span class="flex-fill type-name">${trophy.type}</span>
                <span class="flex-fill count-span">${trophy.count}</span>
                <button type="button" class="btn btn-sm btn-outline-danger remove-trophy-btn">x</button>
            </div>`;
            trophiesList.append(html);
        }

        // Скрываем список выбора
        block.find('.all-trophies-list').remove();

        saveService(block, 'trophy', function (block) {
            return [{
                service_id: trophy.id,
                animal: trophy.animal,
                type: trophy.type,
                count: 1
            }];
        });
    });

    function saveService(block, serviceType, itemsBuilder) {
        const bookingId = block.attr('id').match(/\d+/)[0];

        const items = itemsBuilder(block);

        if (!items.length) return;

        $.ajax({
            url: `/booking/${bookingId}/save-services`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                service_type: serviceType,
                items: items
            },
            success(response) {
                items.forEach((item, index) => {
                    const htmlItem = block.find('.trophy-item').eq(index);
                    htmlItem.attr('data-service-id', response.items[index].id);
                });
            },
            error(err) {
                console.error(`Ошибка сохранения ${serviceType}:`, err);
            }
        });
    }

// Удаление трофея
    $(document).on('click', '.remove-trophy-btn', function () {
        const item = $(this).closest('.trophy-item');
        const serviceId = item.data('service-id'); // <- теперь должно работать
        const block = $(this).closest('.service-block');
        const bookingId = block.attr('id').match(/\d+/)[0];

        // Удаляем визуально
        item.remove();

        // Удаляем с бэка
        $.ajax({
            url: `/booking/${bookingId}/service/${serviceId}`,
            method: 'DELETE',
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success() {
                console.log(`Сервис с ID ${serviceId} удалён`);
            },
            error(err) {
                console.error('Ошибка удаления сервиса:', err);
            }
        });
    });


    // Штрафы
    $(document).ready(function () {

        $('[id^="bookingAddServiceModal"]').on('show.bs.modal', function () {
            const bookingId = $(this).attr('id').replace('bookingAddServiceModal', '');

            const penaltiesBlock = $('#penalties-block-' + bookingId);
            const penaltiesList = penaltiesBlock.find('.penalties-list');

            penaltiesList.empty();

            // Получаем сохранённые штрафы
            $.get(`/booking/${bookingId}/saved-services`, function (data) {
                const savedPenalties = data.penalties || [];
                if (savedPenalties.length) {
                    if (!penaltiesList.find('.penalty-header').length) {
                        penaltiesList.append(`
                        <div class="d-flex fw-bold mb-2 penalty-header">
                            <span class="flex-fill">Животное</span>
                            <span class="flex-fill">Тип штрафа</span>
                            <span class="flex-fill">Охотник</span>
                        </div>
                    `);
                    }
                    savedPenalties.forEach(p => {
                        penaltiesList.append(`
                        <div class="d-flex align-items-center gap-2 mb-2 penalty-item"
                             data-service-id="${p.id}"
                             style="border:2px solid #6c757d; padding:8px; border-radius:6px; background:#fff;">
                            <span class="flex-fill animal-name">${p.animal}</span>
                            <span class="flex-fill type-name">${p.type}</span>
                            <span class="flex-fill hunter-name">${p.hunter}</span>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-penalty-btn">x</button>
                        </div>
                    `);
                    });
                }
            });

            // Загружаем все штрафы для выбора (оверлей)
            if (!penaltiesBlock.data('loaded')) {
                $.get(`/booking/${bookingId}/penalty-services`, function (data) {
                    penaltiesBlock.data('allPenalties', data.penalties || []);
                    penaltiesBlock.data('loaded', true);
                });
            }

        });

        // --- Оверлей для добавления ---
        function showOverlay(block, allItems, overlaySelector, itemClass, fields) {
            const overlay = block.find(overlaySelector);
            overlay.empty();

            allItems.forEach((item, index) => {
                let html = `<div class="${itemClass} p-2 mb-2 rounded" data-index="${index}">`;
                fields.forEach(f => {
                    html += `<strong>${f.label}:</strong> ${item[f.key]} &nbsp;`;
                });
                html += `</div>`;
                overlay.append(html);
            });

            overlay.css({
                display: 'block',
                top: block.find('.btn-outline-primary').position().top + 30,
                left: block.find('.btn-outline-primary').position().left,
                width: '400px'
            });
            overlay.find(`.${itemClass}`).hover(
                function () { $(this).css('background-color', '#e2e6ea'); },
                function () { $(this).css('background-color', ''); }
            ).css('cursor', 'pointer');
        }

        // --- Кнопка "Добавить штраф" ---
        $(document).on('click', '.add-penalty-btn', function (e) {
            e.stopPropagation();
            const bookingId = $(this).data('booking');
            const block = $('#penalties-block-' + bookingId);
            showOverlay(block, block.data('allPenalties') || [], '.all-penalties-overlay', 'penalty-item-select', [
                {label: 'Животное', key: 'animal'},
                {label: 'Тип штрафа', key: 'type'},
            ]);
        });

        // --- Выбор штрафа из оверлея ---
        $(document).on('click', '.penalty-item-select', function () {
            const block = $(this).closest('.service-block');
            const index = $(this).data('index');
            const penalty = block.data('allPenalties')[index];
            const list = block.find('.penalties-list');

            if (!list.find('.penalty-header').length) {
                list.append('<div class="d-flex fw-bold mb-2 penalty-header"><span class="flex-fill">Животное</span><span class="flex-fill">Тип штрафа</span></div>');
            }

            // Проверяем, есть ли уже такой штраф
            let existing = null;
            list.find('.penalty-item').each(function () {
                if ($(this).find('.animal-name').text() === penalty.animal &&
                    $(this).find('.type-name').text() === penalty.type &&
                    $(this).find('.hunter-name').text() === penalty.hunter) {
                    existing = $(this);
                    return false;
                }
            });

            if (existing) {
                // Если есть — увеличиваем количество
                let span = existing.find('.count-span');
                if (!span.length) {
                    existing.append('<span class="flex-fill count-span">1</span>');
                    span = existing.find('.count-span');
                }
                span.text(parseInt(span.text()) + 1);
            } else {
                list.append(`
                <div class="d-flex align-items-center gap-2 mb-2 penalty-item"
                     style="border:2px solid #6c757d; padding:8px; border-radius:6px; background:#fff;">
                    <span class="flex-fill animal-name">${penalty.animal}</span>
                    <span class="flex-fill type-name">${penalty.type}</span>
                    <span class="flex-fill hunter-name">${penalty.hunter}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-penalty-btn">x</button>
                </div>
            `);
            }

            block.find('.all-penalties-overlay').hide();

            savePenalty(block, penalty);
        });

        // --- Сохранение штрафа ---
        function savePenalty(block, penalty) {
            const bookingId = block.attr('id').match(/\d+/)[0];

            $.ajax({
                url: `/booking/${bookingId}/save-services`,
                method: 'POST',
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                data: {
                    service_type: 'penalty',
                    items: [{
                        service_id: penalty.id,
                        animal: penalty.animal,
                        type: penalty.type,
                        hunter: penalty.hunter,
                        count: 1
                    }]
                },
                success(response) {
                    // Устанавливаем правильный ID из базы
                    const lastItem = block.find('.penalty-item').last();
                    if (response.items && response.items[0]) {
                        lastItem.attr('data-service-id', response.items[0].id);
                    }
                },
                error(err) {
                    console.error('Ошибка сохранения штрафа:', err);
                }
            });
        }

        // --- Удаление штрафа ---
        $(document).on('click', '.remove-penalty-btn', function () {
            const item = $(this).closest('.penalty-item');
            const serviceId = item.data('service-id');

            if (!serviceId) {
                alert('Сначала сохраните штраф перед удалением!');
                return;
            }

            const bookingId = item.closest('.service-block').attr('id').match(/\d+/)[0];
            item.remove();

            $.ajax({
                url: `/booking/${bookingId}/service/${serviceId}`,
                method: 'DELETE',
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                success() { console.log(`Штраф с ID ${serviceId} удалён`); },
                error(err) { console.error('Ошибка удаления штрафа:', err); }
            });
        });

        // --- Скрытие оверлея при клике вне ---
        $(document).on('click', function () {
            $('.all-penalties-overlay').hide();
        });

    });


    // Разделка

    $(document).ready(function () {

        $('[id^="bookingAddServiceModal"]').on('show.bs.modal', function () {
            const bookingId = $(this).attr('id').replace('bookingAddServiceModal', '');

            // --- Разделка ---
            const preparationsBlock = $('#preparations-block-' + bookingId);
            const preparationsList = preparationsBlock.find('.preparations-list');
            preparationsList.empty();

            // Получаем сохранённые разделки
            $.get(`/booking/${bookingId}/saved-services`, function (data) {
                const savedPreparations = data.preparations || [];
                if (savedPreparations.length) {
                    if (!preparationsList.find('.preparation-header').length) {
                        preparationsList.append(`
                        <div class="d-flex fw-bold mb-2 preparation-header">
                            <span class="flex-fill">Животное</span>
                            <span class="flex-fill">Количество</span>
                        </div>
                    `);
                    }
                    savedPreparations.forEach(p => {
                        preparationsList.append(`
                        <div class="d-flex align-items-center gap-2 mb-2 preparation-item"
                             data-service-id="${p.id}"
                             style="border:2px solid #6c757d; padding:8px; border-radius:6px; background:#fff;">
                            <span class="flex-fill animal-name">${p.animal}</span>
                            <span class="flex-fill count-span">${p.count}</span>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-preparation-btn">x</button>
                        </div>
                    `);
                    });
                }
            });

            // Загружаем все разделки для выбора (оверлей)
            if (!preparationsBlock.data('loaded')) {
                $.get(`/booking/${bookingId}/preparation-services`, function (data) {
                    preparationsBlock.data('allPreparations', data.preparations || []);
                    preparationsBlock.data('loaded', true);
                });
            }
        });

        // --- Оверлей для добавления ---
        function showOverlay(block, allItems, overlaySelector, itemClass, fields) {
            const overlay = block.find(overlaySelector);
            overlay.empty();

            allItems.forEach((item, index) => {
                let html = `<div class="${itemClass} p-2 mb-2 rounded" data-index="${index}">`;
                fields.forEach(f => {
                    html += `<strong>${f.label}:</strong> ${item[f.key]} &nbsp;`;
                });
                html += `</div>`;
                overlay.append(html);
            });

            overlay.css({
                display: 'block',
                top: block.find('.btn-outline-primary').position().top + 30,
                left: block.find('.btn-outline-primary').position().left,
                width: '400px'
            });

            overlay.find(`.${itemClass}`).hover(
                function () {
                    $(this).css('background-color', '#e2e6ea');
                },
                function () {
                    $(this).css('background-color', '');
                }
            ).css('cursor', 'pointer');
        }

        // --- Кнопка "Добавить разделку" ---
        $(document).on('click', '.add-preparation-btn', function (e) {
            e.stopPropagation();
            const bookingId = $(this).data('booking');
            const block = $('#preparations-block-' + bookingId);
            showOverlay(block, block.data('allPreparations') || [], '.all-preparations-overlay', 'preparation-item-select', [
                {label: 'Животное', key: 'animal'},
            ]);
        });

        // --- Выбор элемента из оверлея ---
        $(document).on('click', '.preparation-item-select', function () {
            const block = $(this).closest('.service-block');
            const index = $(this).data('index');
            const preparation = block.data('allPreparations')[index];
            const list = block.find('.preparations-list');

            if (!list.find('.preparation-header').length) {
                list.append('<div class="d-flex fw-bold mb-2 preparation-header"><span class="flex-fill">Животное</span><span class="flex-fill">Кол-во</span></div>');
            }

            // Проверка на существующий элемент
            let existing = null;
            list.find('.preparation-item').each(function () {
                if ($(this).find('.animal-name').text() === preparation.animal) {
                    existing = $(this);
                    return false;
                }
            });

            if (existing) {
                const span = existing.find('.count-span');
                span.text(parseInt(span.text()) + parseInt(preparation.count || 1));
            } else {
                list.append(`
                <div class="d-flex align-items-center gap-2 mb-2 preparation-item"
                     data-service-id="${preparation.id}"
                     style="border:2px solid #6c757d; padding:8px; border-radius:6px; background:#fff;">
                    <span class="flex-fill animal-name">${preparation.animal}</span>
                    <span class="flex-fill count-span">${preparation.count || 1}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-preparation-btn">x</button>
                </div>
            `);
            }

            block.find('.all-preparations-overlay').hide();

            saveService(block, 'preparation', function () {
                return [{
                    service_id: preparation.id,
                    animal: preparation.animal,
                    count: preparation.count || 1
                }];
            });
        });

        // --- Сохранение сервиса ---
        function saveService(block, serviceType, itemsBuilder) {
            const bookingId = block.attr('id').match(/\d+/)[0];
            const items = itemsBuilder(block);
            if (!items.length) return;

            $.ajax({
                url: `/booking/${bookingId}/save-services`,
                method: 'POST',
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                data: {service_type: serviceType, items: items},
                success(response) {
                    items.forEach((item, index) => {
                        const htmlItem = block.find('.preparation-item').eq(index);
                        htmlItem.attr('data-service-id', response.items[index].id);
                    });
                },
                error(err) {
                    console.error(`Ошибка сохранения ${serviceType}:`, err);
                }
            });
        }

        // --- Удаление сервисов ---
        $(document).on('click', '.remove-preparation-btn', function () {
            const item = $(this).closest('.preparation-item');
            const serviceId = item.data('service-id');
            const bookingId = item.closest('.service-block').attr('id').match(/\d+/)[0];

            item.remove();

            $.ajax({
                url: `/booking/${bookingId}/service/${serviceId}`,
                method: 'DELETE',
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                success() {
                    console.log(`Сервис с ID ${serviceId} удалён`);
                },
                error(err) {
                    console.error('Ошибка удаления сервиса:', err);
                }
            });
        });

        // --- Скрытие оверлея при клике вне ---
        $(document).on('click', function (e) {
            $('.all-preparations-overlay').hide();
        });

    });


    // Питание
    $(document).ready(function () {

        $('[id^="bookingAddServiceModal"]').on('show.bs.modal', function () {
            const bookingId = $(this).attr('id').replace('bookingAddServiceModal', '');
            const block = $('#foods-block-' + bookingId);
            const foodsList = block.find('.foods-list');
            foodsList.empty();

            // --- Загружаем сохранённые продукты ---
            $.get(`/booking/${bookingId}/saved-services`, function (data) {
                const savedFoods = data.nutrition || [];

                if (!savedFoods.length) return;

                if (!foodsList.find('.food-header').length) {
                    foodsList.append(`
                    <div class="d-flex fw-bold mb-2 food-header">
                        <span class="flex-fill">Количество</span>
                    </div>
                `);
                }

                savedFoods.forEach(f => {
                    foodsList.append(`
                    <div class="d-flex align-items-center gap-2 mb-2 food-item"
                         data-service-id="${f.service_id}" 
                         data-service-name="${f.name}"
                         style="border:2px solid #6c757d; padding:8px; border-radius:6px; background:#fff;">
                        <span class="flex-fill count-span">${f.count}</span>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-food-btn">x</button>
                    </div>
                `);
                });
            });

            // --- Загружаем все продукты для оверлея ---
            if (!block.data('loaded')) {
                $.get(`/booking/${bookingId}/food-services`, function (data) {
                    block.data('allFoods', data.foods || []);
                    block.data('loaded', true);
                });
            }
        });

        // --- Открытие оверлея ---
        $(document).on('click', '.add-food-btn', function (e) {
            e.stopPropagation();
            const block = $(this).closest('.service-block');
            const overlay = block.find('.all-foods-overlay');
            const allFoods = block.data('allFoods') || [];

            if (!allFoods.length) {
                alert('Сначала подождите, пока список питания загрузится');
                return;
            }

            overlay.empty();
            allFoods.forEach((food, index) => {
                overlay.append(`
                <div class="food-item-select p-2 mb-2 rounded" data-index="${index}">
                    <strong>Питание:</strong> ${food.name}
                </div>
            `);
            });

            overlay.css({
                display: 'block',
                top: $(this).position().top + $(this).outerHeight() + 5,
                left: $(this).position().left,
                width: '400px'
            });

            overlay.find('.food-item-select').hover(
                function () { $(this).css('background-color', '#e2e6ea'); },
                function () { $(this).css('background-color', ''); }
            ).css('cursor', 'pointer');
        });

        // --- Выбор продукта из оверлея ---
        $(document).on('click', '.food-item-select', function () {
            const block = $(this).closest('.service-block');
            const index = $(this).data('index');
            const food = block.data('allFoods')[index];
            const foodsList = block.find('.foods-list');

            if (!foodsList.find('.food-header').length) {
                foodsList.append('<div class="d-flex fw-bold mb-2 food-header"><span class="flex-fill">Количество</span></div>');
            }

            // --- Проверяем, есть ли уже этот продукт по service_id ---
            let existing = null;
            foodsList.find('.food-item').each(function () {
                if ($(this).data('service-id') === food.id) {
                    existing = $(this);
                    return false;
                }
            });

            if (existing) {
                const countSpan = existing.find('.count-span');
                countSpan.text(parseInt(countSpan.text()) + 1);
            } else {
                foodsList.append(`
                <div class="d-flex align-items-center gap-2 mb-2 food-item"
                     data-service-id="${food.id}" 
                     data-service-name="${food.name}"
                     style="border:2px solid #6c757d; padding:8px; border-radius:6px; background:#fff;">
                    <span class="flex-fill count-span">${food.count || 1}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-food-btn">x</button>
                </div>
            `);
            }

            block.find('.all-foods-overlay').hide();

            // --- Сохраняем на бэке ---
            saveService(block, 'nutrition', function () {
                const items = [];
                foodsList.find('.food-item').each(function () {
                    items.push({
                        service_id: $(this).data('service-id'),
                        name: $(this).data('service-name'),
                        count: parseInt($(this).find('.count-span').text())
                    });
                });
                return items;
            });
        });

        // --- Удаление продукта ---
        $(document).on('click', '.remove-food-btn', function () {
            const item = $(this).closest('.food-item');
            const serviceId = item.data('service-id');
            const block = $(this).closest('.service-block');
            const bookingId = block.attr('id').match(/\d+/)[0];

            item.remove();

            $.ajax({
                url: `/booking/${bookingId}/service/${serviceId}`,
                method: 'DELETE',
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                success() { console.log(`Сервис с ID ${serviceId} удалён`); },
                error(err) { console.error('Ошибка удаления продукта:', err); }
            });
        });

        // --- Функция сохранения ---
        function saveService(block, serviceType, itemsBuilder) {
            const bookingId = block.attr('id').match(/\d+/)[0];
            const items = itemsBuilder(block);
            if (!items.length) return;

            $.ajax({
                url: `/booking/${bookingId}/save-services`,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { service_type: serviceType, items: items },
                success(response) {
                    // Обновляем data-service-id для элементов, чтобы фронт видел правильный id
                    foodsList.find('.food-item').each(function (i) {
                        const itemData = items[i];
                        $(this).attr('data-service-id', itemData.service_id);
                    });
                },
                error(err) { console.error(`Ошибка сохранения ${serviceType}:`, err); }
            });
        }

        // --- Скрытие оверлея при клике вне ---
        $(document).on('click', function () {
            $('.all-foods-overlay').hide();
        });
    });

    // $(document).ready(function () {
    //
    //     $('[id^="bookingAddServiceModal"]').on('show.bs.modal', function () {
    //         const bookingId = $(this).attr('id').replace('bookingAddServiceModal', '');
    //         const foodsBlock = $('#foods-block-' + bookingId);
    //         const foodsList = foodsBlock.find('.foods-list');
    //         foodsList.empty();
    //
    //         $.get(`/booking/${bookingId}/saved-services`, function (data) {
    //             const savedFoods = data.nutrition || [];
    //
    //             if (savedFoods.length) {
    //                 if (!foodsList.find('.food-header').length) {
    //                     foodsList.append(`
    //                     <div class="d-flex fw-bold mb-2 food-header">
    //                         <span class="flex-fill">Количество</span>
    //                     </div>
    //                 `);
    //                 }
    //
    //                 savedFoods.forEach(f => {
    //                     foodsList.append(`
    //                     <div class="d-flex align-items-center gap-2 mb-2 food-item"
    //                          data-service-id="${f.id}"
    //                          style="border:2px solid #6c757d; padding:8px; border-radius:6px; background:#fff;">
    //                         <span class="flex-fill count-span">${f.count}</span>
    //                         <button type="button" class="btn btn-sm btn-outline-danger remove-food-btn">x</button>
    //                     </div>
    //                 `);
    //                 });
    //             }
    //         });
    //
    //         if (!foodsBlock.data('loaded')) {
    //             $.get(`/booking/${bookingId}/food-services`, function (data) {
    //                 foodsBlock.data('allFoods', data.foods || []);
    //                 foodsBlock.data('loaded', true);
    //             });
    //         }
    //     });
    //
    //     function showOverlay(block, allItems, overlaySelector, itemClass, fields) {
    //         const overlay = block.find(overlaySelector);
    //         overlay.empty();
    //
    //         allItems.forEach((item, index) => {
    //             let html = `<div class="${itemClass} p-2 mb-2 rounded" data-index="${index}">`;
    //             fields.forEach(f => {
    //                 html += `<strong>${f.label}:</strong> ${item[f.key]} &nbsp;`;
    //             });
    //             html += `</div>`;
    //             overlay.append(html);
    //         });
    //
    //         overlay.css({
    //             display: 'block',
    //             top: block.find('.btn-outline-primary').position().top + 30,
    //             left: block.find('.btn-outline-primary').position().left,
    //             width: '400px'
    //         });
    //
    //         overlay.find(`.${itemClass}`).hover(
    //             function () { $(this).css('background-color', '#e2e6ea'); },
    //             function () { $(this).css('background-color', ''); }
    //         ).css('cursor', 'pointer');
    //     }
    //
    //     $(document).on('click', '.add-food-btn', function (e) {
    //         e.stopPropagation();
    //         const bookingId = $(this).data('booking');
    //         const block = $('#foods-block-' + bookingId);
    //
    //         showOverlay(
    //             block,
    //             block.data('allFoods') || [],
    //             '.all-foods-overlay',
    //             'food-item-select',
    //             [{ label: 'Питание', key: 'name' }]
    //         );
    //     });
    //
    //     $(document).on('click', '.food-item-select', function () {
    //         const block = $(this).closest('.service-block');
    //         const index = $(this).data('index');
    //         const food = block.data('allFoods')[index];
    //         const list = block.find('.foods-list');
    //
    //         if (!list.find('.food-header').length) {
    //             list.append('<div class="d-flex fw-bold mb-2 food-header"><span class="flex-fill">Кол-во</span></div>');
    //         }
    //
    //         let existing = null;
    //         list.find('.food-item').each(function () {
    //             if ($(this).data('service-id') === (food.db_id || food.id)) {
    //                 existing = $(this);
    //                 return false;
    //             }
    //         });
    //
    //         if (existing) {
    //             const span = existing.find('.count-span');
    //             span.text(parseInt(span.text()) + parseInt(food.count || 1));
    //         } else {
    //             list.append(`
    //             <div class="d-flex align-items-center gap-2 mb-2 food-item"
    //                  data-service-id="${food.db_id || food.id}"
    //                  style="border:2px solid #6c757d; padding:8px; border-radius:6px; background:#fff;">
    //                 <span class="flex-fill count-span">${food.count || 1}</span>
    //                 <button type="button" class="btn btn-sm btn-outline-danger remove-food-btn">x</button>
    //             </div>
    //         `);
    //         }
    //
    //         block.find('.all-foods-overlay').hide();
    //
    //         saveService(block, 'nutrition', function () {
    //             const items = [];
    //             list.find('.food-item').each(function () {
    //                 items.push({
    //                     service_id: $(this).data('service-id'),
    //                     count: parseInt($(this).find('.count-span').text())
    //                 });
    //             });
    //             return items;
    //         });
    //     });
    //
    //     function saveService(block, serviceType, itemsBuilder) {
    //         const bookingId = block.attr('id').match(/\d+/)[0];
    //         const items = itemsBuilder(block);
    //         if (!items.length) return;
    //
    //         $.ajax({
    //             url: `/booking/${bookingId}/save-services`,
    //             method: 'POST',
    //             headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    //             data: { service_type: serviceType, items: items },
    //             success(response) {
    //                 items.forEach((item, index) => {
    //                     const htmlItem = block.find('.food-item').eq(index);
    //                     htmlItem.attr('data-service-id', response.items[index].id);
    //                 });
    //             },
    //             error(err) {
    //                 console.error(`Ошибка сохранения ${serviceType}:`, err);
    //             }
    //         });
    //     }
    //
    //     $(document).on('click', '.remove-food-btn', function () {
    //         const item = $(this).closest('.food-item');
    //         const serviceId = item.data('service-id');
    //         const bookingId = item.closest('.service-block').attr('id').match(/\d+/)[0];
    //
    //         item.remove();
    //
    //         $.ajax({
    //             url: `/booking/${bookingId}/service/${serviceId}`,
    //             method: 'DELETE',
    //             headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    //             success() { console.log(`Сервис с ID ${serviceId} удалён`); },
    //             error(err) { console.error('Ошибка удаления сервиса:', err); }
    //         });
    //     });
    //
    //     $(document).on('click', function () {
    //         $('.all-foods-overlay').hide();
    //     });
    //
    // });

});
