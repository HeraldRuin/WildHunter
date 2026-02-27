$(document).ready(function () {
    $('[id^="bookingAddServiceModal"]').on('show.bs.modal', function () {

        const bookingId = $(this).attr('id').replace('bookingAddServiceModal', '');
        const block = $('#trophies-block-' + bookingId);
        block.data('bookingId', bookingId);

        const trophiesList = block.find('.trophies-list');
        trophiesList.empty();

        addTrophyHeader(trophiesList);

        loadTrophyAnimals(block, bookingId).done(animals => {
            loadSavedTrophies(bookingId, trophiesList);
        });
    });

    function addTrophyHeader(container) {
        container.append(`
            <div class="d-flex fw-bold mb-2 trophy-header">
                <span class="flex-fill">Животное</span>
                <span class="flex-fill">Тип</span>
                <span class="flex-fill">Количество</span>
                <span style="width:40px"></span>
            </div>
        `);
    }

    function loadTrophyAnimals(block, bookingId) {
        return $.get(`/booking/${bookingId}/trophies/animals`)
            .done(animals => block.data('trophyAnimals', animals));
    }

    function loadSavedTrophies(bookingId, container) {
        $.get(`/booking/${bookingId}/saved-services`, res => {
            (res.trophies || []).forEach(trophy => {
                container.append(renderSavedTrophyRow(trophy, bookingId));
            });
        });
    }

    function renderSavedTrophyRow(trophy, bookingId) {
        return $(`
        <div class="trophy-row border rounded p-2 mb-2 d-flex align-items-center"
             data-id="${trophy.id}">

            <div class="trophy-col trophy-animal-col">${trophy.animal_title}</div>
            <div class="trophy-col trophy-type-col">${trophy.type}</div>
            <div class="trophy-col trophy-count-col">${trophy.count}</div>

            <button class="btn btn-sm btn-outline-danger remove-saved-trophy">Удалить</button>
        </div>
    `);
    }

    $(document).on('click', '.add-trophy-btn', function () {

        const block = $(this).closest('.service-block');
        const bookingId = block.data('bookingId');
        const animals = block.data('trophyAnimals') || [];

        block.find('.trophies-list')
            .append(renderNewTrophyRow(animals, bookingId));
    });

    function renderNewTrophyRow(animals, bookingId) {

        const $row = $(`
        <div class="trophy-row border rounded p-2 mb-2 d-flex align-items-center gap-2">

            <div class="col-auto">
                <select class="form-select form-select-sm trophy-animal" style="width: 270px;">
                    <option value="" disabled selected hidden>Животное</option>
                </select>
            </div>

            <div class="col-auto">
                <select class="form-select form-select-sm trophy-type" style="width: 270px;" disabled>
                    <option value="" disabled selected hidden>Тип</option>
                </select>
            </div>

            <div class="col-auto">
                <input type="number" min="1" value="1" class="form-control form-control-sm trophy-count" placeholder="Количество" style="width: 230px;">
            </div>

            <div class="col-auto">
                <button class="btn btn-sm btn-success save-trophy" disabled>Сохранить</button>
                <button class="btn btn-sm btn-outline-secondary cancel-new">Отмена</button>
            </div>
        </div>
    `);

        const $animal = $row.find('.trophy-animal');
        const $type = $row.find('.trophy-type');
        const $count = $row.find('.trophy-count');
        const $save = $row.find('.save-trophy');

        animals.forEach(a => {
            $animal.append(`<option value="${a.id}">${a.title}</option>`);
        });

        $animal.on('change', function () {
            const animal = animals.find(a => String(a.id) === String(this.value));

            $type.empty()
                .append('<option value="" disabled selected hidden>Тип</option>')
                .prop('disabled', true);

            if (animal?.trophies?.length) {
                animal.trophies.forEach(t => {
                    $type.append(
                        `<option value="${t.id}">${t.type}</option>`
                    );
                });
                $type.prop('disabled', false);
            }

            check();
        });

        $type.on('change', check);
        $count.on('input', check);

        function check() {
            $save.prop(
                'disabled',
                !($animal.val() && $type.val() && $count.val())
            );
        }

        $save.on('click', function () {
            const trophyId = $type.val();
            $.post(`/booking/${bookingId}/trophies`, {
                animal_id: $animal.val(),
                type: $type.find('option:selected').text(),
                count: $count.val(),
                trophy_id: trophyId,
                _token: $('meta[name="csrf-token"]').attr('content')
            }).done(saved => {
                $row.replaceWith(renderSavedTrophyRow(saved, bookingId));
            });
        });

        // отмена
        $row.find('.cancel-new').on('click', () => $row.remove());

        return $row;
    }

    $(document).on('click', '.remove-saved-trophy', function () {

        const row = $(this).closest('.trophy-row');
        const trophyId = row.data('id'); // id трофея
        const bookingId = row.closest('.service-block').data('bookingId');

        $.ajax({
            url: `/booking/${bookingId}/trophy/${trophyId}`, // <-- используем id трофея
            type: 'DELETE',
            data: {_token: $('meta[name="csrf-token"]').attr('content')},
            success: () => row.remove()
        });
    });
});

