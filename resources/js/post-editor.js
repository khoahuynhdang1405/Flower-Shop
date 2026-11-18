/**
 * Khung soạn thảo bài Góc nhỏ (Admin › Chuyện Hoa), trong [data-post-editor]:
 * - [data-editor-tool] chèn cú pháp Markdown quanh đoạn đang chọn trong textarea [data-editor-input]
 *   (công cụ "note" chèn mẫu khối ghi nhớ :::ghi-nho từ data-note-template);
 * - [data-editor-stats] đếm chữ + phút đọc (data-stats-template, :words / :minutes);
 * - tab [data-editor-tab] "Viết" / "Xem trước": nội dung gửi lên data-preview-url, server trả HTML đã lọc an toàn
 *   (cùng cách hiển thị với trang khách);
 * - công cụ ảnh: upload lên data-upload-url rồi chèn ![](url) vào vị trí con trỏ.
 */
const csrfToken = (editor) => editor.closest('form')?.querySelector('input[name="_token"]')?.value ?? '';

const wrapSelection = (textarea, before, after) => {
    const { selectionStart: start, selectionEnd: end, value } = textarea;
    const selected = value.slice(start, end);

    textarea.setRangeText(before + selected + after, start, end, 'end');
    textarea.setSelectionRange(start + before.length, start + before.length + selected.length);
};

const prefixLines = (textarea, prefix) => {
    const { selectionStart, selectionEnd, value } = textarea;
    const lineStart = value.lastIndexOf('\n', selectionStart - 1) + 1;
    const lines = value.slice(lineStart, selectionEnd).split('\n').map((line) => prefix + line);

    textarea.setRangeText(lines.join('\n'), lineStart, selectionEnd, 'end');
};

const insertAtCursor = (textarea, text) => {
    textarea.setRangeText(text, textarea.selectionStart, textarea.selectionEnd, 'end');
};

const tools = {
    heading: (textarea) => prefixLines(textarea, '## '),
    bold: (textarea) => wrapSelection(textarea, '**', '**'),
    italic: (textarea) => wrapSelection(textarea, '*', '*'),
    quote: (textarea) => prefixLines(textarea, '> '),
    list: (textarea) => prefixLines(textarea, '- '),
    link: (textarea) => wrapSelection(textarea, '[', '](https://)'),
    note: (textarea, editor) => insertAtCursor(textarea, `\n\n${editor.dataset.noteTemplate}\n\n`),
};

const updateStats = (editor) => {
    const textarea = editor.querySelector('[data-editor-input]');
    const words = textarea.value.trim().split(/\s+/).filter(Boolean).length;
    const minutes = Math.max(1, Math.ceil(words / Number(editor.dataset.wordsPerMinute)));

    editor.querySelector('[data-editor-stats]').textContent = editor.dataset.statsTemplate
        .replace(':words', words)
        .replace(':minutes', minutes);
};

const changed = (textarea) => {
    textarea.focus();
    textarea.dispatchEvent(new Event('input', { bubbles: true }));
};

const showMessage = (editor, text) => {
    editor.querySelector('[data-editor-message]').textContent = text;
};

const renderPreview = async (editor) => {
    const preview = editor.querySelector('[data-editor-preview]');
    const content = editor.querySelector('[data-editor-input]').value;

    if (content.trim() === '') {
        preview.textContent = editor.dataset.labelEmpty;

        return;
    }

    preview.textContent = editor.dataset.labelLoading;

    try {
        const response = await fetch(editor.dataset.previewUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken(editor) },
            body: JSON.stringify({ content }),
        });

        if (!response.ok) {
            throw new Error(response.statusText);
        }

        // HTML do RenderPostContentAction sinh ra: đã bỏ HTML viết tay và link không an toàn.
        preview.innerHTML = (await response.json()).html;
    } catch {
        preview.textContent = editor.dataset.labelError;
    }
};

const selectTab = (editor, name) => {
    editor.querySelectorAll('[data-editor-tab]').forEach((tab) => {
        const selected = tab.dataset.editorTab === name;

        tab.setAttribute('aria-selected', String(selected));
        tab.tabIndex = selected ? 0 : -1;
    });
    editor.querySelectorAll('[data-editor-panel]').forEach((panel) => {
        panel.hidden = panel.dataset.editorPanel !== name;
    });

    if (name === 'preview') {
        renderPreview(editor);
    }
};

const uploadImage = async (editor, file) => {
    const textarea = editor.querySelector('[data-editor-input]');
    const body = new FormData();

    body.append('image', file);
    showMessage(editor, editor.dataset.labelUploading);

    try {
        const response = await fetch(editor.dataset.uploadUrl, {
            method: 'POST',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken(editor) },
            body,
        });
        const data = await response.json();

        if (!response.ok) {
            showMessage(editor, data.errors?.image?.[0] ?? editor.dataset.labelUploadFailed);

            return;
        }

        insertAtCursor(textarea, `\n\n![](${data.url})\n\n`);
        showMessage(editor, '');
        changed(textarea);
    } catch {
        showMessage(editor, editor.dataset.labelUploadFailed);
    }
};

document.addEventListener('click', (event) => {
    const editor = event.target.closest('[data-post-editor]');

    if (!editor) {
        return;
    }

    const tool = event.target.closest('[data-editor-tool]');
    const tab = event.target.closest('[data-editor-tab]');

    if (tool) {
        const textarea = editor.querySelector('[data-editor-input]');

        if (tool.dataset.editorTool === 'image') {
            editor.querySelector('[data-editor-image-input]').click();

            return;
        }

        tools[tool.dataset.editorTool](textarea, editor);
        changed(textarea);
    }

    if (tab) {
        selectTab(editor, tab.dataset.editorTab);
    }
});

document.addEventListener('keydown', (event) => {
    const tab = event.target.closest('[data-editor-tab]');

    if (!tab || !['ArrowLeft', 'ArrowRight'].includes(event.key)) {
        return;
    }

    const editor = tab.closest('[data-post-editor]');
    const tabs = [...editor.querySelectorAll('[data-editor-tab]')];
    const next = tabs[(tabs.indexOf(tab) + (event.key === 'ArrowRight' ? 1 : tabs.length - 1)) % tabs.length];

    selectTab(editor, next.dataset.editorTab);
    next.focus();
});

document.addEventListener('input', (event) => {
    const editor = event.target.closest('[data-post-editor]');

    if (editor && event.target.matches('[data-editor-input]')) {
        updateStats(editor);
    }
});

document.addEventListener('change', (event) => {
    const editor = event.target.closest('[data-post-editor]');

    if (editor && event.target.matches('[data-editor-image-input]') && event.target.files.length > 0) {
        uploadImage(editor, event.target.files[0]);
        event.target.value = '';
    }
});

document.querySelectorAll('[data-post-editor]').forEach(updateStats);
