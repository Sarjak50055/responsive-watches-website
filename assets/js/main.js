function showContextMenu(e, id, type, name, isStarred = false, shareToken) {
    e.preventDefault();
    e.stopPropagation();

    const menu = document.getElementById('contextMenu');
    menu.style.display = 'block';

    // Position menu and handle screen edges
    let x = e.pageX;
    let y = e.pageY;

    const menuWidth = 200;
    const menuHeight = 240;

    if (x + menuWidth > window.innerWidth) x -= menuWidth;
    if (y + menuHeight > window.innerHeight) y -= menuHeight;

    menu.style.left = x + 'px';
    menu.style.top = y + 'px';

    // Set data for actions
    menu.dataset.id = id;
    menu.dataset.type = type;
    menu.dataset.name = name;
    menu.dataset.token = shareToken;

    // Toggle visibility based on type and view
    const urlParams = new URLSearchParams(window.location.search);
    const view = urlParams.get('view');

    const ctxDownload = document.getElementById('ctxDownload');
    const ctxShare = document.getElementById('ctxShare');
    const ctxShareEmail = document.getElementById('ctxShareEmail');
    const ctxRename = document.getElementById('ctxRename');
    const ctxStar = document.getElementById('ctxStar');
    const ctxRestore = document.getElementById('ctxRestore');
    const ctxDelete = document.getElementById('ctxDelete');

    if (ctxDownload) ctxDownload.style.display = type === 'file' ? 'flex' : 'none';
    if (ctxShare) ctxShare.style.display = type === 'file' ? 'flex' : 'none';
    if (ctxShareEmail) ctxShareEmail.style.display = 'flex';

    if (view === 'trash') {
        if (ctxDownload) ctxDownload.style.display = 'none';
        if (ctxShare) ctxShare.style.display = 'none';
        if (ctxShareEmail) ctxShareEmail.style.display = 'none';
        if (ctxRename) ctxRename.style.display = 'none';
        if (ctxStar) ctxStar.style.display = 'none';
        if (ctxRestore) ctxRestore.style.display = 'flex';
        if (ctxDelete) ctxDelete.innerHTML = '<span class="material-icons-outlined">delete_forever</span> Delete forever';
    } else {
        if (ctxRename) ctxRename.style.display = 'flex';
        if (ctxStar) {
            ctxStar.style.display = 'flex';
            if (isStarred) {
                ctxStar.innerHTML = '<span class="material-icons-outlined">star</span> Unstar';
            } else {
                ctxStar.innerHTML = '<span class="material-icons-outlined">star_outline</span> Star';
            }
        }
        if (ctxRestore) ctxRestore.style.display = 'none';
        if (ctxDelete) ctxDelete.innerHTML = '<span class="material-icons-outlined">delete</span> Delete';
    }
}

function handleRestore() {
    const menu = document.getElementById('contextMenu');
    const id = menu.dataset.id;
    const type = menu.dataset.type;
    const parentId = document.getElementById('parentId').value || '';

    if (confirm(`Restore this ${type}?`)) {
        const urlParams = new URLSearchParams(window.location.search);
        const view = urlParams.get('view') || 'my-drive';
        window.location.href = `actions/restore.php?id=${id}&type=${type}&parent_id=${parentId}&view=${view}`;
    }
}

function handleRename() {
    const menu = document.getElementById('contextMenu');
    const id = menu.dataset.id;
    const type = menu.dataset.type;
    const name = menu.dataset.name;

    document.getElementById('renameId').value = id;
    document.getElementById('renameType').value = type;
    document.getElementById('newNameInput').value = name;
    document.getElementById('renameModal').style.display = 'flex';
    menu.style.display = 'none';
}

