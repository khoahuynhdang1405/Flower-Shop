/**
 * Ô số lượng [data-quantity]: nút [data-quantity-step="-1|1"] tăng/giảm [data-quantity-input] trong khoảng min–max;
 * nút "−" bị khóa ở mức tối thiểu, "+" ở mức tối đa. Gõ tay ngoài khoảng sẽ được kéo về khi rời ô.
 */
const clamp = (input, value) => {
    const min = Number(input.min) || 1;
    const max = Number(input.max) || 99;

    return Math.min(Math.max(Number.isFinite(value) ? Math.round(value) : min, min), max);
};

const sync = (stepper) => {
    const input = stepper.querySelector('[data-quantity-input]');
    const value = Number(input.value);

    stepper.querySelector('[data-quantity-step="-1"]').disabled = value <= (Number(input.min) || 1);
    stepper.querySelector('[data-quantity-step="1"]').disabled = value >= (Number(input.max) || 99);
};

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-quantity-step]');
    const stepper = button?.closest('[data-quantity]');

    if (!stepper) {
        return;
    }

    const input = stepper.querySelector('[data-quantity-input]');

    input.value = clamp(input, Number(input.value) + Number(button.dataset.quantityStep));
    sync(stepper);
});

document.addEventListener('change', (event) => {
    const stepper = event.target.closest?.('[data-quantity]');

    if (stepper && event.target.matches('[data-quantity-input]')) {
        event.target.value = clamp(event.target, Number(event.target.value));
        sync(stepper);
    }
});