// Штрафы
$(document).ready(function () {
    $('[id^="bookingAddServiceModal"]').on('show.bs.modal', function () {
        const bookingId = $(this).attr('id').replace('bookingAddServiceModal', '');
        const block = $('#penalties-block-' + bookingId);
        block.data('bookingId', bookingId);
        const penaltiesList = block.find('.penalties-list');
        penaltiesList.empty();

        addPenaltyHeader(penaltiesList);

        loadPenaltyAnimals(block, bookingId).done(animals => {
            loadSavedPenalties(bookingId, penaltiesList);
        });
    });

    function addPenaltyHeader(container) {
        container.append(`
        <div class="d-flex fw-bold mb-2 penalty-header">
            <span class="flex-fill">Животное</span>
            <span class="flex-fill">Тип штрафа</span>
            <span class="flex-fill">Охотник</span>
            <span style="width:40px"></span>
        </div>
    `);
    }


    function loadPenaltyAnimals(block, bookingId) {
        return $.get(`/booking/${bookingId}/penalty/animals`).done(res => {
            block.data('penaltyAnimals', res.animals || []);
            block.data('hunters', res.hunters || []);
        });
    }

    function loadSavedPenalties(bookingId, container) {
        $.get(`/booking/${bookingId}/saved-services`, res => {
            (res.penalties || []).forEach(penalty => {
                container.append(renderSavedPenaltyRow(penalty, bookingId));
            });
        });
    }

    function renderSavedPenaltyRow(penalty, bookingId) {
        return $(`
        <div class="penalty-row border rounded p-2 mb-2 d-flex align-items-center"
             data-id="${penalty.id}">

            <div class="penalty-col animal-penalty-col">${penalty.animal_title}</div>
            <div class="penalty-col type-col">${penalty.type}</div>
            <div class="penalty-col hunter-col">${penalty.hunter_name || ''}</div>
            <button class="btn btn-sm btn-outline-danger remove-saved-penalty">Удалить</button>
        </div>
    `);
    }


    $(document).on('click', '.add-penalty-btn', function () {

        const block = $(this).closest('.service-block');
        const bookingId = block.data('bookingId');
        const animals = block.data('penaltyAnimals') || [];
        const hunters = block.data('hunters') || [];

        block.find('.penalties-list')
            .append(renderNewPenaltyRow(animals, hunters, bookingId));
    });

    function renderNewPenaltyRow(animals, hunters, bookingId) {

        const $row = $(`
        <div class="penalty-row border rounded p-2 mb-2 d-flex align-items-center gap-2">

            <div>
                <select class="form-select form-select-sm penalty-animal" style="width: 267px;">
                    <option value="" disabled selected hidden>Выберите животное</option>
                </select>
            </div>

            <div class="col-auto">
                 <select class="form-select form-select-sm penalty-type" style="width: 267px;" disabled>
                    <option value="" disabled selected hidden>Выберите тип штрафа</option>
                </select>
            </div>

            <div class="col-auto">
                <select class="form-select form-select-sm hunter" style="width: 267px;">
                    <option value="" disabled selected hidden>Выберите охотника</option>
                </select>
            </div>

            <div class="col-auto">
                <button class="btn btn-sm btn-success save-penalty" disabled>Сохранить</button>
                <button class="btn btn-sm btn-outline-secondary cancel-new">Отмена</button>
            </div>
        </div>
    `);

        const $animal = $row.find('.penalty-animal');
        const $type = $row.find('.penalty-type');
        const $hunter = $row.find('.hunter');
        const $save = $row.find('.save-penalty');

        animals.forEach(a => {
            $animal.append(`<option value="${a.id}">${a.title}</option>`);
        });

        hunters.forEach(h => {
            $hunter.append(`<option value="${h.id}">${h.name}</option>`);
        });

        function check() {
            $save.prop(
                'disabled',
                !($animal.val() && $type.val() && $hunter.val())
            );
        }

        $animal.on('change', function () {
            const animal = animals.find(a => String(a.id) === String(this.value));

            $type.empty()
                .append('<option value="" disabled selected hidden>Выберите тип штрафа</option>')
                .prop('disabled', true);

            if (animal?.fines?.length) {
                animal.fines.forEach(f => {
                    $type.append(
                        `<option value="${f.id}" data-price="${f.price}">${f.type}</option>`
                    );
                });
                $type.prop('disabled', false);
            }

            check();
        });

        $type.on('change', check);
        $hunter.on('change', check);

        $save.on('click', function () {
            const fineId = $type.val();
            $.post(`/booking/${bookingId}/penalty`, {
                animal_id: $animal.val(),
                type: $type.find('option:selected').text(),
                hunter_id: $hunter.val(),
                penalty_id: fineId,
                _token: $('meta[name="csrf-token"]').attr('content')
            }).done(saved => {
                $row.replaceWith(renderSavedPenaltyRow(saved, bookingId));
            });
        });

        $row.find('.cancel-new').on('click', () => $row.remove());

        return $row;
    }

    $(document).on('click', '.remove-saved-penalty', function () {

        const row = $(this).closest('.penalty-row');
        const penaltyId = row.data('id');
        const bookingId = row.closest('.service-block').data('bookingId');

        $.ajax({
            url: `/booking/${bookingId}/penalty/${penaltyId}`,
            type: 'DELETE',
            data: {_token: $('meta[name="csrf-token"]').attr('content')},
            success: () => row.remove()
        });
    });

});

