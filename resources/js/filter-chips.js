/**
 * Lọc danh sách bằng chip (chỉ phía trình duyệt):
 * [data-filter-group] chứa các nút [data-filter-chip="<giá trị>|all"] và mục [data-filter-item data-filter-value="<giá trị>"].
 * [data-filter-empty] hiện khi không còn mục nào.
 */
document.addEventListener('click', (event) => {
    const chip = event.target.closest('[data-filter-chip]');

    if (!chip) {
        return;
    }

    const group = chip.closest('[data-filter-group]');
    const value = chip.dataset.filterChip;
    let visibleCount = 0;

    group.querySelectorAll('[data-filter-chip]').forEach((other) => {
        other.setAttribute('aria-pressed', String(other === chip));
    });

    group.querySelectorAll('[data-filter-item]').forEach((item) => {
        const visible = value === 'all' || item.dataset.filterValue === value;

        item.hidden = !visible;
        visibleCount += visible ? 1 : 0;
    });

    const empty = group.querySelector('[data-filter-empty]');

    if (empty) {
        empty.hidden = visibleCount > 0;
    }
});
