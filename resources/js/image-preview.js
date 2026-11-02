/**
 * Xem trước ảnh trước khi tải lên (Admin › Sản phẩm › Ảnh):
 * input[data-image-preview] (multiple) → hiện ảnh thu nhỏ trong [data-image-preview-list]
 * và số ảnh đã chọn trong [data-image-preview-count] (chữ lấy từ data-selected-template, :count) cùng form.
 */
const previewUrls = new WeakMap();

document.addEventListener('change', (event) => {
    const input = event.target.closest('[data-image-preview]');

    if (!input) {
        return;
    }

    const form = input.closest('form');
    const list = form.querySelector('[data-image-preview-list]');
    const count = form.querySelector('[data-image-preview-count]');
    const files = [...input.files];

    (previewUrls.get(input) ?? []).forEach((url) => URL.revokeObjectURL(url));

    const urls = files.map((file) => URL.createObjectURL(file));
    previewUrls.set(input, urls);

    list.replaceChildren(
        ...urls.map((url) => {
            const item = document.createElement('li');
            const image = document.createElement('img');

            image.src = url;
            image.alt = '';
            image.className = 'aspect-square w-full rounded object-cover';
            item.append(image);

            return item;
        }),
    );

    count.textContent = files.length > 0 ? input.dataset.selectedTemplate.replace(':count', files.length) : '';
});
