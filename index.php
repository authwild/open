<?php
if (isset($_GET['redirect'])) {
    $redirect_url = $_GET['redirect'];
    $redirect_count = isset($_GET['redirect_count']) ? intval($_GET['redirect_count']) : 0;

    if ($redirect_count < 5) {
        $redirect_count++;
        $new_url = "https://openauth.herokuapp.com/?redirect_count=$redirect_count&redirect=".urlencode($redirect_url);
        header("Location: $new_url");
        exit;
    } else {
        header("Location: " . $redirect_url);
        exit;
    }
}
?>
