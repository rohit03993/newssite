<?php
require __DIR__ . '/nm/view.php';
$brand = nm_settings(array('brand_favicon'));
$src = nm_favicon_src($brand);
if ($src === '') {
    http_response_code(204);
    exit;
}
header('Location: ' . $src, true, 302);
exit;