function handleStar() {
    const menu = document.getElementById('contextMenu');
    const id = menu.dataset.id;
    const type = menu.dataset.type;
    const parentId = document.getElementById('parentId').value || '';

    const urlParams = new URLSearchParams(window.location.search);
    const view = urlParams.get('view') || 'my-drive';
    window.location.href = `actions/toggle_star.php?id=${id}&type=${type}&parent_id=${parentId}&view=${view}`;
}

function handleDelete() {
    const menu = document.getElementById('contextMenu');
    const id = menu.dataset.id;
    const type = menu.dataset.type;
    const parentId = document.getElementById('parentId').value || '';

    // Use the trash column instead of hard delete if it's not already in trash
    const urlParams = new URLSearchParams(window.location.search);
    const view = urlParams.get('view') || 'my-drive';

    let action = 'trash.php';
    let msg = `Move this ${type} to nebula trash?`;

    if (view === 'trash') {
        action = 'delete.php';
        msg = `Permanently incinerate this ${type}? This cannot be undone.`;
    }

    if (confirm(msg)) {
        window.location.href = `actions/${action}?id=${id}&type=${type}&parent_id=${parentId}&view=${view}`;
    }
}

function handleDownload() {
    const menu = document.getElementById('contextMenu');
    const id = menu.dataset.id;
    window.location.href = `actions/download.php?id=${id}`;
}

function handleShare() {
    const menu = document.getElementById('contextMenu');
    const token = menu.dataset.token;

    if (!token) {
        alert("This item cannot be shared.");
        return;
    }

    const baseUrl = window.SITE_URL || `${window.location.origin}${window.location.pathname.replace('index.php', '')}`;
    const shareUrl = `${baseUrl.endsWith('/') ? baseUrl : baseUrl + '/'}download_shared.php?token=${token}`;

    const message = encodeURIComponent(`\n${shareUrl}\n`);
    const whatsappUrl = `https://wa.me/?text=${message}`;
    window.open(whatsappUrl, '_blank');

    menu.style.display = 'none';
}

function handleShareWithEmail() {
    const menu = document.getElementById('contextMenu');
    const id = menu.dataset.id;
    const type = menu.dataset.type;
    const email = prompt("Enter the email of the person you want to share this with:");
    if (email) {
        window.location.href = `actions/share_with_user.php?id=${id}&type=${type}&email=${encodeURIComponent(email)}`;
    }
}

function handleCopyLink() {
    const menu = document.getElementById('contextMenu');
    const token = menu.dataset.token;

    if (!token) {
        alert("This item cannot be shared.");
        return;
    }

    const baseUrl = window.SITE_URL || `${window.location.origin}${window.location.pathname.replace('index.php', '')}`;
    const shareUrl = `${baseUrl.endsWith('/') ? baseUrl : baseUrl + '/'}download_shared.php?token=${token}`;

    navigator.clipboard.writeText(shareUrl).then(() => {
        alert("Link copied to clipboard!");
    }).catch(err => {
        console.error('Failed to copy: ', err);
    });

    menu.style.display = 'none';
}

// Keyboard Shortcuts
document.addEventListener('keydown', (e) => {
    const isTyping = e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable;
    if (isTyping) return;

    const selected = document.querySelectorAll('.file-card.selected');

    // Ctrl+A / Cmd+A - Select All
    if ((e.ctrlKey || e.metaKey) && e.key === 'a') {
        e.preventDefault();
        document.querySelectorAll('.file-card').forEach(c => c.classList.add('selected'));
        if (typeof window.updateSelectionToolbar === 'function') window.updateSelectionToolbar();
    }

    // Delete / Backspace - Delete selected items
    if (e.key === 'Delete' || (e.key === 'Backspace' && (e.metaKey || e.ctrlKey))) {
        if (selected.length > 0) {
            e.preventDefault();
            if (typeof window.bulkDelete === 'function') window.bulkDelete();
        }
    }

    // Escape - Clear selection
    if (e.key === 'Escape') {
        if (typeof window.clearSelection === 'function') window.clearSelection();
    }

    // Enter - Open or Preview
    if (e.key === 'Enter') {
        if (selected.length === 1) {
            e.preventDefault();
            const item = selected[0];
            const id = item.dataset.id;
            const type = item.dataset.type;
            const name = item.dataset.name;

            if (type === 'folder') {
                window.location.href = `index.php?folder=${id}`;
            } else {
                if (typeof window.openFilePreview === 'function') {
                    window.openFilePreview(id, name, item.dataset.filetype || '', item.dataset.size || '');
                }
            }
        }
    }
});

