/**
 * Kiểm tra ô nhập ngay khi gõ (chỉ phía trình duyệt, không thay kiểm tra ở server):
 * - input[data-validate]: hợp lệ (theo required/pattern/minlength/type) thì gắn data-valid → viền đậm + dấu ✓.
 * - data-match="<id>": phải trùng giá trị ô kia; cập nhật dòng gợi ý bằng data-hint-ok / data-hint-bad.
 * - form[data-ready-form]: [data-ready-text] hiện data-ready khi mọi ô bắt buộc đều hợp lệ, ngược lại data-not-ready.
 */
const isValid = (input) => {
    if (input.value.trim() === '' || !input.checkValidity()) {
        return false;
    }

    if (input.dataset.match) {
        return document.getElementById(input.dataset.match)?.value === input.value;
    }

    return true;
};

const refreshInput = (input) => {
    const valid = isValid(input);

    input.toggleAttribute('data-valid', valid);

    if (input.dataset.match) {
        const hint = document.getElementById(`${input.id}-hint`);

        if (hint) {
            hint.textContent = input.value === '' ? '' : valid ? input.dataset.hintOk : input.dataset.hintBad;
        }
    }
};

const refreshForm = (form) => {
    form.querySelectorAll('[data-validate]').forEach(refreshInput);

    const readyText = form.matches('[data-ready-form]') ? form.querySelector('[data-ready-text]') : null;

    if (!readyText) {
        return;
    }

    const ready = [...form.querySelectorAll('input[required]')].every((input) =>
        input.hasAttribute('data-validate') ? isValid(input) : input.value.trim() !== '',
    );

    readyText.textContent = ready ? readyText.dataset.ready : readyText.dataset.notReady;
};

const refreshAll = () => {
    document.querySelectorAll('form').forEach((form) => {
        if (form.querySelector('[data-validate]')) {
            refreshForm(form);
        }
    });
};

document.addEventListener('input', (event) => {
    const form = event.target.closest('form');

    if (form?.querySelector('[data-validate]')) {
        refreshForm(form);
    }
});

refreshAll();
document.addEventListener('lh:page-updated', refreshAll);
