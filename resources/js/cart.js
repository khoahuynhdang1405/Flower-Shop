/**
 * Trang giỏ hàng: các form trong [data-cart] (− / +, Xóa, mã ưu đãi) gửi bằng fetch thay vì tải lại trang.
 * Server vẫn trả về trang giỏ như khi gửi form thường (redirect về /cart, kèm thông báo / lỗi trong session);
 * script chỉ thay nội dung [data-cart] và số trên icon giỏ [data-cart-count], giữ nguyên vị trí cuộn.
 * Lỗi mạng / phiên hết hạn (419...) thì gửi form theo cách thường. Không có JavaScript form vẫn chạy bình thường.
 */
const parser = new DOMParser();

/** Form số lượng (PATCH) và form xóa (DELETE) của cùng một dòng dùng chung URL — phân biệt thêm bằng _method. */
const formKey = (form) => `${form.querySelector('input[name="_method"]')?.value ?? 'POST'} ${form.action}`;

/** Ô cần focus sau khi thay nội dung: cùng form, cùng vị trí nút; nút bị khóa thì lấy nút còn bấm được. */
const focusTarget = (cart, key, buttonIndex) => {
    const invalid = cart.querySelector('[aria-invalid="true"]');

    if (invalid) {
        return invalid;
    }

    const form = [...cart.querySelectorAll('form[data-cart-form]')].find((candidate) => formKey(candidate) === key);
    const buttons = form ? [...form.querySelectorAll('button')] : [];
    const button = buttons[buttonIndex];

    return button && !button.disabled ? button : (buttons.find((candidate) => !candidate.disabled) ?? cart.querySelector('[data-cart-focus-fallback]'));
};

const announce = (cart) => {
    const announcer = document.querySelector('[data-cart-announcer]');
    const message = cart.querySelector('[role="status"], [role="alert"]');

    if (announcer) {
        announcer.textContent = message?.textContent.trim() ?? '';
    }
};

/** Gửi form theo cách thường (dự phòng), giữ giá trị của nút đã bấm (vd. quantity). */
const submitNatively = (form, submitter) => {
    if (submitter?.name) {
        const input = Object.assign(document.createElement('input'), { type: 'hidden', name: submitter.name, value: submitter.value });

        form.append(input);
    }

    HTMLFormElement.prototype.submit.call(form);
};

document.addEventListener('submit', async (event) => {
    const form = event.target.closest('form[data-cart-form]');
    const cart = form?.closest('[data-cart]');

    if (!cart || event.defaultPrevented) {
        return;
    }

    event.preventDefault();

    if (cart.getAttribute('aria-busy') === 'true') {
        return;
    }

    const submitter = event.submitter;
    const buttonIndex = [...form.querySelectorAll('button')].indexOf(submitter);
    const body = new FormData(form, submitter);

    cart.setAttribute('aria-busy', 'true');

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body,
            headers: { Accept: 'text/html' },
            credentials: 'same-origin',
        });
        const page = parser.parseFromString(await response.text(), 'text/html');
        const nextCart = page.querySelector('[data-cart]');

        if (!response.ok || !nextCart) {
            throw new Error(`Cart update failed (${response.status})`);
        }

        cart.replaceWith(nextCart);
        document.querySelectorAll('[data-cart-count]').forEach((count, index) => {
            const next = page.querySelectorAll('[data-cart-count]')[index];

            if (next) {
                count.replaceWith(next);
            }
        });

        announce(nextCart);
        focusTarget(nextCart, formKey(form), buttonIndex)?.focus({ preventScroll: true });
    } catch {
        submitNatively(form, submitter);
    }
});