// Разделка
$(document).ready(function () {

    $('[id^="bookingAddServiceModal"]').on('show.bs.modal', function () {
        const bookingId = $(this).attr('id').replace('bookingAddServiceModal', '');
        const block = $('#preparations-block-' + bookingId);
        block.data('bookingId', bookingId);

        const preparationsList = block.find('.preparations-list');
        preparationsList.empty();

        addPreparationHeader(preparationsList);

        loadPreparationAnimals(block, bookingId).done(animals => {
            loadSavedPreparations(bookingId, preparationsList);
        });
    });

    function addPreparationHeader(container) {
        container.append(`
            <div class="d-flex fw-bold mb-2 preparation-header">
                <span class="flex-fill">Животное</span>
                <span class="flex-fill">Количество</span>
                <span style="width:40px"></span>
            </div>
        `);
    }

    function loadPreparationAnimals(block, bookingId) {
        return $.get(`/booking/${bookingId}/preparation/animals`).done(res => {
            block.data('preparationAnimals', res.animals || []);
        });
    }

    function loadSavedPreparations(bookingId, container) {
        $.get(`/booking/${bookingId}/saved-services`, res => {
            (res.preparations || []).forEach(prep => {
                container.append(renderSavedPreparationRow(prep, bookingId));
            });
        });
    }

    function renderSavedPreparationRow(prep, bookingId) {
        return $(`
        <div class="preparation-row border rounded p-2 mb-2 d-flex align-items-center"
             data-id="${prep.id}">

            <div class="prep-col animal-col">${prep.animal_title}</div>
            <div class="prep-col count-col">${prep.count}</div>
            <button class="btn btn-sm btn-outline-danger remove-saved-preparation">Удалить</button>
        </div>
    `);
    }

    $(document).on('click', '.add-preparation-btn', function () {

        const block = $(this).closest('.service-block');
        const bookingId = block.data('bookingId');
        const animals = block.data('preparationAnimals') || [];

        block.find('.preparations-list')
            .append(renderNewPreparationRow(animals, bookingId));
    });

    function renderNewPreparationRow(animals, bookingId) {

        const $row = $(`
        <div class="preparation-row border rounded p-2 mb-2 d-flex align-items-center gap-2">

            <div>
                <select class="form-select form-select-sm preparation-animal" style="width: 270px;">
                    <option value="" disabled selected hidden>Выберите животное</option>
                </select>
            </div>

            <div class="col-auto" style="margin-left: 165px">
                <input type="number" min="1" value="1" class="form-control form-control-sm preparation-count" placeholder="Количество">
            </div>

            <div class="col-auto" style="margin-left: 110px">
                <button class="btn btn-sm btn-success save-preparation" disabled>Сохранить</button>
                <button class="btn btn-sm btn-outline-secondary cancel-new">Отмена</button>
            </div>
        </div>
    `);

        const $animal = $row.find('.preparation-animal');
        const $count = $row.find('.preparation-count');
        const $save = $row.find('.save-preparation');

        animals.forEach(a => $animal.append(`<option value="${a.id}">${a.title}</option>`));

        function check() {
            $save.prop('disabled', !($animal.val() && $count.val() > 0));
        }

        $animal.on('change', check);
        $count.on('input', check);

        $save.on('click', function () {
            const selectedAnimal = animals.find(a => a.id == $animal.val());
            const preparationId = selectedAnimal.preparations.length > 0 ? selectedAnimal.preparations[0].id : null;

            $.post(`/booking/${bookingId}/preparation`, {
                animal_id: $animal.val(),
                preparation_id: preparationId,
                count: $count.val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            }).done(saved => {
                $row.replaceWith(renderSavedPreparationRow(saved, bookingId));
            });
        });

        $row.find('.cancel-new').on('click', () => $row.remove());

        return $row;
    }

    $(document).on('click', '.remove-saved-preparation', function () {
        const row = $(this).closest('.preparation-row');
        const prepId = row.data('id');
        const bookingId = row.closest('.service-block').data('bookingId');

        $.ajax({
            url: `/booking/${bookingId}/preparation/${prepId}`,
            type: 'DELETE',
            data: {_token: $('meta[name="csrf-token"]').attr('content')},
            success: () => row.remove()
        });
    });
});

