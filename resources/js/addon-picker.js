/**
 * Khối "Thêm một chút thương yêu" ở chi tiết sản phẩm: khi tích/bỏ tích [data-addon-option],
 * dựng lại dòng tóm tắt [data-addon-summary] (tên món đã chọn + tổng tiền thêm) theo các mẫu câu
 * server đặt sẵn trên [data-addon-picker] — cùng cách tóm tắt render lần đầu trong addon-picker.blade.php.
 */
const formatMoney = (amount, picker) =>
    `${String(amount).replace(/\B(?=(\d{3})+(?!\d))/g, picker.dataset.thousands)} ${picker.dataset.currency}`;

const joinNames = (names, and) =>
    names.length > 1 ? `${names.slice(0, -1).join(', ')}${and}${names.at(-1)}` : names[0];

document.addEventListener('change', (event) => {
    const option = event.target.closest?.('[data-addon-option]');
    const picker = option?.closest('[data-addon-picker]');
    const summary = picker?.querySelector('[data-addon-summary]');

    if (!summary) {
        return;
    }

    const chosen = [...picker.querySelectorAll('[data-addon-option]:checked')];
    const total = chosen.reduce((sum, input) => sum + Number(input.dataset.price), 0);
    const items = joinNames(chosen.map((input) => input.dataset.name), picker.dataset.and);

    if (chosen.length === 0) {
        summary.textContent = picker.dataset.summaryEmpty;
    } else if (total === 0) {
        summary.textContent = picker.dataset.summaryFree.replace(':items', items);
    } else {
        summary.textContent = picker.dataset.summary.replace(':items', items).replace(':total', formatMoney(total, picker));
    }
});
