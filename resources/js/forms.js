/**
 * Hành vi chung cho form (lắng nghe ở document để vẫn chạy sau khi <body> được thay):
 * - Nút ẩn/hiện mật khẩu: [data-password-toggle] aria-controls="<id ô mật khẩu>".
 * - Form [data-busy-form]: khi gửi, khóa nút submit và đổi nhãn để tránh bấm hai lần.
 */
document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-password-toggle]');

    if (!toggle) {
        return;
    }

    const input = document.getElementById(toggle.getAttribute('aria-controls'));
    const isVisible = input.type === 'text';

    input.type = isVisible ? 'password' : 'text';
    toggle.setAttribute('aria-pressed', String(!isVisible));
    toggle.setAttribute('aria-label', isVisible ? toggle.dataset.labelShow : toggle.dataset.labelHide);
    toggle.querySelector('[data-icon-show]').classList.toggle('hidden', !isVisible);
    toggle.querySelector('[data-icon-hide]').classList.toggle('hidden', isVisible);
});

document.addEventListener('submit', (event) => {
    const form = event.target.closest('[data-busy-form]');

    if (!form || event.defaultPrevented) {
        return;
    }

    const button = form.querySelector('[data-busy-label]');

    if (!button) {
        return;
    }

    button.disabled = true;
    button.setAttribute('aria-busy', 'true');
    button.querySelector('[data-button-label]').textContent = button.dataset.busyLabel;
});

/**
 * Form [data-confirm="<câu hỏi>"]: hỏi lại trước khi gửi (vd. xóa bản ghi).
 */
document.addEventListener('submit', (event) => {
    const form = event.target.closest('[data-confirm]');

    if (form && !window.confirm(form.dataset.confirm)) {
        event.preventDefault();
    }
});

/**
 * Trang được khôi phục từ bộ nhớ đệm khi bấm Back: mở lại nút đã khóa.
 */
window.addEventListener('pageshow', (event) => {
    if (!event.persisted) {
        return;
    }

    document.querySelectorAll('[data-busy-form] [aria-busy="true"]').forEach((button) => {
        button.disabled = false;
        button.removeAttribute('aria-busy');
    });
});
