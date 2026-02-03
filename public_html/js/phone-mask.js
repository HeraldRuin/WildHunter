/* global Inputmask */

document.addEventListener('DOMContentLoaded', function () {
    initPhoneMask();
});

function initPhoneMask(selector = '.phone-mask') {
    const inputs = document.querySelectorAll(selector);
    if (!inputs.length) return;

    Inputmask({
        mask: '+7 (999) 999-99-99',
        placeholder: '_',
        showMaskOnHover: false,
        showMaskOnFocus: true,
        clearIncomplete: true,

        // 🔥 ВАЖНО
        inputmode: 'numeric',
        numericInput: false,
        rightAlign: false,

        // Ловит любой ввод / вставку
        onBeforePaste: function (pastedValue) {
            return pastedValue.replace(/\D/g, '');
        }
    }).mask(inputs);
}

