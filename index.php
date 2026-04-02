<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$current_folder_id = $_GET['folder'] ?? null;
$view = $_GET['view'] ?? 'my-drive';

// Breadcrumbs (only for folder navigation)
$breadcrumbs = $current_folder_id ? getFolderPath($pdo, $current_folder_id) : [];

// Handle Folder Fetching Logic
if ($view == 'shared') {
    $folder_where = "id IN (SELECT folder_id FROM file_shares WHERE user_id = ? AND folder_id IS NOT NULL)";
} else {
    $folder_where = "user_id = ?";
}
$folder_params = [$user_id];

// Handle File Fetching Logic
if ($view == 'shared') {
    $file_where = "id IN (SELECT file_id FROM file_shares WHERE user_id = ? AND file_id IS NOT NULL)";
} else {
    $file_where = "user_id = ?";
}
$file_params = [$user_id];

switch ($view) {
    case 'starred':
        $clause = " AND is_starred = 1 AND is_trash = 0";
        $folder_where .= $clause;
        $file_where .= $clause;
        $title = "Starred";
        break;
    case 'trash':
        $clause = " AND is_trash = 1";
        $folder_where .= $clause;
        $file_where .= $clause;
        $title = "Trash";
        break;
    case 'recent':
        $clause = " AND is_trash = 0";
        $folder_where .= $clause;
        $file_where .= $clause;
        $order_by = "uploaded_at DESC LIMIT 20";
        $title = "Recent";
        break;
    case 'shared':
        $clause = " AND is_trash = 0";
        $folder_where .= $clause;
        $file_where .= $clause;
        $title = "Shared with me";
        break;
    case 'computers':
    case 'spam':
        $folder_where .= " AND 1=0";
        $file_where .= " AND 1=0";
        $title = ucfirst($view);
        break;
    case 'storage':
        $folder_where .= " AND is_trash = 0";
        $file_where .= " AND is_trash = 0";
        $order_by = "file_size DESC";
        $title = "Storage";
        break;
    default:
        $folder_where .= " AND is_trash = 0 AND parent_id " . ($current_folder_id ? "= ?" : "IS NULL");
        $file_where .= " AND is_trash = 0 AND folder_id " . ($current_folder_id ? "= ?" : "IS NULL");
        if ($current_folder_id) {
            $folder_params[] = $current_folder_id;
            $file_params[] = $current_folder_id;
        }
        $title = "My Drive";
        break;
}

// Default sorting
$sort_sql = "name ASC";

// Fetch Folders
$folders = [];
if ($view !== 'storage') {
    $folder_sql = "SELECT * FROM folders WHERE $folder_where ORDER BY $sort_sql";
    $stmt = $pdo->prepare($folder_sql);
    $stmt->execute($folder_params);
    $folders = $stmt->fetchAll();
}

// Fetch Files
$file_sql = "SELECT * FROM files WHERE $file_where ORDER BY " . ($order_by ?? $sort_sql);
$stmt = $pdo->prepare($file_sql);
$stmt->execute($file_params);
$files = $stmt->fetchAll();

// Storage info
$usage = getStorageUsage($pdo, $user_id);
$limit = getStorageLimit($pdo, $user_id);
$usage_percent = ($usage / $limit) * 100;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - Google Drive</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=1.3">
    <script src="assets/js/theme.js?v=1.0"></script>
    <script>window.SITE_URL = '<?php echo SITE_URL; ?>';</script>
</head>

