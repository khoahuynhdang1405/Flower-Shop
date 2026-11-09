/**
 * Trang giỏ hàng: các form trong [data-cart] (− / +, Xóa, mã ưu đãi) gửi bằng fetch thay vì tải lại trang.
 * Server vẫn trả về trang giỏ như khi gửi form thường (redirect về /cart, kèm thông báo / lỗi trong session);
 * script chỉ thay nội dung [data-cart] và số trên icon giỏ [data-cart-count], giữ nguyên vị trí cuộn.
 *
 * Để thao tác mượt:
 * - Bấm − / + đổi số ngay trên màn hình (không chờ server); bấm liên tiếp được gộp thành một request.
 * - Request gửi lần lượt theo hàng đợi; chỉ thay [data-cart] khi hàng đợi đã trống, nên không ghi đè lúc khách đang bấm.
 * - Không làm mờ gì trong lúc chờ: số tiền giữ nguyên tới khi có số mới; bấm Xóa thì ẩn dòng ngay ([data-cart-removing]).
 *
 * Lỗi mạng / phiên hết hạn (419...) thì gửi form theo cách thường. Không có JavaScript form vẫn chạy bình thường.
 */
const parser = new DOMParser();
const QUANTITY_DEBOUNCE_MS = 350;

/** Form số lượng (PATCH) và form xóa (DELETE) của cùng một dòng dùng chung URL — phân biệt thêm bằng _method. */
const formKey = (form) => `${form.querySelector('input[name="_method"]')?.value ?? 'POST'} ${form.action}`;

/** @type {{ form: HTMLFormElement, key: string, body: FormData, submitter: {name: string, value: string}|null, buttonIndex: number }[]} */
const queue = [];
let isRunning = false;
let debounceTimer = null;

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

/** Đổi số lượng hiển thị ngay, cập nhật giá trị và trạng thái khóa của hai nút − / +. */
const showQuantity = (form, quantity) => {
    const max = Number(form.dataset.max);

    form.querySelectorAll('button[data-step]').forEach((button) => {
        const next = quantity + Number(button.dataset.step);

        button.value = String(next);
        button.disabled = next < 1 || next > max;
    });

    const value = form.querySelector('[data-quantity-value]');
    const label = form.querySelector('[data-quantity-label]');

    if (value) {
        value.textContent = String(quantity);
    }

    if (label) {
        label.textContent = label.dataset.template.replace(':quantity', String(quantity));
    }
};

/** Đánh dấu giỏ đang cập nhật (cho trình đọc màn hình); bấm Xóa thì ẩn dòng ngay. Không làm mờ gì cả. */
const markPending = (cart, form) => {
    cart.setAttribute('aria-busy', 'true');

    if (form.querySelector('input[name="_method"]')?.value === 'DELETE') {
        form.closest('li')?.setAttribute('data-cart-removing', '');
    }
};

const send = async (job) => {
    const response = await fetch(job.form.action, {
        method: 'POST',
        body: job.body,
        headers: { Accept: 'text/html' },
        credentials: 'same-origin',
    });
    const page = parser.parseFromString(await response.text(), 'text/html');

    if (!response.ok || !page.querySelector('[data-cart]')) {
        throw new Error(`Cart update failed (${response.status})`);
    }

    return page;
};

const render = (page, job) => {
    const cart = document.querySelector('[data-cart]');
    const nextCart = page.querySelector('[data-cart]');
    const hadFocus = cart.contains(document.activeElement) || document.activeElement === document.body;

    cart.replaceWith(nextCart);
    document.querySelectorAll('[data-cart-count]').forEach((count, index) => {
        const next = page.querySelectorAll('[data-cart-count]')[index];

        if (next) {
            count.replaceWith(next);
        }
    });

    announce(nextCart);

    if (hadFocus) {
        focusTarget(nextCart, job.key, job.buttonIndex)?.focus({ preventScroll: true });
    }
};

/** Gửi lần lượt các request trong hàng đợi; chỉ vẽ lại giỏ từ response cuối cùng. */
const run = async () => {
    if (isRunning) {
        return;
    }

    isRunning = true;
    let page = null;
    let job = null;

    try {
        while (queue.length > 0) {
            job = queue.shift();
            page = await send(job);
        }

        render(page, job);
    } catch {
        submitNatively(job.form, job.submitter);
    } finally {
        isRunning = false;
    }
};

const enqueue = (job, delay) => {
    const existing = queue.findIndex((queued) => queued.key === job.key);

    // Hai lần đổi số lượng cùng một dòng: chỉ cần gửi giá trị mới nhất.
    if (existing !== -1) {
        queue.splice(existing, 1);
    }

    queue.push(job);
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(run, delay);
};

document.addEventListener('submit', (event) => {
    const form = event.target.closest('form[data-cart-form]');
    const cart = form?.closest('[data-cart]');

    if (!cart || event.defaultPrevented) {
        return;
    }

    event.preventDefault();

    const submitter = event.submitter;
    const isQuantityStep = submitter?.hasAttribute('data-step') ?? false;

    // Lấy dữ liệu form trước khi showQuantity() đổi giá trị các nút.
    const job = {
        form,
        key: formKey(form),
        body: new FormData(form, submitter),
        submitter: submitter?.name ? { name: submitter.name, value: submitter.value } : null,
        buttonIndex: [...form.querySelectorAll('button')].indexOf(submitter),
    };

    if (isQuantityStep) {
        showQuantity(form, Number(submitter.value));
    }

    markPending(cart, form);
    enqueue(job, isQuantityStep ? QUANTITY_DEBOUNCE_MS : 0);
});
