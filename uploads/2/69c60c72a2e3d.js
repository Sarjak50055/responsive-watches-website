function showContextMenu(e, id, type, name, isStarred = false) {
    e.preventDefault();
    e.stopPropagation();

    const menu = document.getElementById('contextMenu');
    menu.style.display = 'block';

    // Position menu and handle screen edges
    let x = e.pageX;
    let y = e.pageY;

    const menuWidth = 200;
    const menuHeight = 220;

    if (x + menuWidth > window.innerWidth) x -= menuWidth;
    if (y + menuHeight > window.innerHeight) y -= menuHeight;

    menu.style.left = x + 'px';
    menu.style.top = y + 'px';

    // Set data for actions
    menu.dataset.id = id;
    menu.dataset.type = type;
    menu.dataset.name = name;

    // Toggle visibility based on type and view
    const urlParams = new URLSearchParams(window.location.search);
    const view = urlParams.get('view');

    const ctxDownload = document.getElementById('ctxDownload');
    const ctxShare = document.getElementById('ctxShare');
    const ctxRename = document.getElementById('ctxRename');
    const ctxStar = document.getElementById('ctxStar');
    const ctxRestore = document.getElementById('ctxRestore');
    const ctxDelete = document.getElementById('ctxDelete');

    if (ctxDownload) ctxDownload.style.display = type === 'file' ? 'flex' : 'none';
    if (ctxShare) ctxShare.style.display = type === 'file' ? 'flex' : 'none';

    if (view === 'trash') {
        if (ctxDownload) ctxDownload.style.display = 'none';
        if (ctxShare) ctxShare.style.display = 'none';
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

    if (confirm(`Restore this ${type}?`)) {
        window.location.href = `actions/restore.php?id=${id}&type=${type}`;
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

    window.location.href = `actions/toggle_star.php?id=${id}&type=${type}&parent_id=${parentId}`;
}

function handleDelete() {
    const menu = document.getElementById('contextMenu');
    const id = menu.dataset.id;
    const type = menu.dataset.type;
    const parentId = document.getElementById('parentId').value || '';

    // Use the trash column instead of hard delete if it's not already in trash
    const urlParams = new URLSearchParams(window.location.search);
    const view = urlParams.get('view');

    let action = 'trash.php';
    let msg = `Move this ${type} to nebula trash?`;

    if (view === 'trash') {
        action = 'delete.php';
        msg = `Permanently incinerate this ${type}? This cannot be undone.`;
    }

    if (confirm(msg)) {
        window.location.href = `actions/${action}?id=${id}&type=${type}&parent_id=${parentId}`;
    }
}

function handleDownload() {
    const menu = document.getElementById('contextMenu');
    const id = menu.dataset.id;
    window.location.href = `actions/download.php?id=${id}`;
}

function handleShare() {
    const menu = document.getElementById('contextMenu');
    const id = menu.dataset.id;
    const name = menu.dataset.name;

    // Construct a sharing link (using the new automatic download landing page)
    const shareUrl = `${window.location.origin}${window.location.pathname.replace('index.php', '')}download_shared.php?id=${id}`;

    // Share via WhatsApp
    const message = encodeURIComponent(`Check out this file on Drive: ${name}\n\nLink: ${shareUrl}`);
    const whatsappUrl = `https://wa.me/?text=${message}`;
    window.open(whatsappUrl, '_blank');

    menu.style.display = 'none';
}

document.addEventListener('click', () => {
    const ctx = document.getElementById('contextMenu');
    if (ctx) ctx.style.display = 'none';
});

/* ── Grid ↔ Detail view toggle ────────────────────────────────────────────── */
(function () {
    const listBtn = document.getElementById('viewListBtn');
    const gridBtn = document.getElementById('viewGridBtn');
    const grid = document.getElementById('fileGrid');

    if (!listBtn || !gridBtn || !grid) return;

    const STORAGE_KEY = 'driveViewMode';   // 'grid' | 'detail'

    function applyMode(mode, animate) {
        if (mode === 'detail') {
            grid.classList.add('view-detail');
            listBtn.classList.add('active');
            gridBtn.classList.remove('active');

            // Inject header row only once
            if (!grid.querySelector('.list-header')) {
                const header = document.createElement('div');
                header.className = 'list-header';
                header.innerHTML = '<span></span><span>Name</span><span>Size</span><span>Modified</span>';
                grid.insertBefore(header, grid.firstChild);
            }
        } else {
            grid.classList.remove('view-detail');
            gridBtn.classList.add('active');
            listBtn.classList.remove('active');

            // Remove header row
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

    // Restore saved preference on load
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

