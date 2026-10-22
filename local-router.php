<?php
/**
 * Local only. PHP's built-in server does not read .htaccess.
 * Start with: C:\xampp\php\php.exe -S 127.0.0.1:8092 -t public_html local-router.php
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
$root = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');

function nm_local_page($file, $query = array())
{
    foreach ($query as $key => $value) {
        $_GET[$key] = $value;
    }
    require $file;
}

if (preg_match('#^/favicon\.ico$#', $uri)) {
    nm_local_page($root . '/favicon.php');
    return true;
}
if (preg_match('#^/news/([^/]+)/?$#', $uri, $m)) {
    nm_local_page($root . '/news/index.php', array('url' => $m[1]));
    return true;
}
if (preg_match('#^/category/([^/]+)/?$#', $uri, $m)) {
    nm_local_page($root . '/category/index.php', array('url' => $m[1]));
    return true;
}
if (preg_match('#^/latest/?$#', $uri)) {
    nm_local_page($root . '/latest.php');
    return true;
}
if (preg_match('#^/search/?$#', $uri)) {
    nm_local_page($root . '/search.php');
    return true;
}
if (preg_match('#^/author/([0-9]+)/?$#', $uri, $m)) {
    nm_local_page($root . '/author.php', array('id' => $m[1]));
    return true;
}
if (preg_match('#^/page/([^/]+)/?$#', $uri, $m)) {
    nm_local_page($root . '/page/index.php', array('url' => $m[1]));
    return true;
}
if (preg_match('#^/install/?$#', $uri)) {
    nm_local_page($root . '/install.php');
    return true;
}

$path = $root . $uri;
if ($uri !== '/' && is_file($path)) {
    return false;
}
if ($uri === '/' || $uri === '') {
    nm_local_page($root . '/index.php');
    return true;
}

http_response_code(404);
echo 'Not found';
return true;