<body>
    <div class="app-container">
        <!-- Header -->
        <header class="main-header">
            <div class="header-left">
                <a href="index.php?view=my-drive" class="logo-container">
                    <svg width="34" height="34" viewBox="0 0 24 24" class="drive-logo">
                        <path fill="#34A853" d="M15.43 3.5H8.57L2 15h6.86l6.57-11.5z"></path>
                        <path fill="#4285F4" d="M22 15l-6.57-11.5H8.57L15.43 15H22z"></path>
                        <path fill="#FBBC05" d="M5.43 21h13.14L22 15H8.86L5.43 21z"></path>
                    </svg>
                    <span class="logo-text">Drive</span>
                </a>
            </div>
            <div class="header-center">
                <div class="search-wrapper" id="searchWrapper">
                    <div class="search-bar">
                        <button class="icon-btn search-icon"><span
                                class="material-icons-outlined">search</span></button>
                        <input type="text" id="searchInput" placeholder="Search in Drive" autocomplete="off">
                        <button class="icon-btn search-clear-btn" id="searchClearBtn" style="display:none;" title="Clear">
                            <span class="material-icons-outlined">close</span>
                        </button>
                        <button class="icon-btn" id="filterBtn" title="Show search options">
                            <span class="material-icons-outlined">tune</span>
                        </button>
                    </div>
                    <div id="searchResults" class="search-results-dropdown" style="display:none;"></div>

                    <!-- Advanced Search Options Dropdown -->
                    <div id="searchOptions" class="search-options-dropdown" style="display:none;">
                        <div class="option-row">
                            <label>Type</label>
                            <select id="filterType" class="filter-select">
                                <option value="any">Any</option>
                                <option value="pdf">PDFs</option>
                                <option value="image">Images</option>
                                <option value="video">Videos</option>
                                <option value="folder">Folders</option>
                            </select>
                        </div>
                        <div class="option-row">
                            <label>Owner</label>
                            <select id="filterOwner" class="filter-select">
                                <option value="any">Any</option>
                                <option value="me">Owned by me</option>
                                <option value="shared">Shared with me</option>
                            </select>
                        </div>
                        <div class="option-row">
                            <label>Date modified</label>
                            <select id="filterDate" class="filter-select">
                                <option value="any">Any time</option>
                                <option value="today">Today</option>
                                <option value="7days">Last 7 days</option>
                                <option value="30days">Last 30 days</option>
                            </select>
                        </div>
                        <div class="option-actions">
                            <button class="text-btn" id="resetFilters">Reset</button>
                            <button class="pill-btn primary" id="applyFilters">Search</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="header-right">
                <button class="icon-btn" id="themeToggle" title="Toggle color theme">
                    <span class="material-icons-outlined theme-icon-dark">dark_mode</span>
                    <span class="material-icons-outlined theme-icon-light" style="display:none;">light_mode</span>
                </button>
                <button class="icon-btn" id="supportBtn" title="Support"><span class="material-icons-outlined">help_outline</span></button>
                <button class="icon-btn" id="settingsBtn" title="Settings"><span class="material-icons-outlined">settings</span></button>
                <div class="profile-section">
                    <div class="avatar" title="<?php echo $_SESSION['email']; ?>">
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                </div>
            </div>
        </header>

        <?php if (isset($_GET['msg'])): ?>
            <div style="background: var(--bg-surface); color: var(--accent-blue); padding: 12px 24px; text-align: center; font-size: 14px; font-weight: 500; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; gap: 8px;">
                <span class="material-icons" style="font-size: 18px;">info</span>
                <?php echo htmlspecialchars($_GET['msg']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'success_upgrade'): ?>
            <div style="background: #e6f4ea; color: #137333; padding: 12px 24px; text-align: center; font-size: 14px; font-weight: 500; border-bottom: 1px solid #ceead6;">
                Success! Your storage has been upgraded. Extra 30GB added to your account.
            </div>
        <?php endif; ?>

        <div class="content-wrapper">
            <aside class="sidebar">
                <div class="new-btn-container">
                    <button class="new-btn" id="newBtn">
                        <span class="plus-icon">
                            <svg viewBox="0 0 36 36">
                                <path fill="#34A853" d="M16 16v14h4V20z"></path>
                                <path fill="#4285F4" d="M30 16H20l-4 4h14z"></path>
                                <path fill="#FBBC05" d="M6 16v4h10l4-4z"></path>
                                <path fill="#EA4335" d="M20 16V6h-4v14z"></path>
                                <path fill="none" d="M0 0h36v36H0z"></path>
                            </svg>
                        </span>
                        <span>New</span>
                    </button>
                </div>

                <nav class="nav-menu">

                    <a href="index.php?view=my-drive"
                        class="nav-item <?php echo ($view == 'my-drive' || (!isset($_GET['view']) && !$current_folder_id)) ? 'active' : ''; ?>">
                        <span class="material-icons-outlined">folder</span>
                        <span>My Drive</span>
                    </a>
                    <a href="index.php?view=shared" class="nav-item <?php echo $view == 'shared' ? 'active' : ''; ?>">
                        <span class="material-icons-outlined">people_outline</span>
                        <span>Shared with me</span>
                    </a>
                    <div class="nav-divider"></div>
                    <a href="index.php?view=recent" class="nav-item <?php echo $view == 'recent' ? 'active' : ''; ?>">
                        <span class="material-icons-outlined">schedule</span>
                        <span>Recent</span>
                    </a>
                    <a href="index.php?view=starred" class="nav-item <?php echo $view == 'starred' ? 'active' : ''; ?>">
                        <span class="material-icons-outlined">star_outline</span>
                        <span>Starred</span>
                    </a>
                    <div class="nav-divider"></div>
                    <a href="index.php?view=trash" class="nav-item <?php echo $view == 'trash' ? 'active' : ''; ?>">
                        <span class="material-icons-outlined">delete_outline</span>
                        <span>Trash</span>
                    </a>
                </nav>

                <div class="storage-indicator">
                    <div class="storage-bar">
                        <div class="storage-fill" style="width: <?php echo $usage_percent; ?>%;"></div>
                    </div>
                    <div class="storage-text">
                        <?php echo formatSize($usage); ?> of <?php echo formatSize($limit); ?> used
                    </div>
                    <button class="get-storage-btn" onclick="window.location.href='upgrade.php'">Get more
                        storage</button>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="main-content">
                <div class="view-container">
                    <div class="view-header">
                        <div class="breadcrumb-nav">
                            <a href="index.php?view=<?php echo $view; ?>"><?php echo $title; ?></a>
                            <?php 
                            $total_crumbs = count($breadcrumbs);
                            foreach ($breadcrumbs as $index => $crumb): 
                                $is_last = ($index === $total_crumbs - 1);
                                if ($is_last): 
                            ?>
                                <span class="material-icons separator">chevron_right</span>
                                <span class="current-folder"><?php echo $crumb['name']; ?></span>
                                <span class="material-icons dropdown-arrow">arrow_drop_down</span>
                            <?php else: ?>
                                <span class="material-icons separator">chevron_right</span>
                                <a href="index.php?folder=<?php echo $crumb['id']; ?>"><?php echo $crumb['name']; ?></a>
                            <?php 
                                endif; 
                            endforeach; 

                            // Show arrow only if no crumbs (current is the root)
                            if (empty($breadcrumbs)): ?>
                                <span class="material-icons dropdown-arrow">arrow_drop_down</span>
                            <?php endif; ?>
                        </div>

                        <!-- Selection Toolbar -->
                        <div id="selectionToolbar" class="selection-toolbar" style="display:none;">
                            <span id="selectionCount">0 items selected</span>
                            <div class="toolbar-actions">
                                <button class="icon-btn" id="bulkRestoreBtn" onclick="bulkRestore()" title="Restore Selected" style="display:none;"><span class="material-icons-outlined">settings_backup_restore</span></button>
                                <button class="icon-btn" onclick="bulkShare()" title="Share Selected"><span class="material-icons-outlined">person_add</span></button>
                                <button class="icon-btn" onclick="bulkDownload()" title="Download Selected"><span class="material-icons-outlined">download</span></button>
                                <button class="icon-btn" onclick="bulkDelete()" title="Delete Selected"><span class="material-icons-outlined">delete</span></button>
                                <button class="icon-btn" onclick="clearSelection()" title="Clear Selection"><span class="material-icons-outlined">close</span></button>
                            </div>
                        </div>

                        <div class="view-options">
                            <?php if ($view === 'trash'): ?>
                                <form action="actions/empty_trash.php" method="POST" id="emptyTrashForm" style="display:inline;">
                                    <button type="button" id="emptyTrashBtn" class="text-btn danger" style="border: 1px solid var(--border-color); margin-right: 8px;">Empty Trash</button>
                                </form>
                            <?php endif; ?>
                            <button class="icon-btn" onclick="document.getElementById('fileInput').click()" title="Upload file">
                                <span class="material-icons-outlined">upload_file</span>
                            </button>
                            <div class="view-pill-toggle">
                                <div class="view-pill-segment" id="viewListBtn" title="List view">
                                    <span class="material-icons check-icon">check</span>
                                    <span class="material-icons">view_headline</span>
                                </div>
                                <div class="view-pill-segment" id="viewGridBtn" title="Grid view">
                                    <span class="material-icons check-icon">check</span>
                                    <span class="material-icons">grid_view</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="items-area">
                        <div class="file-grid" id="fileGrid">
                            <?php foreach ($folders as $folder): ?>
                                <div class="file-card folder"
                                    data-id="<?php echo $folder['id']; ?>"
                                    data-type="folder"
                                    data-name="<?php echo addslashes($folder['name']); ?>"
                                    oncontextmenu="showContextMenu(event, '<?php echo $folder['id']; ?>', 'folder', '<?php echo addslashes($folder['name']); ?>', <?php echo $folder['is_starred'] ? 'true' : 'false'; ?>, '')"
                                    ondblclick="window.location.href='index.php?folder=<?php echo $folder['id']; ?>'">
                                    <div class="card-icon"><span class="material-icons">folder</span></div>
                                    <div class="card-name"><?php echo $folder['name']; ?></div>
                                </div>
                            <?php endforeach; ?>

                            <?php foreach ($files as $file): ?>
                                <div class="file-card"
                                    data-id="<?php echo $file['id']; ?>"
                                    data-type="file"
                                    data-name="<?php echo addslashes($file['name']); ?>"
                                    data-filetype="<?php echo $file['file_type']; ?>"
                                    data-size="<?php echo formatSize($file['file_size']); ?>"
                                    oncontextmenu="showContextMenu(event, '<?php echo $file['id']; ?>', 'file', '<?php echo addslashes($file['name']); ?>', <?php echo $file['is_starred'] ? 'true' : 'false'; ?>, '<?php echo $file['share_token']; ?>')"
                                    ondblclick="openFilePreview('<?php echo $file['id']; ?>', '<?php echo addslashes($file['name']); ?>', '<?php echo $file['file_type']; ?>', '<?php echo formatSize($file['file_size']); ?>')">
                                    <div class="card-icon"><span
                                            class="material-icons"><?php echo getFileIcon($file['file_type']); ?></span>
                                    </div>
                                    <div class="card-name"><?php echo $file['name']; ?></div>
                                    <div class="card-meta"><?php echo formatSize($file['file_size']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (empty($folders) && empty($files)): ?>
                            <div class="empty-state">
                                <img src="https://ssl.gstatic.com/docs/doclist/images/empty_state_my_drive_v2.svg"
                                    alt="Empty" style="width: 200px; margin-bottom: 24px;">
                                <h1 style="font-weight: 400; color: #3c4043; font-size: 24px;">A place for all of your files</h1>
                                <p style="color: #5f6368; margin-top: 8px;">Drag files here or use the "New" button to upload</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>

        </div>
    </div>

    <!-- Context Menu -->
    <div id="contextMenu" class="google-menu" style="display:none;">
        <div class="menu-item" onclick="handleShareWithEmail()" id="ctxShareEmail"><span class="material-icons-outlined">person_add</span> Share with person</div>
        <div class="menu-item" onclick="handleShare()" id="ctxShare"><span class="material-icons-outlined">share</span> Copy share link</div>
        <div class="menu-item" onclick="handleCopyLink()" id="ctxCopyLink"><span class="material-icons-outlined">link</span> Copy link</div>
        <div class="menu-item" onclick="handleDownload()" id="ctxDownload"><span class="material-icons-outlined">download</span> Download</div>
        <div class="menu-item" onclick="handleRename()" id="ctxRename"><span class="material-icons-outlined">edit</span> Rename</div>
        <div class="menu-item" onclick="handleStar()" id="ctxStar"><span class="material-icons-outlined">star_outline</span> Star</div>
        <div class="menu-item" onclick="handleRestore()" id="ctxRestore" style="display:none;"><span class="material-icons-outlined">settings_backup_restore</span> Restore</div>
        <div class="menu-divider"></div>
        <div class="menu-item danger" onclick="handleDelete()" id="ctxDelete"><span class="material-icons-outlined">delete</span> Delete</div>
    </div>

    <!-- Modals -->
    <div id="newMenu" class="google-menu" style="display:none;">
        <div class="menu-item" onclick="document.getElementById('folderModal').style.display='flex'">
            <span class="material-icons-outlined">create_new_folder</span>
            <span>New folder</span>
        </div>
        <div class="menu-divider"></div>
        <div class="menu-item" onclick="document.getElementById('fileInput').click()">
            <span class="material-icons-outlined">upload_file</span>
            <span>File upload</span>
        </div>
        <div class="menu-item" onclick="document.getElementById('folderInput').click()">
            <span class="material-icons-outlined">drive_folder_upload</span>
            <span>Folder upload</span>
        </div>

    </div>

    <div id="folderModal" class="modal-overlay" style="display:none;">
        <div class="google-modal">
            <h2>New folder</h2>
            <form action="create_folder.php" method="POST">
                <input type="hidden" name="parent_id" value="<?php echo $current_folder_id; ?>">
                <input type="text" name="folder_name" placeholder="Untitled folder" autofocus required>
                <div class="modal-actions">
                    <button type="button" class="text-btn"
                        onclick="document.getElementById('folderModal').style.display='none'">Cancel</button>
                    <button type="submit" class="text-btn primary">Create</button>
                </div>
            </form>
        </div>
    </div>

    <div id="renameModal" class="modal-overlay" style="display:none;">
        <div class="google-modal">
            <h2>Rename</h2>
            <form action="actions/rename.php" method="POST">
                <input type="hidden" name="parent_id" id="parentId" value="<?php echo $current_folder_id; ?>">
                <input type="hidden" name="id" id="renameId">
                <input type="hidden" name="type" id="renameType">
                <input type="text" name="new_name" id="newNameInput" required>
                <div class="modal-actions">
                    <button type="button" class="text-btn"
                        onclick="document.getElementById('renameModal').style.display='none'">Cancel</button>
                    <button type="submit" class="text-btn primary">OK</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Upload Progress Popup -->
    <div id="uploadPopup" class="upload-popup" style="display:none;">
        <div class="up-header">
            <span id="upStatus">1 item uploading</span>
            <div class="up-actions">
                <span class="material-icons-outlined" id="upMinimize" style="cursor:pointer">remove</span>
                <span class="material-icons-outlined" id="upClose" style="display:none; cursor:pointer">close</span>
            </div>
        </div>
        <div class="up-body">
            <div class="up-item">
                <div class="up-item-info">
                    <span class="material-icons-outlined up-icon" id="upItemIcon">insert_drive_file</span>
                    <div class="up-details">
                        <div class="up-filename" id="upFilename">filename.pdf</div>
                        <div class="up-progress-container">
                            <div class="up-progress-bar" id="upProgressBar" style="width: 0%;"></div>
                        </div>
                    </div>
                    <span class="up-percentage" id="upPercentage">0%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden native inputs for JS handling -->
    <div style="display:none;">
        <input type="file" id="fileInput" onchange="handleFileUpload(this.files)">
        <input type="file" id="folderInput" webkitdirectory directory multiple onchange="handleFileUpload(this.files, true)">
    </div>

    <!-- Profile Menu Dropdown -->
    <div id="profileDropdown" class="profile-dropdown" style="display:none;">
        <div class="profile-header">
            <span><?php echo htmlspecialchars($_SESSION['email']); ?></span>
            <span class="material-icons-outlined close-btn" onclick="document.getElementById('profileDropdown').style.display='none'">close</span>
        </div>

        <div class="profile-main">
            <div class="profile-avatar-large">
                <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
            </div>
            <div class="profile-greeting">Hi, <?php echo htmlspecialchars(explode(' ', trim($_SESSION['username']))[0]); ?>!</div>
            <a href="#" class="manage-account-btn">Manage your Google Account</a>
        </div>

        <div class="account-list">
            <?php
            // Fetch all other registered users from the DB so every account is always shown
            $stmt_accounts = $pdo->prepare("SELECT id, username, email FROM users WHERE id != ? ORDER BY username");
            $stmt_accounts->execute([$_SESSION['user_id']]);
            $other_accounts = $stmt_accounts->fetchAll();
            foreach ($other_accounts as $acc):
            ?>
                <a href="actions/switch_account.php?id=<?php echo $acc['id']; ?>" class="account-item">
                    <div class="avatar-small"><?php echo strtoupper(substr($acc['username'], 0, 1)); ?></div>
                    <div class="account-info">
                        <div class="account-name"><?php echo htmlspecialchars($acc['username']); ?></div>
                        <div class="account-email"><?php echo htmlspecialchars($acc['email']); ?></div>
                    </div>
                </a>
            <?php endforeach; ?>

            <a href="login.php?add_account=1" class="account-action">
                <span class="material-icons-outlined">add</span>
                Add another account
            </a>
            <a href="logout.php" class="account-action">
                <span class="material-icons-outlined">logout</span>
                Sign out
            </a>
        </div>

        <div class="profile-storage-info">
            <span class="material-icons-outlined">cloud</span>
            <?php echo round($usage_percent, 1); ?>% of <?php echo formatSize($limit); ?> used
        </div>

        <div class="profile-footer">
            <a href="#">Privacy Policy</a>
            <span>•</span>
            <a href="#">Terms of Service</a>
        </div>
    </div>

    <!-- ═══ File Preview Modal ════════════════════════════════════════════ -->
    <div id="filePreviewModal" class="file-preview-overlay" style="display:none;" role="dialog" aria-modal="true">
        <div class="file-preview-panel">

            <!-- Top toolbar -->
            <div class="fp-toolbar">
                <div class="fp-title-wrap">
                    <span class="material-icons-outlined fp-file-icon" id="fpIcon">insert_drive_file</span>
                    <span class="fp-filename" id="fpFilename">File</span>
                </div>
                <div class="fp-actions">
                    <a class="fp-action-btn" id="fpDownloadBtn" href="#" title="Download">
                        <span class="material-icons-outlined">download</span>
                    </a>
                    <button class="fp-action-btn" id="fpCloseBtn" title="Close preview">
                        <span class="material-icons-outlined">close</span>
                    </button>
                </div>
            </div>

            <!-- Preview body -->
            <div class="fp-body" id="fpBody">
                <!-- Dynamically filled by JS -->
            </div>
        </div>
    </div>

    <!-- Support Menu -->
    <div id="supportDropdown" class="google-menu" style="display:none;">
        <div class="menu-item" onclick="alert('Help center coming soon...')"><span class="material-icons-outlined">help_outline</span> Help</div>
        <div class="menu-item" onclick="alert('Training modules coming soon...')"><span class="material-icons-outlined">description</span> Training</div>
        <div class="menu-item" onclick="alert('Checking for updates...')"><span class="material-icons-outlined">update</span> Updates</div>
        <div class="menu-divider"></div>
        <div class="menu-item" onclick="alert('Terms and Policy')">Terms and Policy</div>
        <div class="menu-item" onclick="alert('Opening feedback form...')">Send feedback</div>
    </div>

    <!-- Settings Menu -->
    <div id="settingsDropdown" class="google-menu" style="display:none;">
        <div class="menu-item" onclick="alert('Settings panel coming soon...')"><span class="material-icons-outlined">settings</span> Settings</div>
        <div class="menu-item" onclick="alert('Downloading Drive for Desktop...')"><span class="material-icons-outlined">get_app</span> Get Drive for Desktop</div>
        <div class="menu-item" onclick="alert('Keyboard shortcuts guide coming soon...')"><span class="material-icons-outlined">keyboard</span> Keyboard shortcuts</div>
    </div>

    <script src="assets/js/theme.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/search.js"></script>
    <script src="assets/js/preview.js"></script>
    <script>
        document.getElementById('newBtn').addEventListener('click', function(e) {
            e.stopPropagation();
            hideAllMenus();
            const menu = document.getElementById('newMenu');
            const rect = this.getBoundingClientRect();
            menu.style.top = (rect.bottom + 8) + 'px';
            menu.style.left = rect.left + 'px';
            menu.style.display = 'block';
        });

        document.getElementById('supportBtn').addEventListener('click', function(e) {
            e.stopPropagation();
            hideAllMenus();
            const menu = document.getElementById('supportDropdown');
            const rect = this.getBoundingClientRect();
            menu.style.top = (rect.bottom + 8) + 'px';
            menu.style.left = (rect.right - 200) + 'px';
            menu.style.display = 'block';
        });

        document.getElementById('settingsBtn').addEventListener('click', function(e) {
            e.stopPropagation();
            hideAllMenus();
            const menu = document.getElementById('settingsDropdown');
            const rect = this.getBoundingClientRect();
            menu.style.top = (rect.bottom + 8) + 'px';
            menu.style.left = (rect.right - 200) + 'px';
            menu.style.display = 'block';
        });

        document.querySelector('.avatar').addEventListener('click', function(e) {
            e.stopPropagation();
            hideAllMenus();
            const dropdown = document.getElementById('profileDropdown');
            dropdown.style.display = 'block';
        });

        function hideAllMenus() {
            document.getElementById('newMenu').style.display = 'none';
            document.getElementById('profileDropdown').style.display = 'none';
            document.getElementById('supportDropdown').style.display = 'none';
            document.getElementById('settingsDropdown').style.display = 'none';
        }

        document.addEventListener('click', () => {
            hideAllMenus();
        });
    </script>
</body>

</html>