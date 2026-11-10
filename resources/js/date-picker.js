/**
 * Ô chọn ngày [data-date-picker] (resources/views/components/form/date-picker.blade.php).
 * - Ẩn <input type="date"> gốc (vẫn giữ giá trị Y-m-d và kiểm tra required/min/max), hiện nút + lịch tháng.
 * - Bàn phím theo mẫu WAI-ARIA Date Picker Dialog: mũi tên đi từng ngày/tuần, PageUp/PageDown đổi tháng
 *   (thêm Shift: đổi năm), Home/End về đầu/cuối tuần, Enter/Space chọn, Esc đóng.
 * - Đọc lại min/max mỗi lần vẽ, nên trang khác có thể đổi input.min (VD: hôm nay đã hết khung giờ).
 * - Chọn ngày thì phát sự kiện input + change trên ô gốc như khi khách tự gõ.
 */
const DAY = 86400000;

/** Ngày tính theo UTC để cộng trừ không lệch vì múi giờ. */
const parse = (value) => {
    const [year, month, day] = value.split('-').map(Number);

    return new Date(Date.UTC(year, month - 1, day));
};
const iso = (date) => date.toISOString().slice(0, 10);
const addDays = (value, days) => iso(new Date(parse(value).getTime() + days * DAY));
const addMonths = (value, months) => {
    const date = parse(value);
    const target = new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth() + months, 1));
    const lastDay = new Date(Date.UTC(target.getUTCFullYear(), target.getUTCMonth() + 1, 0)).getUTCDate();

    target.setUTCDate(Math.min(date.getUTCDate(), lastDay));

    return iso(target);
};
/** Thứ trong tuần, Thứ Hai = 0 … Chủ Nhật = 6. */
const weekdayIndex = (value) => (parse(value).getUTCDay() + 6) % 7;
const pad = (number) => String(number).padStart(2, '0');

