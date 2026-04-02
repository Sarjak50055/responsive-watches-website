<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$usage = getStorageUsage($pdo, $user_id);
$limit = getStorageLimit($pdo, $user_id);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Drive - Get more storage</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Google+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background-color: #131314;
            color: #e3e3e3;
            font-family: 'Roboto', sans-serif;
            margin: 0;
            overflow-x: hidden;
        }

        .g-one-header {
            height: 64px;
            display: flex;
            align-items: center;
            padding: 0 24px;
            border-bottom: 1px solid #444746;
            position: sticky;
            top: 0;
            background: #1e1f20;
            z-index: 100;
        }

        .g-one-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: 'Google Sans', sans-serif;
            font-size: 22px;
            color: #e3e3e3;
            text-decoration: none;
        }

        .upgrade-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 48px 24px;
            text-align: center;
        }

        .upgrade-title {
            font-family: 'Google Sans', sans-serif;
            font-size: 36px;
            font-weight: 400;
            margin-bottom: 16px;
            color: #ffffff;
        }

        .upgrade-subtitle {
            font-size: 14px;
            color: #9aa0a6;
            max-width: 700px;
            margin: 0 auto 48px;
            line-height: 1.5;
        }

        .upgrade-subtitle a {
            color: #a8c7fa;
            text-decoration: none;
        }

        .plans-grid {
            display: flex;
            flex-direction: column;
            gap: 24px;
            align-items: center;
        }

        .plan-card {
            background-color: #1e1f20;
            border: 1px solid #444746;
            border-radius: 16px;
            width: 100%;
            max-width: 680px;
            text-align: left;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .plan-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
        }

        .plan-header {
            padding: 24px;
            border-left: 6px solid transparent;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .plan-30gb .plan-header {
            border-left-color: #7cacf8;
        }

        .plan-100gb .plan-header {
            border-left-color: #81c995;
        }

        .plan-info h3 {
            font-family: 'Google Sans', sans-serif;
            font-size: 24px;
            margin: 0 0 8px;
            color: #ffffff;
        }

        .plan-info p {
            font-size: 14px;
            color: #9aa0a6;
            margin: 0;
        }

        .plan-details {
            padding: 0 24px 24px;
            display: flex;
            gap: 48px;
            border-top: 1px solid #444746;
            padding-top: 24px;
        }

        .detail-item {
            flex: 1;
        }

        .detail-label {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 4px;
            color: #e3e3e3;
        }

        .detail-price {
            font-size: 20px;
            font-weight: 500;
            margin-bottom: 4px;
            color: #ffffff;
        }

        .detail-sub {
            font-size: 14px;
            color: #9aa0a6;
            margin-bottom: 16px;
        }

        .offer-badge {
            display: inline-block;
            background: #0f5223;
            color: #c4eed0;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 12px;
        }

        .buy-btn {
            background-color: #a8c7fa;
            color: #062e6f;
            border: none;
            padding: 10px 24px;
            border-radius: 20px;
            font-family: 'Google Sans', sans-serif;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            width: 100%;
            transition: background 0.2s;
            text-align: center;
            text-decoration: none;
            display: block;
        }

        .buy-btn:hover {
            background-color: #c2e7ff;
        }

        .buy-btn.alt {
            background-color: #354a5e;
            color: #c2e7ff;
        }

        .buy-btn.alt:hover {
            background-color: #445a70;
        }

        .back-btn {
            position: absolute;
            left: 24px;
            top: 20px;
            color: #e3e3e3;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: color 0.2s;
        }

        .back-btn:hover {
            color: #ffffff;
        }

        .drive-logo-header {
            width: 32px;
            height: 32px;
        }
    </style>
</head>

<body>
    <header class="g-one-header">
        <a href="index.php" class="back-btn">
            <span class="material-icons-outlined">arrow_back</span>
        </a>
        <a href="index.php" class="g-one-logo" style="margin-left: 40px;">
            <svg class="drive-logo-header" viewBox="0 0 24 24">
                <path fill="#34A853" d="M15.43 3.5H8.57L2 15h6.86l6.57-11.5z"></path>
                <path fill="#4285F4" d="M22 15l-6.57-11.5H8.57L15.43 15H22z"></path>
                <path fill="#FBBC05" d="M5.43 21h13.14L22 15H8.86L5.43 21z"></path>
            </svg>
            <span>Google Drive</span>
        </a>
    </header>

    <div class="upgrade-container">
        <h1 class="upgrade-title">Get more storage with a discounted plan</h1>
        <p class="upgrade-subtitle">
            Your Google Account gives you <?php echo formatSize($limit); ?> of storage, which is included as part of the total storage offered in other Google Drive plans.
            Cancel anytime. By subscribing, you agree to terms for Google Drive. <a href="#">Age limits</a>, language availability, system requirements, and other <a href="#">restrictions</a> may apply.
        </p>

        <div class="plans-grid">
            <!-- 30 GB Plan -->
            <div class="plan-card plan-30gb">
                <div class="plan-header">
                    <div class="plan-info">
                        <h3>30 GB</h3>
                        <p>total storage for Google Photos, Drive, and Gmail</p>
                    </div>
                    <span class="material-icons-outlined">expand_less</span>
                </div>
                <div class="plan-details">
                    <div class="detail-item">
                        <div class="detail-label">Pay monthly</div>
                        <div class="offer-badge">Save ₹132</div>
                        <div class="detail-price">₹15/mo</div>
                        <div class="detail-sub">for 3 months with offer, then ₹59/mo</div>
                        <a href="actions/process_purchase.php?amount=30" class="buy-btn">Get discount</a>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Pay annually, save 29%</div>
                        <div class="offer-badge">Save ₹209</div>
                        <div class="detail-price">₹499/yr</div>
                        <div class="detail-sub">for 1 year with offer, then <del style="color:#747775">₹708</del> ₹589/yr</div>
                        <a href="actions/process_purchase.php?amount=30" class="buy-btn alt">Get discount</a>
                    </div>
                </div>
            </div>

            <!-- 100 GB Plan -->
            <div class="plan-card plan-100gb">
                <div class="plan-header" style="border-bottom:none">
                    <div class="plan-info">
                        <h3>100 GB</h3>
                        <p>₹35/mo and up</p>
                        <p>Share storage with up to 5 others</p>
                    </div>
                    <span class="material-icons-outlined">expand_more</span>
                </div>
            </div>
        </div>

        <div style="margin-top: 40px;">
            <a href="#" style="color: #a8c7fa; font-size: 14px; font-weight: 500; text-decoration: none;">See all plans</a>
        </div>
    </div>
</body>

</html>