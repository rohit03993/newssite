<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, $uri);
if ($uri !== '/' && (is_file($file) || is_dir($file))) {
    return false;
}
if (preg_match('#^/news/([^/]+)/?$#', $uri, $m)) {
    $_GET['url'] = $m[1];
    require __DIR__ . '/news/index.php';
    return true;
}
if ($uri === '/' || $uri === '/index.php') {
    require __DIR__ . '/index.php';
    return true;
}
http_response_code(404);
echo 'Page not found';