function setUp(picker) {
    const input = picker.querySelector('[data-date-picker-input]');
    const trigger = picker.querySelector('[data-date-picker-trigger]');
    const valueText = picker.querySelector('[data-date-picker-value]');
    const dialog = picker.querySelector('[data-date-picker-dialog]');
    const monthTitle = picker.querySelector('[data-date-picker-month]');
    const grid = picker.querySelector('[data-date-picker-grid]');
    const previous = picker.querySelector('[data-date-picker-prev]');
    const next = picker.querySelector('[data-date-picker-next]');
    const error = picker.querySelector('[data-date-picker-error]');
    const errorText = picker.querySelector('[data-date-picker-error-text]');
    const labels = JSON.parse(picker.dataset.labels);

    /** Ngày đang có focus trong lịch (Y-m-d). */
    let focused = '';

    const clamp = (value) => {
        if (input.min && value < input.min) {
            return input.min;
        }

        return input.max && value > input.max ? input.max : value;
    };

    const format = (value) => {
        const [year, month, day] = value.split('-');

        return `${labels.weekdays[weekdayIndex(value)]}, ${day}/${month}/${year}`;
    };

    const showError = (message) => {
        error.classList.toggle('hidden', !message);
        errorText.textContent = message;
        trigger.setAttribute('aria-invalid', String(Boolean(message)));
    };

    const syncTrigger = () => {
        valueText.textContent = input.value ? format(input.value) : valueText.dataset.placeholder;
        valueText.toggleAttribute('data-empty', !input.value);
    };

    const render = () => {
        const first = `${focused.slice(0, 7)}-01`;
        const [year, month] = focused.split('-').map(Number);
        const daysInMonth = new Date(Date.UTC(year, month, 0)).getUTCDate();

        monthTitle.textContent = labels.month.replace(':month', month).replace(':year', year);
        previous.disabled = Boolean(input.min) && first <= input.min;
        next.disabled = Boolean(input.max) && addMonths(first, 1) > input.max;

        const cells = Array.from({ length: weekdayIndex(first) }, () => '<td></td>');

        for (let day = 1; day <= daysInMonth; day++) {
            const value = `${focused.slice(0, 8)}${pad(day)}`;
            const isDisabled = value !== clamp(value);

            cells.push(
                `<td role="gridcell" data-date="${value}" tabindex="${value === focused ? 0 : -1}" aria-label="${format(value)}"` +
                    ` aria-selected="${value === input.value}"${isDisabled ? ' aria-disabled="true"' : ''}` +
                    ' class="h-10 cursor-pointer rounded-sm text-lh-primary-dark tabular-nums transition-colors hover:bg-lh-addon-selected' +
                    ' focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-lh-primary' +
                    ' aria-selected:bg-lh-addon-accent aria-selected:text-white' +
                    ' aria-disabled:cursor-not-allowed aria-disabled:text-lh-border aria-disabled:hover:bg-transparent">' +
                    `${day}</td>`,
            );
        }

        const rows = [];

        for (let index = 0; index < cells.length; index += 7) {
            rows.push(`<tr>${cells.slice(index, index + 7).join('')}</tr>`);
        }

        grid.innerHTML = rows.join('');
    };

    const moveFocus = (value) => {
        focused = clamp(value);
        render();
        grid.querySelector(`[data-date="${focused}"]`).focus();
    };

    const open = () => {
        focused = clamp(input.value || input.min || iso(new Date()));
        dialog.hidden = false;
        trigger.setAttribute('aria-expanded', 'true');
        moveFocus(focused);
    };

    const close = (returnFocus = true) => {
        if (dialog.hidden) {
            return;
        }

        dialog.hidden = true;
        trigger.setAttribute('aria-expanded', 'false');

        if (returnFocus) {
            trigger.focus();
        }
    };

    const select = (value) => {
        if (value !== clamp(value)) {
            return;
        }

        input.value = value;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
        close();
    };

    // Bật giao diện lịch: ô gốc chỉ còn giữ giá trị (vẫn nằm trong form để kiểm tra hợp lệ).
    input.classList.add('sr-only');
    input.tabIndex = -1;
    input.setAttribute('aria-hidden', 'true');
    trigger.hidden = false;
    picker.querySelector('label').htmlFor = trigger.id;
    syncTrigger();

    trigger.addEventListener('click', () => (dialog.hidden ? open() : close()));
    previous.addEventListener('click', () => {
        focused = clamp(addMonths(focused, -1));
        render();
    });
    next.addEventListener('click', () => {
        focused = clamp(addMonths(focused, 1));
        render();
    });

    grid.addEventListener('click', (event) => {
        const cell = event.target.closest('[data-date]');

        if (cell) {
            select(cell.dataset.date);
        }
    });

    grid.addEventListener('keydown', (event) => {
        const moves = {
            ArrowLeft: () => addDays(focused, -1),
            ArrowRight: () => addDays(focused, 1),
            ArrowUp: () => addDays(focused, -7),
            ArrowDown: () => addDays(focused, 7),
            PageUp: () => addMonths(focused, event.shiftKey ? -12 : -1),
            PageDown: () => addMonths(focused, event.shiftKey ? 12 : 1),
            Home: () => addDays(focused, -weekdayIndex(focused)),
            End: () => addDays(focused, 6 - weekdayIndex(focused)),
        };

        if (moves[event.key]) {
            event.preventDefault();
            moveFocus(moves[event.key]());
        } else if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            select(focused);
        }
    });

    picker.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !dialog.hidden) {
            event.preventDefault();
            close();
        }
    });

    // Bấm ra ngoài hoặc Tab ra khỏi ô chọn ngày thì đóng lịch.
    document.addEventListener('click', (event) => {
        if (!picker.contains(event.target)) {
            close(false);
        }
    });
    picker.addEventListener('focusout', (event) => {
        if (event.relatedTarget && !picker.contains(event.relatedTarget)) {
            close(false);
        }
    });

    input.addEventListener('change', () => {
        syncTrigger();
        showError('');
    });

    // Báo lỗi ngay dưới ô (ô gốc đang ẩn nên bong bóng của trình duyệt sẽ chỉ vào chỗ trống).
    input.addEventListener('invalid', (event) => {
        event.preventDefault();
        showError(input.validity.valueMissing && labels.required ? labels.required : input.validationMessage);

        if (input.form?.querySelector(':invalid') === input) {
            trigger.focus();
        }
    });
}

document.querySelectorAll('[data-date-picker]').forEach(setUp);
