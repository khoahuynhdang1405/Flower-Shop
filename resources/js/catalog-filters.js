/**
 * Bộ lọc trang "Tất cả hoa" — lọc không tải lại trang:
 * - Đổi lựa chọn trong form[data-catalog-form] (kể cả control ngoài form qua thuộc tính form=""), bấm link
 *   phân trang [data-catalog-page] hoặc "Xóa tất cả" [data-catalog-reset]: tải trang mới bằng fetch rồi chỉ thay
 *   nội dung các vùng [data-catalog-region], cập nhật URL bằng history.pushState (Back / Forward vẫn dùng được).
 * - Khi lọc chỉ thay lưới hoa + dòng đếm để checkbox đang bấm không mất focus; khi xóa lọc / Back thì thay cả form.
 * - Lỗi mạng: chuyển trang bình thường. Tắt JS: form GET vẫn chạy (nút "Áp dụng" trong <noscript>).
 * - details[data-collapse-below-lg]: HTML mở sẵn; dưới 1024px thu gọn để lưới hoa không bị đẩy xuống xa.
 */
const RESULT_REGIONS = ['count', 'results'];
const ALL_REGIONS = [...RESULT_REGIONS, 'filters', 'sort'];

let pending = null;

const queryOf = (form) => {
    const params = new URLSearchParams();

    new FormData(form).forEach((value, name) => {
        if (value !== '') {
            params.append(name, value);
        }
    });

    return params.toString();
};

const load = async (url, { regions, push = true, scroll = false }) => {
    const results = document.querySelector('[data-catalog-results]');

    pending?.abort();
    pending = new AbortController();
    results?.setAttribute('aria-busy', 'true');

    try {
        const response = await fetch(url, { headers: { Accept: 'text/html' }, signal: pending.signal });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const page = new DOMParser().parseFromString(await response.text(), 'text/html');

        regions.forEach((name) => {
            const current = document.querySelector(`[data-catalog-region="${name}"]`);
            const fresh = page.querySelector(`[data-catalog-region="${name}"]`);

            if (current && fresh) {
                current.innerHTML = fresh.innerHTML;
            }
        });

        if (push) {
            history.pushState({ catalog: true }, '', response.url);
        }

        if (scroll && results && results.getBoundingClientRect().top < 0) {
            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            results.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth' });
        }

        results?.removeAttribute('aria-busy');
    } catch (error) {
        if (error.name !== 'AbortError') {
            window.location.assign(url);
        }
    }
};

document.addEventListener('change', (event) => {
    const form = event.target.form;

    if (form?.matches('[data-catalog-form]')) {
        load(`${form.action}?${queryOf(form)}`, { regions: RESULT_REGIONS });
    }
});

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (form.matches('[data-catalog-form]')) {
        event.preventDefault();
        load(`${form.action}?${queryOf(form)}`, { regions: RESULT_REGIONS });
    }
});

document.addEventListener('click', (event) => {
    const link = event.target.closest('a[data-catalog-page], a[data-catalog-reset]');

    if (!link || event.metaKey || event.ctrlKey || event.shiftKey || event.button !== 0) {
        return;
    }

    event.preventDefault();

    if (link.matches('[data-catalog-page]')) {
        load(link.href, { regions: RESULT_REGIONS, scroll: true });
    } else {
        load(link.href, { regions: ALL_REGIONS });
    }
});

window.addEventListener('popstate', () => {
    if (document.querySelector('[data-catalog-form]')) {
        load(window.location.href, { regions: ALL_REGIONS, push: false });
    }
});

const desktop = window.matchMedia('(min-width: 64rem)');

const syncPanels = () => {
    document.querySelectorAll('details[data-collapse-below-lg]').forEach((panel) => {
        panel.open = desktop.matches;
    });
};

syncPanels();
desktop.addEventListener('change', syncPanels);
