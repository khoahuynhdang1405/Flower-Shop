/**
 * Nút sao chép đường dẫn [data-copy-link="<url>"] (trang bài Góc nhỏ): chép vào clipboard rồi báo
 * data-copied-label qua [data-copy-link-status] (aria-live) cạnh nút.
 */
document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-copy-link]');

    if (!button) {
        return;
    }

    try {
        await navigator.clipboard.writeText(button.dataset.copyLink);
    } catch {
        return;
    }

    const status = button.parentElement.querySelector('[data-copy-link-status]');

    if (status) {
        status.textContent = button.dataset.copiedLabel;
    }

    button.title = button.dataset.copiedLabel;
});
