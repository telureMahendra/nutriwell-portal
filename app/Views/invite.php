<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Healthie4U</title>

    <!-- Open Graph / WhatsApp / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Join me on Healthie4U!">
    <meta property="og:description"
        content="I've been using Healthie4U to stay on track with my fitness goals and it's been really helpful. Thought you might like it too! Give it a try 💪">
    <meta property="og:image" content="<?= base_url('public/assets/admin/pages/img/favicon.png') ?>">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:site_name" content="Healthie4U">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Join me on Healthie4U!">
    <meta name="twitter:description"
        content="I've been using Healthie4U to stay on track with my fitness goals and it's been really helpful. Thought you might like it too! Give it a try 💪">
    <meta name="twitter:image" content="<?= base_url('public/assets/admin/pages/img/favicon.png') ?>">

    <link rel="icon" type="image/png" href="<?= base_url('public/assets/admin/pages/img/favicon.png') ?>">

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            color: #333;
            text-align: center;
            padding: 50px 20px;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }

        .logo {
            width: 100px;
            margin-bottom: 20px;
        }

        h1 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 10px;
        }

        p {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }

        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border-left-color: #3498db;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .buttons {
            display: none;
            /* Shown only on desktop or if JS fails */
            flex-direction: row;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }

        .store-badge img {
            height: 50px;
            width: auto;
            transition: transform 0.2s;
        }

        .store-badge:hover img {
            transform: scale(1.05);
        }
    </style>
</head>

<body>

    <div class="container">
        <img src="<?= base_url('public/assets/admin/pages/img/logo.png') ?>" alt="Nutriwell" class="logo">
        <div class="spinner" id="loading-spinner"></div>
        <h1 id="status-text">Taking you to the app...</h1>
        <p>Please wait while we redirect you to your device's app store.</p>

        <div class="buttons" id="fallback-buttons">
            <a href="#" id="android-link" class="store-badge">
                <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg"
                    alt="Get it on Google Play">
            </a>
            <a href="#" id="ios-link" class="store-badge">
                <img src="https://upload.wikimedia.org/wikipedia/commons/3/3c/Download_on_the_App_Store_Badge.svg"
                    alt="Download on the App Store">
            </a>
        </div>
    </div>

    <script>
        // Get referral code from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const referrer = urlParams.get('referrer') || urlParams.get('ref') || '';

        // Store links
        // IMPORTANT: Update the iOS link when you have your Apple App Store URL!
        const androidUrl = 'https://play.google.com/store/apps/details?id=com.calibort.healthie4u.healthie4u' + (referrer ? '&referrer=' + encodeURIComponent(referrer) : '');

        // For iOS, you might need to append the referral code differently or rely on Firebase Dynamic Links/AppsFlyer
        // If it's just a standard App Store link, append it as a query param if your app handles it via a deep link handler
        const iosUrl = 'https://apps.apple.com/in/app/healthie4u/id6480464902'; // <-- CHANGE THIS TO ACTUAL APP STORE ID

        document.getElementById('android-link').href = androidUrl;
        document.getElementById('ios-link').href = iosUrl;

        function detectDeviceAndRedirect() {
            const userAgent = navigator.userAgent || navigator.vendor || window.opera;

            // iOS detection
            if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
                window.location.href = iosUrl;
                return;
            }

            // Android detection
            if (/android/i.test(userAgent)) {
                window.location.href = androidUrl;
                return;
            }

            // Desktop / Other: Show buttons manually
            document.getElementById('loading-spinner').style.display = 'none';
            document.getElementById('status-text').innerText = 'Choose your platform';
            document.getElementById('fallback-buttons').style.display = 'flex';
        }

        // Give the UI a tiny moment to render before redirecting
        setTimeout(detectDeviceAndRedirect, 800);
    </script>

</body>

</html>