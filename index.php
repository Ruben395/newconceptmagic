<?php
// Start the session
session_start();

// Honeypot trap (invisible to humans)
if (!empty($_POST['honeypot'])) {
    // Log the bot attempt (you can save this to a file or database)
    file_put_contents('bot_log.txt', "Bot detected: " . $_SERVER['REMOTE_ADDR'] . "\n", FILE_APPEND);
    // Redirect bots to a dummy page
    header("Location: /dummy_page.html");
    exit;
}

// CAPTCHA validation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['captcha'])) {
    if ($_POST['captcha'] !== '1234') { // Replace with a real CAPTCHA validation (e.g., Google reCAPTCHA)
        die("CAPTCHA failed. Access denied.");
    }
    // CAPTCHA passed, allow access
    $_SESSION['verified'] = true;
}

// If the user is not verified, show a CAPTCHA form
if (!isset($_SESSION['verified'])) {
    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Verify You Are Human</title>
    </head>
    <body>
        <h1>Please Verify You Are Human</h1>
        <form method="POST">
            <label for="captcha">Enter the CAPTCHA (1234):</label>
            <input type="text" name="captcha" id="captcha" required>
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
    <title>...</title>
    <script>
        // Redirect to the real content after 2 seconds
        setTimeout(function() {
            window.location.href = "/real_content.html";
        }, 2000);
    </script>
</head>
<body>
    <h>Please wait while we redirect you...</h>
</body>
</html>
';
?>
