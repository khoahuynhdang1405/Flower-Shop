/**
 * Drawer cho sidebar admin trên màn hình nhỏ (< lg).
 * Mở bằng nút menu trên header; đóng bằng Esc, lớp phủ, nút đóng, hoặc khi màn hình rộng ra.
 * Lắng nghe ở document và tìm phần tử lúc xảy ra sự kiện, để vẫn chạy sau khi <body> được thay
 * (ví dụ khi chuyển ngôn ngữ).
 */
const desktop = window.matchMedia('(min-width: 64rem)');

const parts = () => ({
    drawer: document.querySelector('[data-admin-drawer]'),
    toggle: document.querySelector('[data-admin-drawer-toggle]'),
    overlay: document.querySelector('[data-admin-drawer-overlay]'),
    closeButton: document.querySelector('[data-admin-drawer-close]'),
});

const isOpen = () => parts().drawer?.hasAttribute('data-open') ?? false;

const open = () => {
    const { drawer, toggle, overlay, closeButton } = parts();

    drawer.setAttribute('data-open', '');
    overlay.hidden = false;
    toggle.setAttribute('aria-expanded', 'true');
    document.documentElement.classList.add('overflow-hidden');
    closeButton.focus();
};

const close = ({ restoreFocus = true } = {}) => {
    if (!isOpen()) {
        return;
    }

    const { drawer, toggle, overlay } = parts();

    drawer.removeAttribute('data-open');
    overlay.hidden = true;
    toggle.setAttribute('aria-expanded', 'false');
    document.documentElement.classList.remove('overflow-hidden');

    if (restoreFocus) {
        toggle.focus();
    }
};

document.addEventListener('click', (event) => {
    if (event.target.closest('[data-admin-drawer-toggle]')) {
        isOpen() ? close() : open();
    } else if (event.target.closest('[data-admin-drawer-close], [data-admin-drawer-overlay]')) {
        close();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        close();
    }
});

desktop.addEventListener('change', (event) => {
    if (event.matches) {
        close({ restoreFocus: false });
    }
});
