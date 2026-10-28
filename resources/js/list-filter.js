/**
 * Tìm kiếm + lọc danh sách admin (chỉ phía trình duyệt):
 * [data-list-filter] chứa input[data-list-search], select[data-list-select="<field>"]
 * và các mục [data-list-item data-search="<chữ để tìm>" data-<field>="<giá trị>"].
 * [data-list-empty] hiện khi không còn mục nào; [data-list-count] đổi chữ theo data-all / data-some (:shown, :total).
 */
const normalize = (text) =>
    text
        .toLowerCase()
        .normalize('NFD')
        .replace(/\p{Diacritic}/gu, '')
        .replace(/đ/g, 'd')
        .trim();

const applyFilters = (container) => {
    const query = normalize(container.querySelector('[data-list-search]')?.value ?? '');
    const selects = [...container.querySelectorAll('[data-list-select]')];
    const items = [...container.querySelectorAll('[data-list-item]')];
    let shown = 0;

    items.forEach((item) => {
        const matchesQuery = query === '' || normalize(item.dataset.search ?? '').includes(query);
        const matchesSelects = selects.every(
            (select) => select.value === '' || item.dataset[select.dataset.listSelect] === select.value,
        );
        const visible = matchesQuery && matchesSelects;

        item.hidden = !visible;
        shown += visible ? 1 : 0;
    });

    const empty = container.querySelector('[data-list-empty]');

    if (empty) {
        empty.hidden = shown > 0;
    }

    const count = container.querySelector('[data-list-count]');

    if (count) {
        const template = shown === items.length ? count.dataset.all : count.dataset.some;

        count.textContent = template.replace(':shown', shown).replace(':total', items.length);
    }
};

const handle = (event) => {
    const container = event.target.closest('[data-list-filter]');

    if (container && event.target.matches('[data-list-search], [data-list-select]')) {
        applyFilters(container);
    }
};

document.addEventListener('input', handle);
document.addEventListener('change', handle);
