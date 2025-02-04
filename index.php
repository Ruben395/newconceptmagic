<?php
session_start();

// Honeypot trap (invisible to humans)
if (!empty($_POST['honeypot'])) {
    // Log the bot attempt
    file_put_contents('bot_log.txt', "Bot detected: " . $_SERVER['REMOTE_ADDR'] . "\n", FILE_APPEND);
    // Redirect bots to a dummy page
    header("Location: /dummy_page.html");
    exit;
}

// Cloudflare Turnstile validation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cf-turnstile-response'])) {
    $turnstile_response = $_POST['cf-turnstile-response'];
    $secret_key = '0x4AAAAAAA7Ziq9AXpSbJ9fo02BRttyTnYY'; // Replace with your Cloudflare Turnstile Secret Key

    // Validate the Turnstile response
    $url = "https://challenges.cloudflare.com/turnstile/v0/siteverify";
    $data = [
        'secret' => $secret_key,
        'response' => $turnstile_response,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];

    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $response = json_decode($result, true);

    if ($response['success']) {
        // CAPTCHA passed, allow access
        $_SESSION['verified'] = true;
    } else {
        // CAPTCHA failed, deny access
        die("CAPTCHA failed. Access denied.");
    }
}

// If the user is not verified, show the CAPTCHA form
if (!isset($_SESSION['verified'])) {
    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Verify You Are Human</title>
        <!-- Cloudflare Turnstile Widget -->
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
        <style>
            body {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                background-color: #f4f4f4;
                font-family: Arial, sans-serif;
            }
            .turnstile-container {
                text-align: center;
            }
            .cf-turnstile {
                margin: 0 auto;
            }
        </style>
    </head>
    <body>
        <div class="turnstile-container">
           
            <form id="captcha-form" method="POST">
                <!-- Cloudflare Turnstile Widget -->
                <div class="cf-turnstile" data-sitekey="0x4AAAAAAA7ZitSVGI2u-6Ed" data-callback="onCaptchaSuccess"></div>
                <!-- Honeypot trap -->
                <input type="text" name="honeypot" style="display:none;">
            </form>
        </div>
        <script>
            // Automatically submit the form after successful CAPTCHA validation
            function onCaptchaSuccess(token) {
                document.getElementById("captcha-form").submit();
            }
        </script>
    </body>
    </html>
    ';
    exit;
}

// JavaScript-based redirect (only executed by real browsers)
echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting...</title>
    <script>
        // Redirect to the real content after 2 seconds
        setTimeout(function() {
            window.location.href = "/real_content.html";
        }, 6000);
    </script>
</head>
<body>
    <h>...</h>
</body>
</html>
';
?>
