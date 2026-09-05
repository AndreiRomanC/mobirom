<?php
$toolFile = __DIR__ . '/tooluri/' . ($toolSlug ?? '') . '.php';
if (!$toolSlug || !file_exists($toolFile)) {
    http_response_code(404);
    $pageTitle = 'Tool negăsit';
    require __DIR__ . '/../templates/header.php';
    echo '<div class="container" style="padding:4rem 0;text-align:center"><h1>Tool negăsit</h1><a href="/tooluri/" class="btn btn-primary" style="margin-top:1rem">← Toate toolurile</a></div>';
    require __DIR__ . '/../templates/footer.php';
    exit;
}
require $toolFile;