document.addEventListener('click', () => {
    const ctx = document.getElementById('contextMenu');
    if (ctx) ctx.style.display = 'none';
});

// Selection Logic
(function() {
    let lastSelectedIndex = -1;

    document.addEventListener('click', (e) => {
        const card = e.target.closest('.file-card');
        const cards = Array.from(document.querySelectorAll('.file-card'));
        
        if (card) {
            const index = cards.indexOf(card);
            
            if (e.ctrlKey || e.metaKey) {
                card.classList.toggle('selected');
                lastSelectedIndex = index;
            } else if (e.shiftKey && lastSelectedIndex !== -1) {
                const start = Math.min(lastSelectedIndex, index);
                const end = Math.max(lastSelectedIndex, index);
                cards.forEach((c, i) => {
                    if (i >= start && i <= end) c.classList.add('selected');
                });
            } else {
                cards.forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
                lastSelectedIndex = index;
            }
            updateSelectionToolbar();
        } else if (!e.target.closest('.google-menu') && !e.target.closest('#selectionToolbar')) {
            window.clearSelection();
        }
    });

    window.updateSelectionToolbar = function() {
        const selected = document.querySelectorAll('.file-card.selected');
        const toolbar = document.getElementById('selectionToolbar');
        const urlParams = new URLSearchParams(window.location.search);
        const view = urlParams.get('view');

        if (!toolbar) return;

        if (selected.length >= 1) {
            toolbar.style.display = 'flex';
            document.getElementById('selectionCount').textContent = `${selected.length} items selected`;
            
            const bulkRestoreBtn = document.getElementById('bulkRestoreBtn');
            const bulkShareBtn = document.querySelector('[onclick="bulkShare()"]');
            
            if (view === 'trash') {
                if (bulkRestoreBtn) bulkRestoreBtn.style.display = 'inline-flex';
                if (bulkShareBtn) bulkShareBtn.style.display = 'none';
            } else {
                if (bulkRestoreBtn) bulkRestoreBtn.style.display = 'none';
                if (bulkShareBtn) bulkShareBtn.style.display = 'inline-flex';
            }
        } else {
            toolbar.style.display = 'none';
        }
    };

    window.bulkRestore = function() {
        const selected = document.querySelectorAll('.file-card.selected');
        const items = Array.from(selected).map(c => ({
            id: c.dataset.id,
            type: c.dataset.type
        }));

        if (items.length === 0) return;

        if (confirm(`Restore ${items.length} items?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'actions/bulk_restore.php';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'items';
            input.value = JSON.stringify(items);
            form.appendChild(input);

            const urlParams = new URLSearchParams(window.location.search);
            const parentInput = document.createElement('input');
            parentInput.type = 'hidden';
            parentInput.name = 'parent_id';
            parentInput.value = urlParams.get('folder') || '';
            form.appendChild(parentInput);

            document.body.appendChild(form);
            form.submit();
        }
    };

    window.bulkShare = function() {
        const selected = document.querySelectorAll('.file-card.selected');
        if (selected.length === 1) {
            const id = selected[0].dataset.id;
            const type = selected[0].dataset.type;
            const email = prompt("Enter the email of the person you want to share this with:");
            if (email) {
                window.location.href = `actions/share_with_user.php?id=${id}&type=${type}&email=${encodeURIComponent(email)}`;
            }
        } else if (selected.length > 1) {
            alert("Currently, items can only be shared one at a time.");
        }
    };

    window.clearSelection = function() {
        document.querySelectorAll('.file-card.selected').forEach(c => c.classList.remove('selected'));
        lastSelectedIndex = -1;
        updateSelectionToolbar();
    };

    window.bulkDelete = function() {
        const selected = document.querySelectorAll('.file-card.selected');
        const items = Array.from(selected).map(c => ({
            id: c.dataset.id,
            type: c.dataset.type
        }));

        if (items.length === 0) return;

        const urlParams = new URLSearchParams(window.location.search);
        const view = urlParams.get('view');
        const msg = view === 'trash' 
            ? `Permanently incinerate ${items.length} items? This cannot be undone.`
            : `Move ${items.length} items to nebula trash?`;

        if (confirm(msg)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = view === 'trash' ? 'actions/bulk_delete.php' : 'actions/bulk_trash.php';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'items';
            input.value = JSON.stringify(items);
            form.appendChild(input);

            const parentInput = document.createElement('input');
            parentInput.type = 'hidden';
            parentInput.name = 'parent_id';
            parentInput.value = urlParams.get('folder') || '';
            form.appendChild(parentInput);

            const viewInput = document.createElement('input');
            viewInput.type = 'hidden';
            viewInput.name = 'view';
            viewInput.value = view || 'my-drive';
            form.appendChild(viewInput);

            document.body.appendChild(form);
            form.submit();
        }
    };

    window.bulkDownload = function() {
        const selected = document.querySelectorAll('.file-card.selected');
        const fileIds = Array.from(selected)
            .filter(c => c.dataset.type === 'file')
            .map(c => c.dataset.id);

        if (fileIds.length === 0) {
            alert("No files selected to download (only files can be bulk-downloaded currently).");
            return;
        }

        if (confirm(`Download ${fileIds.length} files?`)) {
            fileIds.forEach((id, index) => {
                setTimeout(() => {
                    const link = document.createElement('a');
                    link.href = `actions/download.php?id=${id}`;
                    link.style.display = 'none';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }, index * 1000);
            });
        }
    };
})();

// Drag and drop UI
const itemsArea = document.querySelector('.items-area');
if (itemsArea) {
    itemsArea.addEventListener('dragenter', preventDefaults, false);
    itemsArea.addEventListener('dragover', preventDefaults, false);
    itemsArea.addEventListener('dragleave', preventDefaults, false);
    itemsArea.addEventListener('drop', preventDefaults, false);

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        itemsArea.addEventListener(eventName, () => itemsArea.classList.add('drag-over'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        itemsArea.addEventListener(eventName, () => itemsArea.classList.remove('drag-over'), false);
    });

    itemsArea.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length > 0) {
            handleFileUpload(files);
        }
    });
}

/* ── Grid ↔ Detail view toggle ────────────────────────────────────────────── */
(function () {
    const listBtn = document.getElementById('viewListBtn');
    const gridBtn = document.getElementById('viewGridBtn');
    const grid = document.getElementById('fileGrid');

    if (!listBtn || !gridBtn || !grid) return;

    const STORAGE_KEY = 'driveViewMode';

    function applyMode(mode, animate) {
        if (mode === 'detail') {
            grid.classList.add('view-detail');
            listBtn.classList.add('active');
            gridBtn.classList.remove('active');

            if (!grid.querySelector('.list-header')) {
                const header = document.createElement('div');
                header.className = 'list-header';
                header.innerHTML = '<span></span><span>Name</span><span>Size</span><span>Last modified</span>';
                grid.insertBefore(header, grid.firstChild);
            }
        } else {
            grid.classList.remove('view-detail');
            gridBtn.classList.add('active');
            listBtn.classList.remove('active');

            const header = grid.querySelector('.list-header');
            if (header) header.remove();
        }

        if (animate) {
            grid.style.opacity = '0';
            grid.style.transform = 'translateY(4px)';
            requestAnimationFrame(() => {
                grid.style.transition = 'opacity 0.18s ease, transform 0.18s ease';
                grid.style.opacity = '1';
                grid.style.transform = 'translateY(0)';
                setTimeout(() => { grid.style.transition = ''; }, 200);
            });
        }
    }

    const saved = localStorage.getItem(STORAGE_KEY) || 'grid';
    applyMode(saved, false);

    listBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        if (localStorage.getItem(STORAGE_KEY) === 'detail') return;
        localStorage.setItem(STORAGE_KEY, 'detail');
        applyMode('detail', true);
    });

    gridBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        if (localStorage.getItem(STORAGE_KEY) === 'grid') return;
        localStorage.setItem(STORAGE_KEY, 'grid');
        applyMode('grid', true);
    });
})();

/* ── File Upload Logic ─────────────────────────────────────────────────────── */
window.handleFileUpload = function(files, isFolder = false) {
    if (!files || files.length === 0) return;

    const popup = document.getElementById('uploadPopup');
    const upStatus = document.getElementById('upStatus');
    const upFilename = document.getElementById('upFilename');
    const upProgressBar = document.getElementById('upProgressBar');
    const upPercentage = document.getElementById('upPercentage');
    const upClose = document.getElementById('upClose');
    const upMinimize = document.getElementById('upMinimize');
    const upItemIcon = document.getElementById('upItemIcon');

    if (!popup) return;

    popup.style.display = 'flex';
    popup.classList.remove('minimized');
    upClose.style.display = 'none';
    upMinimize.style.display = 'block';
    upPercentage.classList.remove('up-success');
    
    const count = files.length;
    upStatus.textContent = count > 1 ? `Uploading ${count} items` : `Uploading 1 item`;
    upFilename.textContent = files[0].name + (count > 1 ? ` and ${count-1} others` : '');
    upItemIcon.textContent = isFolder ? 'folder' : 'insert_drive_file';

    const formData = new FormData();
    const urlParams = new URLSearchParams(window.location.search);
    formData.append('folder_id', urlParams.get('folder') || '');

    if (isFolder) {
        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
            formData.append('paths[]', files[i].webkitRelativePath);
        }
    } else {
        if (files.length > 1) {
            for (let i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
            }
        } else {
            formData.append('file', files[0]);
        }
    }

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'upload.php', true);

    xhr.upload.onprogress = function(e) {
        if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 100);
            upProgressBar.style.width = percent + '%';
            upPercentage.textContent = percent + '%';
        }
    };

    xhr.onload = function() {
        if (xhr.status === 200) {
            upProgressBar.style.width = '100%';
            upPercentage.textContent = '100%';
            upPercentage.classList.add('up-success');
            upStatus.textContent = count > 1 ? `${count} uploads complete` : `Upload complete`;
            upMinimize.style.display = 'none';
            upClose.style.display = 'block';
            
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            console.error('Upload failed:', xhr.responseText);
            alert('Upload failed. Check console for details.');
            popup.style.display = 'none';
        }
    };

    xhr.onerror = function() {
        alert('Upload failed. Please check your connection.');
        popup.style.display = 'none';
    };

    xhr.send(formData);
};

// Popup controls
if (document.getElementById('upMinimize')) {
    document.getElementById('upMinimize').addEventListener('click', () => {
        document.getElementById('uploadPopup').classList.toggle('minimized');
    });
}
if (document.getElementById('upClose')) {
    document.getElementById('upClose').addEventListener('click', () => {
        document.getElementById('uploadPopup').style.display = 'none';
    });
}

// Empty Trash action
(function() {
    const emptyBtn = document.getElementById('emptyTrashBtn');
    if (emptyBtn) {
        emptyBtn.addEventListener('click', function() {
            if (confirm("Permanently incinerate EVERYTHING in your trash? This cannot be undone.")) {
                document.getElementById('emptyTrashForm').submit();
            }
        });
    }
})();
