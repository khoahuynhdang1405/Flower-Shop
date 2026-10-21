/**
 * Menu thả xuống dùng <details data-dropdown> (chạy được không cần JS).
 * JS chỉ bổ sung: đóng khi bấm ra ngoài, khi nhấn Esc, và chỉ mở một menu tại một thời điểm.
 */
const openDropdowns = () => document.querySelectorAll('details[data-dropdown][open]');

document.addEventListener('click', (event) => {
    openDropdowns().forEach((dropdown) => {
        if (!dropdown.contains(event.target)) {
            dropdown.open = false;
        }
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
        return;
    }

    openDropdowns().forEach((dropdown) => {
        dropdown.open = false;
        dropdown.querySelector('summary')?.focus();
    });
});
