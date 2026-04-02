/**
 * File Preview Modal — Google Drive Clone
 * Opens an in-page viewer when a file search result is clicked.
 * Supports: image, video, audio, PDF, plain-text, and generic download.
 */
(function () {
    'use strict';

    const modal = document.getElementById('filePreviewModal');
    const fpBody = document.getElementById('fpBody');
    const fpFilename = document.getElementById('fpFilename');
    const fpIcon = document.getElementById('fpIcon');
    const fpDl = document.getElementById('fpDownloadBtn');
    const fpClose = document.getElementById('fpCloseBtn');

    if (!modal) return;

    // ── Icon helper (mirrors search.js) ──────────────────────────────────────
    function iconFor(fileType) {
        const ft = (fileType || '').toLowerCase();
        if (ft.includes('image/')) return 'image';
        if (ft.includes('video/')) return 'videocam';
        if (ft.includes('audio/')) return 'music_note';
        if (ft.includes('pdf')) return 'picture_as_pdf';
        if (ft.includes('zip') || ft.includes('rar')) return 'archive';
        if (ft.includes('word') || ft.includes('text/')) return 'description';
        return 'insert_drive_file';
    }

    // ── Build the preview content based on MIME type ──────────────────────────
    function buildPreview(fileId, fileName, fileType, fileSize) {
        const ft = (fileType || '').toLowerCase();
        const src = `actions/preview.php?id=${fileId}`;
        const dlHref = `actions/download.php?id=${fileId}`;

        fpFilename.textContent = fileName;
        fpIcon.textContent = iconFor(fileType);
        fpDl.href = dlHref;

        let html = '';

        if (ft.includes('image/')) {
            html = `<img src="${src}" alt="${escHtml(fileName)}" class="fp-media-img">`;

        } else if (ft.includes('video/')) {
            html = `
                <video controls class="fp-media-video">
                    <source src="${src}" type="${escHtml(fileType)}">
                    Your browser does not support this video format.
                </video>`;

        } else if (ft.includes('audio/')) {
            html = `
                <div class="fp-audio-wrap">
                    <span class="material-icons fp-audio-icon">music_note</span>
                    <p class="fp-audio-name">${escHtml(fileName)}</p>
                    <audio controls class="fp-audio-player">
                        <source src="${src}" type="${escHtml(fileType)}">
                        Your browser does not support audio playback.
                    </audio>
                </div>`;

        } else if (ft.includes('pdf')) {
            html = `<iframe src="${src}" class="fp-iframe" title="${escHtml(fileName)}"></iframe>`;

        } else if (ft.includes('text/') || ft.includes('json') || ft.includes('xml') || ft.includes('javascript')) {
            // Fetch text content and display it
            html = `<div class="fp-text-loading"><span class="material-icons-outlined fp-spin">refresh</span> Loading...</div>`;
            // Will be replaced after fetch below
        } else {
            // Generic: can't preview — show a card with download button
            html = `
                <div class="fp-generic-wrap">
                    <span class="material-icons fp-generic-icon">insert_drive_file</span>
                    <p class="fp-generic-name">${escHtml(fileName)}</p>
                    <p class="fp-generic-size">${fileSize}</p>
                    <p class="fp-generic-msg">This file type cannot be previewed.</p>
                    <a href="${dlHref}" class="fp-download-big">
                        <span class="material-icons-outlined">download</span>
                        Download file
                    </a>
                </div>`;
        }

        fpBody.innerHTML = html;

        // Load text file content
        if (ft.includes('text/') || ft.includes('json') || ft.includes('xml') || ft.includes('javascript')) {
            fetch(src)
                .then(r => r.text())
                .then(text => {
                    fpBody.innerHTML = `<pre class="fp-text-content">${escHtml(text)}</pre>`;
                })
                .catch(() => {
                    fpBody.innerHTML = `<div class="fp-error">Could not load file content.</div>`;
                });
        }
    }

    // ── Public: open the modal for a given file ───────────────────────────────
    window.openFilePreview = function (fileId, fileName, fileType, fileSize) {
        buildPreview(fileId, fileName, fileType, fileSize || '');

        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        // Animate in
        requestAnimationFrame(() => {
            modal.classList.add('fp-visible');
        });
    };

    // ── Close ─────────────────────────────────────────────────────────────────
    function closePreview() {
        modal.classList.remove('fp-visible');
        setTimeout(() => {
            modal.style.display = 'none';
            fpBody.innerHTML = '';
            document.body.style.overflow = '';
        }, 220);
    }

    fpClose.addEventListener('click', closePreview);

    // Click on dark backdrop to close
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closePreview();
    });

    // Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.style.display !== 'none') {
            closePreview();
        }
    });

    // ── Utility ──────────────────────────────────────────────────────────────
    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
})();
