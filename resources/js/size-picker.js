/**
 * Chọn size ở chi tiết sản phẩm: radio [data-size-option] mang sẵn data-price / data-dimension đã định dạng;
 * khi đổi size, cập nhật [data-size-price] (aria-live) và [data-size-dimension] trong cùng [data-product].
 */
document.addEventListener('change', (event) => {
    const option = event.target.closest?.('[data-size-option]');
    const product = option?.closest('[data-product]');

    if (!product) {
        return;
    }

    const price = product.querySelector('[data-size-price]');
    const dimension = product.querySelector('[data-size-dimension]');

    if (price) {
        price.textContent = option.dataset.price;
    }

    if (dimension) {
        dimension.textContent = option.dataset.dimension;
    }
});
