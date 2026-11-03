/**
 * Bộ lọc danh sách hoa (storefront, trang Tất cả hoa):
 * - form[data-auto-submit]: gửi lại form ngay khi đổi một lựa chọn, kể cả control nằm ngoài form qua thuộc tính form="".
 * - details[data-collapse-below-lg]: HTML mở sẵn (chạy được khi tắt JS); dưới 1024px thu gọn để lưới hoa không bị đẩy xuống xa.
 */
document.addEventListener('change', (event) => {
    const form = event.target.form;

    if (form?.matches('[data-auto-submit]')) {
        form.requestSubmit();
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
