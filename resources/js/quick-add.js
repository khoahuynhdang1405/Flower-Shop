/**
 * Nút "+" trên thẻ sản phẩm (form[data-quick-add]): thêm 1 bó cỡ mặc định vào giỏ qua POST /api/cart/items,
 * không rời trang. Thành công: nút hiện dấu ✓ một lúc, số trên icon giỏ [data-cart-count] tăng, hiện thông báo
 * [data-cart-toast] "Đã thêm … · Xem giỏ". Lỗi kiểm tra (422, vd. hoa vừa ngừng bán): báo lỗi trong thông báo.
 *
 * Phiên hết hạn (419) hoặc lỗi mạng thì gửi form theo cách thường (về trang giỏ). Không có JavaScript form vẫn chạy.
 */
const ADDED_MS = 1500;
const TOAST_MS = 3500;
const API_URL = '/api/cart/items';

let toastTimer = null;

const updateCartCount = (count) => {
    document.querySelectorAll('[data-cart-count]').forEach((link) => {
        const number = link.querySelector('[data-cart-count-number]');
        const label = link.querySelector('[data-cart-count-label]');

        if (number) {
            number.textContent = `(${count})`;
        }

        if (label) {
            label.textContent = label.dataset.cartCountLabel.replace(':count', count);
        }
    });
};

const hideToast = () => {
    const toast = document.querySelector('[data-cart-toast]');

    clearTimeout(toastTimer);

    if (toast) {
        toast.hidden = true;
    }
};

/** @param {{ message: string, isError: boolean }} options */
const showToast = ({ message, isError }) => {
    const toast = document.querySelector('[data-cart-toast]');
    const live = document.querySelector('[data-cart-toast-live]');

    if (!toast) {
        return;
    }

    toast.querySelector('[data-cart-toast-message]').textContent = message;
    toast.querySelector('[data-cart-toast-link]').hidden = isError;
    toast.querySelector('[data-cart-toast-icon]')?.classList.toggle('hidden', isError);
    toast.hidden = false;

    if (live) {
        // Xóa rồi ghi lại để trình đọc màn hình đọc cả khi thêm cùng một bó hai lần.
        live.textContent = '';
        requestAnimationFrame(() => {
            live.textContent = message;
        });
    }

    clearTimeout(toastTimer);
    toastTimer = setTimeout(hideToast, TOAST_MS);
};

const markAdded = (button) => {
    button.dataset.added = '';
    setTimeout(() => delete button.dataset.added, ADDED_MS);
};

/** @param {HTMLFormElement} form */
const quickAdd = async (form) => {
    const button = form.querySelector('button[type="submit"]');
    const toast = document.querySelector('[data-cart-toast]');

    button.disabled = true;

    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            body: new FormData(form),
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        if (response.status === 419) {
            form.submit();

            return;
        }

        const payload = await response.json().catch(() => ({}));

        if (!response.ok) {
            showToast({ message: response.status === 422 && payload.message ? payload.message : toast?.dataset.failedMessage ?? '', isError: true });

            return;
        }

        updateCartCount(payload.data?.item_count ?? 0);
        markAdded(button);
        showToast({ message: (toast?.dataset.addedTemplate ?? '').replace(':name', form.dataset.name), isError: false });
    } catch {
        form.submit();
    } finally {
        button.disabled = false;
    }
};

document.addEventListener('submit', (event) => {
    const form = event.target.closest?.('form[data-quick-add]');

    if (!form) {
        return;
    }

    event.preventDefault();

    if (!form.querySelector('button[type="submit"]').disabled) {
        quickAdd(form);
    }
});

document.addEventListener('click', (event) => {
    if (event.target.closest('[data-cart-toast-close]')) {
        hideToast();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        hideToast();
    }
});
