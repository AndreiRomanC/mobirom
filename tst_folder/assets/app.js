const form = document.getElementById('uploadForm');
const input = document.getElementById('fileInput');
const dropzone = document.getElementById('dropzone');
const queue = document.getElementById('queue');
const statusEl = document.getElementById('uploadStatus');
const uploadButton = document.getElementById('uploadButton');

document.querySelectorAll('.confirm-delete-file').forEach(deleteForm => {
    deleteForm.addEventListener('submit', event => {
        if (!window.confirm('Ștergi acest fișier?')) event.preventDefault();
    });
});

document.querySelectorAll('.confirm-delete-all').forEach(deleteForm => {
    deleteForm.addEventListener('submit', event => {
        const count = deleteForm.dataset.count || 'toate';
        if (!window.confirm(`Ștergi toate cele ${count} fișiere?`)) event.preventDefault();
    });
});

const bulkDeleteForm = document.getElementById('bulkDeleteForm');
const fileSelections = Array.from(document.querySelectorAll('.file-select'));
if (bulkDeleteForm) {
    const bulkDeleteButton = bulkDeleteForm.querySelector('button[type="submit"]');
    const updateBulkDeleteState = () => {
        bulkDeleteButton.disabled = !fileSelections.some(selection => selection.checked);
    };

    fileSelections.forEach(selection => selection.addEventListener('change', updateBulkDeleteState));
    bulkDeleteForm.addEventListener('submit', event => {
        const selectedCount = fileSelections.filter(selection => selection.checked).length;
        if (!selectedCount || !window.confirm(`Ștergi cele ${selectedCount} fișiere selectate?`)) {
            event.preventDefault();
        }
    });
}

function formatSize(bytes) {
    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    let value = bytes;
    let i = 0;
    while (value >= 1024 && i < units.length - 1) {
        value = value / 1024;
        i++;
    }
    return i === 0 ? `${bytes} ${units[i]}` : `${value.toFixed(2)} ${units[i]}`;
}

function renderQueue() {
    queue.innerHTML = '';

    const files = Array.from(input.files || []);
    if (!files.length) {
        statusEl.textContent = statusEl.textContent.startsWith('Upload') ? statusEl.textContent : statusEl.textContent;
        return;
    }

    files.forEach(file => {
        const item = document.createElement('div');
        item.className = 'queue-item';

        const name = document.createElement('span');
        name.textContent = file.name;

        const size = document.createElement('span');
        size.textContent = formatSize(file.size);

        item.appendChild(name);
        item.appendChild(size);
        queue.appendChild(item);
    });

    statusEl.textContent = `${files.length} fișier(e) selectate`;
}

if (input) {
    input.addEventListener('change', renderQueue);
}

if (dropzone) {
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, event => {
            event.preventDefault();
            dropzone.classList.add('is-over');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, event => {
            event.preventDefault();
            dropzone.classList.remove('is-over');
        });
    });

    dropzone.addEventListener('drop', event => {
        const files = event.dataTransfer.files;
        if (!files || !files.length) return;
        input.files = files;
        renderQueue();
    });
}

if (form) {
    form.addEventListener('submit', event => {
        event.preventDefault();

        if (!input.files || !input.files.length) {
            statusEl.textContent = 'Alege cel puțin un fișier.';
            return;
        }

        const formData = new FormData(form);
        const xhr = new XMLHttpRequest();

        uploadButton.disabled = true;
        uploadButton.textContent = 'Se încarcă...';

        xhr.upload.addEventListener('progress', event => {
            if (!event.lengthComputable) {
                statusEl.textContent = 'Upload în curs...';
                return;
            }

            const percent = Math.round((event.loaded / event.total) * 100);
            statusEl.textContent = `Upload ${percent}%`;
        });

        xhr.addEventListener('load', () => {
            uploadButton.disabled = false;
            uploadButton.textContent = 'Încarcă';

            try {
                const response = JSON.parse(xhr.responseText);
                if (xhr.status >= 200 && xhr.status < 300 && response.ok) {
                    statusEl.textContent = 'Upload finalizat.';
                    window.location.href = 'index.php';
                } else {
                    statusEl.textContent = response.errors && response.errors.length
                        ? response.errors[0]
                        : `Upload eșuat (HTTP ${xhr.status}).`;
                }
            } catch (error) {
                const detail = xhr.responseText.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
                statusEl.textContent = detail
                    ? `Serverul a răspuns cu HTTP ${xhr.status}: ${detail.slice(0, 180)}`
                    : `Răspuns invalid de la server (HTTP ${xhr.status}).`;
            }
        });

        xhr.addEventListener('error', () => {
            uploadButton.disabled = false;
            uploadButton.textContent = 'Încarcă';
            statusEl.textContent = 'Upload eșuat.';
        });

        xhr.open('POST', 'index.php');
        xhr.setRequestHeader('X-CSRF-Token', formData.get('csrf'));
        xhr.send(formData);
    });
}