// Питание
$(document).ready(function () {
    $('[id^="bookingAddServiceModal"]').on('show.bs.modal', function () {

        const bookingId = $(this).attr('id').replace('bookingAddServiceModal', '');
        const block = $('#foods-block-' + bookingId);
        block.data('bookingId', bookingId);
        const foodsList = block.find('.foods-list');
        foodsList.empty();

        addFoodHeader(foodsList)
        loadSavedFoods(bookingId, foodsList);
    });

    function addFoodHeader(container) {
        container.append(`
            <div class="d-flex fw-bold mb-2 food-header">
                <span class="flex-fill">Питание</span>
                <span class="flex-fill">Количество</span>
                <span style="width:40px"></span>
            </div>
        `);
    }

    function loadSavedFoods(bookingId, container) {
        $.get(`/booking/${bookingId}/saved-services`, res => {
            (res.foods || []).forEach(food => {
                container.append(renderSavedFoodRow(food));
            });
        });
    }

    function renderSavedFoodRow(food) {
        return $(`
        <div class="food-row border rounded p-2 mb-2 d-flex align-items-center"
             data-id="${food.id}">

            <div class="food-col food-name-col">Питание</div>
            <div class="food-col count-col">${food.count}</div>

            <button class="btn btn-sm btn-outline-danger remove-saved-food">Удалить</button>
        </div>
    `);
    }

    $(document).on('click', '.add-food-btn', function () {

        const block = $(this).closest('.service-block');
        const bookingId = block.data('bookingId');

        block.find('.foods-list')
            .append(renderNewFoodRow(bookingId));
    });

    function renderNewFoodRow(bookingId) {

        const $row = $(`
        <div class="food-row border rounded p-2 mb-2 d-flex align-items-center gap-2">

            <div class="flex-fill">
                Питание
            </div>

            <div class="col-auto" style="margin-right: 100px">
                <input
                   type="number" min="1" value="1"
                    class="form-control form-control-sm food-count"
                    placeholder="Количество" >
            </div>

            <div class="col-auto">
                <button class="btn btn-sm btn-success save-food" disabled>Сохранить</button>
                <button class="btn btn-sm btn-outline-secondary cancel-new">Отмена</button>
            </div>
        </div>
    `);

        const $count = $row.find('.food-count');
        const $save = $row.find('.save-food');

        function check() {
            $save.prop('disabled', !($count.val() > 0));
        }

        $count.on('input', check);
        check();

        $save.on('click', function () {
            $.post(`/booking/${bookingId}/food`, {
                count: $count.val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            }).done(saved => {
                $row.replaceWith(renderSavedFoodRow(saved));
            });
        });

        $row.find('.cancel-new').on('click', () => $row.remove());

        return $row;
    }

    $(document).on('click', '.remove-saved-food', function () {

        const row = $(this).closest('.food-row');
        const foodId = row.data('id');
        const bookingId = row.closest('.service-block').data('bookingId');

        $.ajax({
            url: `/booking/${bookingId}/food/${foodId}`,
            type: 'DELETE',
            data: {_token: $('meta[name="csrf-token"]').attr('content')},
            success: () => row.remove()
        });
    });

});

