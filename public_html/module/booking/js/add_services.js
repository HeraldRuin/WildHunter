$(document).ready(function () {
    $('[id^="bookingAddServiceModal"]').on('show.bs.modal', function () {

        const bookingId = $(this).attr('id').replace('bookingAddServiceModal', '');
        const block = $('#trophies-block-' + bookingId);
        block.data('bookingId', bookingId);

        const trophiesList = block.find('.trophies-list');
        trophiesList.empty();

        addTrophyHeader(trophiesList);

        loadTrophyAnimals(block).done(animals => {
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

    function loadTrophyAnimals(block) {
        return $.get('/booking/trophies/animals')
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
                <input type="text" class="form-control form-control-sm trophy-count" placeholder="Количество" style="width: 170px;">
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
                    $type.append(`<option value="${t.id}">${t.type}</option>`);
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
            $.post(`/booking/${bookingId}/trophies`, {
                animal_id: $animal.val(),
                type: $type.find('option:selected').text(),
                count: $count.val(),
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

            <div class="col-auto">
                <select class="form-select form-select-sm penalty-animal" style="width: 250px;">
                    <option value="" disabled selected hidden>Выберите животное</option>
                </select>
            </div>

            <div class="col-auto">
                 <select class="form-select form-select-sm penalty-type" style="width: 250px;" disabled>
                    <option value="" disabled selected hidden>Выберите тип штрафа</option>
                </select>
            </div>

            <div class="col-auto">
                <select class="form-select form-select-sm hunter" style="width: 250px;">
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
                    $type.append(`<option value="${f.id}">${f.type}</option>`);
                });
                $type.prop('disabled', false);
            }

            check();
        });

        $type.on('change', check);
        $hunter.on('change', check);

        $save.on('click', function () {
            $.post(`/booking/${bookingId}/penalty`, {
                animal_id: $animal.val(),
                type: $type.find('option:selected').text(),
                hunter_id: $hunter.val(),
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

                <div class="col-auto">
                    <select class="form-select form-select-sm preparation-animal" style="width: 270px;">
                        <option value="" disabled selected hidden>Выберите животное</option>
                    </select>
                </div>

                <div class="col-auto">
                    <input type="text" class="form-control form-control-sm preparation-count" placeholder="Количество">
                </div>

                <div class="col-auto">
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
            $.post(`/booking/${bookingId}/preparation`, {
                animal_id: $animal.val(),
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

            <div class="col-auto">
                <input
                    type="text"
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

        loadOtherPrices(block, bookingId).done(() => {
            loadSavedOthers(bookingId, list);
        });
    });

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

            <button class="btn btn-sm btn-outline-danger remove-saved-other">Удалить</button>
        </div>
    `);
    }
    $(document).on('click', '.add-other-btn', function () {

        const block = $(this).closest('.service-block');
        const bookingId = block.data('bookingId');
        const prices = block.data('otherPrices') || [];

        block.find('.others-list')
            .append(renderNewOtherRow(prices, bookingId));
    });

    function renderNewOtherRow(prices, bookingId) {

        const $row = $(`
        <div class="other-row border rounded p-2 mb-2 d-flex align-items-center gap-2">

            <div class="flex-fill">
                <select class="form-select form-select-sm other-price" style="width: 370px;">
                    <option value="" disabled selected hidden>Выберите услугу</option>
                </select>
            </div>

            <div>
                <button class="btn btn-sm btn-success save-other" disabled>Сохранить</button>
                <button class="btn btn-sm btn-outline-secondary cancel-new">Отмена</button>
            </div>
        </div>
    `);

        const $select = $row.find('.other-price');
        const $save   = $row.find('.save-other');

        prices.forEach(p => {
            $select.append(`<option value="${p.id}">${p.name}</option>`);
        });

        $select.on('change', () => {
            $save.prop('disabled', !$select.val());
        });

        $save.on('click', function () {
            $.post(`/booking/${bookingId}/addetional`, {
                addetional: $select.find('option:selected').text(),
                _token: $('meta[name="csrf-token"]').attr('content')
            }).done(saved => {
                $row.replaceWith(renderSavedOtherRow(saved));
            });
        });

        $row.find('.cancel-new').on('click', () => $row.remove());

        return $row;
    }

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





