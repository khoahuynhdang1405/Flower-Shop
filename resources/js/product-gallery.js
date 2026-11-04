/**
 * Gallery chi tiết sản phẩm: [data-gallery] chứa các ảnh lớn [data-gallery-slide] và ảnh nhỏ [data-gallery-thumb] cùng thứ tự.
 * Bấm ảnh nhỏ → hiện ảnh lớn tương ứng (mờ chéo bằng CSS data-active). Phím ← / → chuyển giữa các ảnh nhỏ.
 */
const show = (gallery, index) => {
    const thumbs = [...gallery.querySelectorAll('[data-gallery-thumb]')];

    gallery.querySelectorAll('[data-gallery-slide]').forEach((slide, position) => {
        slide.toggleAttribute('data-active', position === index);
        slide.toggleAttribute('aria-hidden', position !== index);
    });

    thumbs.forEach((thumb, position) => thumb.setAttribute('aria-pressed', String(position === index)));

    return thumbs[index];
};

document.addEventListener('click', (event) => {
    const thumb = event.target.closest('[data-gallery-thumb]');

    if (!thumb) {
        return;
    }

    const gallery = thumb.closest('[data-gallery]');

    show(gallery, [...gallery.querySelectorAll('[data-gallery-thumb]')].indexOf(thumb));
});

document.addEventListener('keydown', (event) => {
    const thumb = event.target.closest?.('[data-gallery-thumb]');

    if (!thumb || !['ArrowLeft', 'ArrowRight'].includes(event.key)) {
        return;
    }

    event.preventDefault();

    const gallery = thumb.closest('[data-gallery]');
    const thumbs = [...gallery.querySelectorAll('[data-gallery-thumb]')];
    const step = event.key === 'ArrowRight' ? 1 : -1;
    const next = (thumbs.indexOf(thumb) + step + thumbs.length) % thumbs.length;

    show(gallery, next).focus();
});
