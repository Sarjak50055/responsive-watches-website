/**
 * Live Search for Google Drive Clone
 * Queries actions/search.php and renders results in a dropdown panel.
 */
(function () {
    const input = document.getElementById('searchInput');
    const resultsBox = document.getElementById('searchResults');
    const clearBtn = document.getElementById('searchClearBtn');
    const wrapper = document.getElementById('searchWrapper');
    
    // Filters
    const filterBtn = document.getElementById('filterBtn');
    const searchOptions = document.getElementById('searchOptions');
    const applyFilters = document.getElementById('applyFilters');
    const resetFilters = document.getElementById('resetFilters');

    const filterType = document.getElementById('filterType');
    const filterOwner = document.getElementById('filterOwner');
    const filterDate = document.getElementById('filterDate');

    if (!input || !resultsBox) return;

    let debounceTimer = null;

    // Toggle options
    filterBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        const isHidden = searchOptions.style.display === 'none';
        searchOptions.style.display = isHidden ? 'flex' : 'none';
        if (isHidden) hideDropdown(); 
    });

    document.addEventListener('click', (e) => {
        if (!searchOptions.contains(e.target) && e.target !== filterBtn) {
            searchOptions.style.display = 'none';
        }
    });

    applyFilters.addEventListener('click', () => {
        searchOptions.style.display = 'none';
        doSearch(input.value.trim());
    });

    resetFilters.addEventListener('click', () => {
        filterType.value = 'any';
        filterOwner.value = 'any';
        filterDate.value = 'any';
    });

    // ── Material icon mapping (mirrors PHP getFileIcon) ──────────────────────
    function getIcon(item) {
        if (item.type === 'folder') return 'folder';
        const ft = (item.file_type || '').toLowerCase();
        if (ft.includes('image/')) return 'image';
        if (ft.includes('video/')) return 'videocam';
        if (ft.includes('pdf')) return 'picture_as_pdf';
        if (ft.includes('zip') || ft.includes('rar')) return 'archive';
        if (ft.includes('word') || ft.includes('text/')) return 'description';
        return 'insert_drive_file';
    }

    // ── Highlight query text inside a string ─────────────────────────────────
    function highlight(text, query) {
        if (!query) return escHtml(text);
        const re = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        return escHtml(text).replace(re, '<mark>$1</mark>');
    }

    function escHtml(str) {
        return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    // ── Render results into the dropdown ─────────────────────────────────────
    function renderResults(items, query) {
        if (!items.length) {
            resultsBox.innerHTML = `
                <div class="search-empty">
                    <span class="material-icons-outlined">search_off</span>
                    <span>No results found</span>
                </div>`;
            showDropdown();
            return;
        }

        const html = items.map(item => {
            const icon = getIcon(item);
            const iconColor = item.type === 'folder' ? '#c4c7c5' : '#a8c7fa';
            const meta = item.type === 'file'
                ? `<span class="sr-meta">${escHtml(item.size)}</span>`
                : `<span class="sr-meta">Folder</span>`;

            if (item.type === 'folder') {
                const link = `index.php?folder=${item.id}`;
                return `
                    <a href="${link}" class="search-result-item" data-type="folder">
                        <span class="material-icons sr-icon" style="color:${iconColor}">${icon}</span>
                        <div class="sr-info">
                            <div class="sr-name">${highlight(item.name, query)}</div>
                            ${meta}
                        </div>
                    </a>`;
            } else {
                const safeType = escHtml(item.file_type || '');
                const safeSize = escHtml(item.size || '');
                const safeName = escHtml(item.name);
                return `
                    <div class="search-result-item" data-type="file"
                         data-id="${item.id}"
                         data-name="${safeName}"
                         data-filetype="${safeType}"
                         data-size="${safeSize}"
                         style="cursor:pointer;">
                        <span class="material-icons sr-icon" style="color:${iconColor}">${icon}</span>
                        <div class="sr-info">
                            <div class="sr-name">${highlight(item.name, query)}</div>
                            ${meta}
                        </div>
                    </div>`;
            }
        }).join('');

        resultsBox.innerHTML = html;

        resultsBox.querySelectorAll('.search-result-item[data-type="file"]').forEach(el => {
            el.addEventListener('click', function (e) {
                e.stopPropagation();
                hideDropdown();
                if (typeof window.openFilePreview === 'function') {
                    window.openFilePreview(
                        this.dataset.id,
                        this.dataset.name,
                        this.dataset.filetype,
                        this.dataset.size
                    );
                }
            });
        });

        showDropdown();
    }

    function showDropdown() {
        resultsBox.style.display = 'block';
        wrapper.classList.add('search-active');
    }

    function hideDropdown() {
        resultsBox.style.display = 'none';
        wrapper.classList.remove('search-active');
    }

    function doSearch(query) {
        const type = filterType.value;
        const owner = filterOwner.value;
        const date = filterDate.value;

        fetch(`actions/search.php?q=${encodeURIComponent(query)}&type=${type}&owner=${owner}&date=${date}`)
            .then(r => r.json())
            .then(data => renderResults(data.results || [], query))
            .catch(() => hideDropdown());
    }

    input.addEventListener('input', function () {
        const q = this.value.trim();
        clearBtn.style.display = q.length ? 'flex' : 'none';
        clearTimeout(debounceTimer);
        
        // Show empty dropdown to show current filters state? Optional.
        if (!q && filterType.value === 'any' && filterOwner.value === 'any' && filterDate.value === 'any') {
            hideDropdown();
            return;
        }

        debounceTimer = setTimeout(() => doSearch(q), 250);
    });

    clearBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        input.value = '';
        clearBtn.style.display = 'none';
        hideDropdown();
        input.focus();
    });

    document.addEventListener('click', function (e) {
        if (!wrapper.contains(e.target)) {
            hideDropdown();
        }
    });

    resultsBox.addEventListener('click', (e) => e.stopPropagation());

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            hideDropdown();
            searchOptions.style.display = 'none';
            input.blur();
        }
    });
})();