// Другое
$(document).ready(function () {

    $('[id^="bookingAddServiceModal"]').on('show.bs.modal', function () {

        const bookingId = $(this).attr('id').replace('bookingAddServiceModal', '');
        const block = $('#others-block-' + bookingId);
        block.data('bookingId', bookingId);
        const list = block.find('.others-list');
        list.empty();

        addOthersHeader(list);

        loadOtherPrices(block, bookingId).done(() => {
            loadSavedOthers(bookingId, list);
        });
    });

    function addOthersHeader(container) {
        container.append(`
            <div class="d-flex fw-bold mb-2 food-header">
                <span class="flex-fill">Название</span>
                <span class="flex-fill">Количество</span>
                <span style="width:40px"></span>
            </div>
        `);
    }

    function loadOtherPrices(block, bookingId) {
        return $.get(`/booking/${bookingId}/addetional/services`)
            .done(res => {
                block.data('otherPrices', res.addetionals || []);
            });
    }

    function loadSavedOthers(bookingId, container) {
        $.get(`/booking/${bookingId}/saved-services`, res => {
            (res.addetionals || []).forEach(addetional => {
                container.append(renderSavedOtherRow(addetional));
            });
        });
    }

    function renderSavedOtherRow(addetional) {
        return $(`
            <div class="other-row border rounded p-2 mb-2 d-flex align-items-center"
                 data-id="${addetional.id}">
                <div class="other-col type-col other-name-col">${addetional.type ?? '—'}</div>
                <div class="other-col type-col other-count-col">${addetional.count ?? '—'}</div>

                <button class="btn btn-sm btn-outline-danger remove-saved-other">Удалить</button>
            </div>
        `);
    }

    // Добавление новой строки
    $(document).on('click', '.add-other-btn', function () {
        const block = $(this).closest('.service-block');
        const bookingId = block.data('bookingId');
        const prices = block.data('otherPrices') || [];

        block.find('.others-list').append(renderNewOtherRow(prices, bookingId));
    });

    function renderNewOtherRow(prices, bookingId) {
        const $row = $(`
        <div class="other-row border rounded p-2 mb-2 d-flex align-items-center gap-2">

            <div class="flex-fill">
                <select class="form-select form-select-sm other-price" style="width: 190px;">
                    <option value="" disabled selected hidden>Выберите услугу</option>
                </select>
            </div>

            <div style="width: 220px; margin-right: 200px">
                <input
                    type="number"
                    class="form-control form-control-sm other-count"
                    min="1"
                    value="1"
                    disabled
                />
            </div>

            <div class="col-auto">
                <button class="btn btn-sm btn-success save-other" disabled>Сохранить</button>
                <button class="btn btn-sm btn-outline-secondary cancel-new">Отмена</button>
            </div>
        </div>
    `);

        const $select = $row.find('.other-price');
        const $countInput = $row.find('.other-count');
        const $save = $row.find('.save-other');

        prices.forEach(p => {
            $select.append(`
            <option value="${p.id}" data-max="${p.count ?? 1}">
                ${p.name}
            </option>
        `);
        });

        $select.on('change', function () {
            const max = parseInt($(this).find('option:selected').data('max'), 10);

            $countInput
                .prop('disabled', false)
                .attr('max', max)
                .val(1);

            $save.prop('disabled', false);
        });

        $countInput.on('input', function () {
            const max = parseInt($(this).attr('max'), 10);
            let val = parseInt($(this).val(), 10);

            if (isNaN(val) || val < 1) val = 1;
            if (val > max) val = max;

            $(this).val(val);
        });

        // Сохранение
        $save.one('click', function () {
            const selected = $select.find('option:selected');
            const addetionalId = selected.val();
            const count = parseInt($countInput.val(), 10);

            $.post(`/booking/${bookingId}/addetional`, {
                addetional: selected.text(),
                addetional_id: addetionalId,
                count: count,
                _token: $('meta[name="csrf-token"]').attr('content')
            }).done(saved => {
                $row.replaceWith(renderSavedOtherRow(saved));
            });
        });

        $row.find('.cancel-new').on('click', () => $row.remove());

        return $row;
    }


    // Удаление сохраненной услуги
    $(document).on('click', '.remove-saved-other', function () {
        const row = $(this).closest('.other-row');
        const id = row.data('id');
        const bookingId = row.closest('.service-block').data('bookingId');

        $.ajax({
            url: `/booking/${bookingId}/addetional/${id}`,
            type: 'DELETE',
            data: {_token: $('meta[name="csrf-token"]').attr('content')},
            success: () => row.remove()
        });
    });
});

