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
    </head>
    <body>
        <h1>Please Verify You Are Human</h1>
        <form method="POST">
            <!-- Cloudflare Turnstile Widget -->
            <div class="cf-turnstile" data-sitekey="0x4AAAAAAA7ZitSVGI2u-6Ed"></div>
            <!-- Honeypot trap -->
            <input type="text" name="honeypot" style="display:none;">
            <button type="submit">Submit</button>
        </form>
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
        }, 2000);
    </script>
</head>
<body>
    <h1>Please wait while we redirect you...</h1>
</body>
</html>
';
?>
