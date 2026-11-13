/**
 * Trang thanh toán (resources/views/checkout/show.blade.php), chỉ phía trình duyệt:
 * - "Tôi là người nhận": tên + SĐT người nhận lấy theo người đặt (chỉ đọc), ẩn khối thiệp.
 * - Ô "Người gửi" / "Người nhận" trên thiệp tự điền tên gọi (hai chữ cuối của họ tên), cho tới khi khách tự sửa.
 * - Khối "Lịch hẹn", nhãn phí giao theo quận, ghi chú phương thức thanh toán cập nhật ngay.
 * - Hôm nay: khóa khung giờ đã bắt đầu; hết khung giờ thì ngày giao sớm nhất là ngày mai.
 * - Gửi form: kiểm tra dữ liệu, hợp lệ thì gửi đơn (OrderController@store) và khóa nút "Đặt hoa".
 */
const root = document.querySelector('[data-checkout]');

if (root) {
    const form = root.querySelector('[data-checkout-form]');
    const field = (name) => form.elements.namedItem(name);
    const checkedValue = (name) => form.querySelector(`input[name="${name}"]:checked`)?.value ?? '';
    const isSelf = () => checkedValue('is_gift') === '0';

    const formatDate = (value) => {
        const [year, month, day] = value.split('-');

        return `${day}/${month}/${year}`;
    };

    let wasSelf = false;

    const syncReceiver = () => {
        const self = isSelf();

        [
            ['receiver_name', 'sender_name'],
            ['receiver_phone', 'sender_phone'],
        ].forEach(([receiver, sender]) => {
            field(receiver).readOnly = self;

            if (self) {
                field(receiver).value = field(sender).value;
            } else if (wasSelf) {
                // Vừa chuyển lại "Gửi tặng": bỏ thông tin người đặt đã chép sang để khách nhập người nhận thật.
                field(receiver).value = '';
            }
        });

        wasSelf = self;

        const card = root.querySelector('[data-gift-only]');

        card.hidden = self;
        card.querySelectorAll('input, textarea').forEach((input) => {
            input.disabled = self;
        });
    };

    /** Tên gọi trên thiệp: hai chữ cuối của họ tên ("Huỳnh Đăng Khoa" → "Đăng Khoa"). */
    const callName = (fullName) => fullName.trim().split(/\s+/).slice(-2).join(' ');

    const syncCard = () => {
        [
            ['card_sender', 'sender_name'],
            ['card_receiver', 'receiver_name'],
        ].forEach(([card, source]) => {
            if (!field(card).dataset.touched) {
                field(card).value = callName(field(source).value);
            }
        });
    };

    const slots = root.querySelector('[data-time-slots]');
    const slotInputs = [...slots.querySelectorAll('input[name="time_slot"]')];

    /** Ngày (Y-m-d) và giờ (HH:MM) hiện tại theo múi giờ của tiệm. */
    const shopNow = () => {
        const parts = Object.fromEntries(
            new Intl.DateTimeFormat('en-CA', {
                timeZone: slots.dataset.timezone,
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                hourCycle: 'h23',
            })
                .formatToParts(new Date())
                .map(({ type, value }) => [type, value]),
        );

        return { date: `${parts.year}-${parts.month}-${parts.day}`, time: `${parts.hour}:${parts.minute}` };
    };

    const hasStarted = (slot, now) => slot.split('-')[0] <= now.time;

    /**
     * Khung giờ đã bắt đầu thì không nhận đơn cho hôm nay; hết khung giờ thì ngày sớm nhất lùi sang ngày mai.
     */
    const syncTimeSlots = () => {
        const date = field('delivery_date');
        const now = shopNow();

        date.dataset.min ??= date.min;

        if (slotInputs.every((input) => hasStarted(input.value, now))) {
            const tomorrow = new Date(`${now.date}T00:00:00Z`);

            tomorrow.setUTCDate(tomorrow.getUTCDate() + 1);
            date.min = [date.dataset.min, tomorrow.toISOString().slice(0, 10)].sort().pop();
        }

        if (date.value && date.value < date.min) {
            date.value = '';
            date.dispatchEvent(new Event('change', { bubbles: true }));
        }

        const isToday = date.value === now.date;

        slotInputs.forEach((input) => {
            input.disabled = isToday && hasStarted(input.value, now);
            input.checked &&= !input.disabled;
        });

        slots.querySelector('[data-time-slots-past-note]').hidden = !slotInputs.some((input) => input.disabled);
    };

    const syncAppointment = () => {
        const when = root.querySelector('[data-appointment-when]');
        const date = field('delivery_date').value;
        const slot = checkedValue('time_slot').replace('-', '–');

        when.textContent = date || slot ? [date && formatDate(date), slot].filter(Boolean).join(' · ') : when.dataset.empty;

        root.querySelector('[data-appointment-who]').textContent = [field('receiver_name').value.trim(), field('receiver_phone').value.trim()]
            .filter(Boolean)
            .join(' · ');
    };

    const syncShipping = () => {
        const label = root.querySelector('[data-shipping-label]');
        const district = field('shipping_district').value;

        label.dataset.default ??= label.textContent.trim();
        label.textContent = district ? label.dataset.template.replace(':district', district) : label.dataset.default;
    };

    const syncPaymentNote = () => {
        const note = root.querySelector('[data-payment-note]');

        note.textContent = note.getAttribute(`data-note-${checkedValue('payment_method')}`) ?? note.textContent;
    };

    const syncAll = () => {
        syncTimeSlots();
        syncReceiver();
        syncCard();
        syncAppointment();
        syncShipping();
        syncPaymentNote();
    };

    form.addEventListener('input', (event) => {
        if (event.target.name === 'card_sender' || event.target.name === 'card_receiver') {
            event.target.dataset.touched = event.target.value === '' ? '' : '1';
        }

        syncAll();
    });
    form.addEventListener('change', syncAll);

    form.addEventListener('submit', (event) => {
        if (!form.reportValidity()) {
            event.preventDefault();

            return;
        }

        // Nút "Đặt hoa" nằm ngoài form (form="checkout-form"): khóa lại để khách không gửi đơn hai lần.
        root.querySelector('[data-checkout-submit]').disabled = true;
    });

    // Khách bấm Back từ trang VNPAY/MoMo (trang lấy lại từ bộ nhớ đệm): mở lại nút.
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            root.querySelector('[data-checkout-submit]').disabled = false;
        }
    });

    syncAll();

    // Trang để mở lâu thì khung giờ của hôm nay vẫn khóa đúng lúc.
    setInterval(syncAll, 60000);
}
