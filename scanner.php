<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the URLs from the input form
    $urls = $_POST['urls'];

    // Convert the URLs string into an array of URLs
    $urls_array = explode("\n", $urls);

    // Remove any whitespace and empty lines from the URLs array
    $urls_array = array_filter(array_map('trim', $urls_array));

    // Display an error message if there are no URLs to scan
    if (count($urls_array) == 0) {
        echo '<div class="error">Please enter at least one URL to scan.</div>';
        exit;
    }

    // Initialize the result array
    $results = array();

    // Loop through each URL and check for open redirect vulnerability
    foreach ($urls_array as $url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $redirect_url = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
        curl_close($ch);

        if ($http_code >= 300 && $http_code < 400 && !empty($redirect_url)) {
            $results[] = array(
                'url' => $url,
                'redirect_url' => $redirect_url,
            );
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Open Redirect Scanner</title>
</head>
<body>
    <h1>Open Redirect Scanner</h1>
    <form method="POST">
        <label for="urls">Enter URLs to scan:</label><br>
        <textarea id="urls" name="urls" rows="10" cols="50"><?php echo isset($_POST['urls']) ? htmlspecialchars($_POST['urls']) : ''; ?></textarea><br>
        <input type="submit" value="Scan">
    </form>
    <?php if (isset($results)): ?>
        <h2>Results:</h2>
        <?php if (count($results) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>URL</th>
                        <th>Redirect URL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $result): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($result['url']); ?></td>
                            <td><?php echo htmlspecialchars($result['redirect_url']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No open redirect vulnerabilities found.</p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>

