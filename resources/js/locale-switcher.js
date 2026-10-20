/**
 * Chuyển ngôn ngữ mượt: nền trượt ngay khi bấm, gửi form ngầm, rồi thay nội dung trang
 * (kèm hiệu ứng mờ chéo nếu trình duyệt hỗ trợ View Transitions) thay vì tải lại cả trang.
 * Nếu có lỗi, quay về gửi form bình thường.
 */
const SLIDE_DURATION = 200;
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

const swapPage = (html, locale) => {
    const next = new DOMParser().parseFromString(html, 'text/html');
    const scrollY = window.scrollY;

    document.documentElement.lang = next.documentElement.lang;
    document.title = next.title;
    document.body.replaceWith(document.adoptNode(next.body));
    window.scrollTo(0, scrollY);

    document.querySelector(`[data-locale-switcher] button[lang="${locale}"]`)?.focus();
};

document.addEventListener('submit', async (event) => {
    const form = event.target.closest('[data-locale-form]');

    if (!form) {
        return;
    }

    event.preventDefault();

    const switcher = form.closest('[data-locale-switcher]');
    const button = form.querySelector('button');

    if (button.getAttribute('aria-pressed') === 'true' || switcher.hasAttribute('aria-busy')) {
        return;
    }

    switcher.setAttribute('aria-busy', 'true');
    switcher.style.setProperty('--active-index', form.dataset.index);
    switcher.querySelectorAll('button').forEach((other) => {
        other.setAttribute('aria-pressed', String(other === button));
    });

    try {
        const [response] = await Promise.all([
            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { Accept: 'text/html' },
                credentials: 'same-origin',
            }),
            wait(reducedMotion.matches ? 0 : SLIDE_DURATION),
        ]);

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const html = await response.text();
        const swap = () => swapPage(html, button.lang);

        if (document.startViewTransition && !reducedMotion.matches) {
            document.startViewTransition(swap);
        } else {
            swap();
        }
    } catch {
        form.submit();
    }
});
