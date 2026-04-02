<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$token = $_GET['token'] ?? null;
$file = null;

if ($token) {
    $stmt = $pdo->prepare("SELECT * FROM files WHERE share_token = ?");
    $stmt->execute([$token]);
    $file = $stmt->fetch();
}

if (!$file) {
    die("Invalid sharing link or file no longer exists.");
}

$file_name = $file['name'];
$file_size = formatSize($file['file_size']);
$file_icon = getFileIcon($file['file_type']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Downloading <?php echo htmlspecialchars($file_name); ?> - Drive</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0f1115;
            --card-bg: #1e2128;
            --primary-color: #8ab4f8;
            --text-main: #e8eaed;
            --text-dim: #9aa0a6;
            --accent-glow: rgba(138, 180, 248, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .download-container {
            text-align: center;
            background: var(--card-bg);
            padding: 3rem;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            max-width: 450px;
            width: 90%;
            position: relative;
            border: 1px solid rgba(255,255,255,0.05);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .icon-circle {
            width: 100px;
            height: 100px;
            background: var(--bg-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            position: relative;
        }

        .icon-circle .material-icons-outlined {
            font-size: 48px;
            color: var(--primary-color);
        }

        .pulse {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: var(--primary-color);
            opacity: 0.3;
            animation: pulse-ring 2s infinite;
        }

        @keyframes pulse-ring {
            0% { transform: scale(0.8); opacity: 0.5; }
            100% { transform: scale(1.5); opacity: 0; }
        }

        h1 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .file-info {
            color: var(--text-dim);
            margin-bottom: 2.5rem;
            font-size: 0.95rem;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            background: var(--accent-glow);
            color: var(--primary-color);
            border-radius: 100px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 1.5rem;
        }

        .progress-container {
            width: 100%;
            height: 6px;
            background: #2d3139;
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .progress-bar {
            height: 100%;
            background: var(--primary-color);
            width: 0%;
            transition: width 0.1s linear;
            box-shadow: 0 0 10px var(--primary-color);
        }

        .footer-text {
            font-size: 0.8rem;
            color: var(--text-dim);
        }

        .footer-text a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }

        .download-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: var(--primary-color);
            color: #000;
            padding: 16px 32px;
            border-radius: 100px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(138, 180, 248, 0.3);
            border: none;
            cursor: pointer;
            width: 100%;
        }

        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(138, 180, 248, 0.4);
            filter: brightness(1.1);
        }

        .download-btn .material-icons-outlined {
            font-size: 20px;
        }

        /* Subtle background glow */
        .glow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            height: 300px;
            background: var(--primary-color);
            filter: blur(100px);
            opacity: 0.1;
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="glow"></div>
    <div class="download-container">
        <div class="icon-circle">
            <div class="pulse"></div>
            <span class="material-icons-outlined"><?php echo $file_icon; ?></span>
        </div>
        
        <div class="status-badge" id="statusBadge">Preparing your download...</div>
        
        <h1><?php echo htmlspecialchars($file_name); ?></h1>
        <div class="file-info"><?php echo $file_size; ?> • Shared via Drive</div>

        <div class="progress-container">
            <div class="progress-bar" id="progressBar"></div>
        </div>

        <a href="actions/stream_shared.php?token=<?php echo $token; ?>" class="download-btn" id="mainDownloadBtn">
            <span class="material-icons-outlined">download</span>
            Download Now
        </a>

        <div class="footer-text">
            Your download will start automatically. <br>
            If it doesn't, <a href="actions/stream_shared.php?token=<?php echo $token; ?>">click here to download manually</a>.
        </div>
    </div>

    <script>
        const progressBar = document.getElementById('progressBar');
        const statusBadge = document.getElementById('statusBadge');
        const mainDownloadBtn = document.getElementById('mainDownloadBtn');
        const token = "<?php echo $token; ?>";
        let progress = 0;

        // Simulate a smooth loading experience before starting the actual download
        const interval = setInterval(() => {
            progress += 2;
            progressBar.style.width = progress + '%';

            if (progress === 40) {
                statusBadge.innerText = "Connecting to server...";
            }
            if (progress === 70) {
                statusBadge.innerText = "Starting download...";
            }

            if (progress >= 100) {
                clearInterval(interval);
                statusBadge.innerText = "Downloading...";
                // Trigger the actual download
                window.location.href = `actions/stream_shared.php?token=${token}`;
                
                mainDownloadBtn.innerHTML = '<span class="material-icons-outlined">check</span> Finished';
                mainDownloadBtn.style.background = '#81c995';
                
                // After triggering, we can show a success message
                setTimeout(() => {
                    statusBadge.innerText = "Download started!";
                    statusBadge.style.background = "rgba(52, 168, 83, 0.15)";
                    statusBadge.style.color = "#81c995";
                }, 1000);
            }
        }, 30);
    </script>
</body>
</html>
