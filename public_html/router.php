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
if (preg_match('#^/category/([^/]+)/?$#', $uri, $m)) {
    $_GET['url'] = $m[1];
    require __DIR__ . '/category/index.php';
    return true;
}
if (preg_match('#^/latest/?$#', $uri)) {
    require __DIR__ . '/latest.php';
    return true;
}
if (preg_match('#^/author/([0-9]+)/?$#', $uri, $m)) {
    $_GET['id'] = $m[1];
    require __DIR__ . '/author.php';
    return true;
}
if (preg_match('#^/page/([^/]+)/?$#', $uri, $m)) {
    $_GET['url'] = $m[1];
    require __DIR__ . '/page/index.php';
    return true;
}
if (preg_match('#^/install/?$#', $uri)) {
    require __DIR__ . '/install.php';
    return true;
}
if ($uri === '/' || $uri === '/index.php') {
    require __DIR__ . '/index.php';
    return true;
}
http_response_code(404);
echo 'Page not found';