// Траты охотников
$(document).ready(function () {
    $('[id^="bookingAddServiceModal"]').on('show.bs.modal', function () {
        const bookingId = $(this).attr('id').replace('bookingAddServiceModal', '');
        const block = $('#spending-block-' + bookingId);
        block.data('bookingId', bookingId);
        const spendingList = block.find('.spending-list');
        spendingList.empty();

        addSpendingHeader(spendingList);

        loadSpendingUser(block, bookingId).done(() => {
            loadSavedSpendings(bookingId, spendingList);
        });
    });

    function addSpendingHeader(container) {
        container.append(`
        <div class="d-flex fw-bold mb-2 spending-header">
            <span class="flex-fill">Кто платил</span>
            <span class="flex-fill">Сумма</span>
            <span class="flex-fill">Коммент</span>
            <span style="width:40px"></span>
        </div>
    `);
    }

    function loadSpendingUser(block, bookingId) {
        return $.get(`/booking/${bookingId}/spending/users`).done(res => {
            block.data('hunters', res.hunters || []);
        });
    }

    function loadSavedSpendings(bookingId, container) {
        $.get(`/booking/${bookingId}/saved-services`, res => {
            (res.spendings || []).forEach(spending => {
                container.append(renderSavedSpendingRow(spending));
            });
        });
    }

    function renderSavedSpendingRow(spending) {
        return $(`
        <div class="spending-row border rounded p-2 mb-2 d-flex align-items-center"
             data-id="${spending.id}">

       <div class="spending-col spending-hunter-col">${spending.hunter_name || ''}</div>
            <div class="spending-col spending-count-col">${spending.count}</div>
            <div class="spending-col spending-comment-col">${spending.comment}</div>
     
            <button class="btn btn-sm btn-outline-danger remove-saved-spending">Удалить</button>
        </div>
    `);
    }

    $(document).on('click', '.add-spending-btn', function () {

        const block = $(this).closest('.service-block');
        const bookingId = block.data('bookingId');
        const hunters = block.data('hunters') || [];

        block.find('.spending-list')
            .append(renderNewSpendingRow(hunters, bookingId));
    });

    function renderNewSpendingRow(hunters, bookingId) {

        const $row = $(`
        <div class="spending-row border rounded p-2 mb-2 d-flex align-items-center gap-2">

            <div>
                <select class="form-select form-select-sm hunter" style="width: 270px;">
                    <option value="" disabled selected hidden>Выберите охотника</option>
                </select>
            </div>

            <div class="col-auto">
                <input type="number" min="1" value="1" class="form-control form-control-sm spending-count" placeholder="Сумма" style="width: 200px;">
            </div>

            <div class="col-auto">
                <input type="text" class="form-control form-control-sm spending-comment" placeholder="Коммент" style="width: 340px;">
            </div>

            <div class="col-auto">
                <button class="btn btn-sm btn-success save-spending" disabled>Сохранить</button>
                <button class="btn btn-sm btn-outline-secondary cancel-new">Отмена</button>
            </div>
        </div>
    `);

        const $hunter = $row.find('.hunter');
        const $count = $row.find('.spending-count');
        const $comment = $row.find('.spending-comment');
        const $save = $row.find('.save-spending');

        hunters.forEach(h => {
            $hunter.append(`<option value="${h.id}">${h.name}</option>`);
        });

        function check() {
            const isFilled = $hunter.val() && $count.val().trim() !== '' && $comment.val().trim() !== '';
            $save.prop('disabled', !isFilled);
        }

        $hunter.on('change', check);
        $count.on('input', check);
        $comment.on('input', check);

        $save.on('click', function () {
            $.post(`/booking/${bookingId}/spending`, {
                hunter_id: $hunter.val(),
                price: $count.val(),
                comment: $comment.val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            }).done(saved => {
                $row.replaceWith(renderSavedSpendingRow(saved, bookingId));
            });
        });

        $row.find('.cancel-new').on('click', () => $row.remove());

        return $row;
    }

    $(document).on('click', '.remove-saved-spending', function () {

        const row = $(this).closest('.spending-row');
        const spendingId = row.data('id');
        const bookingId = row.closest('.service-block').data('bookingId');

        $.ajax({
            url: `/booking/${bookingId}/spending/${spendingId}`,
            type: 'DELETE',
            data: {_token: $('meta[name="csrf-token"]').attr('content')},
            success: () => row.remove()
        });
    });
});






