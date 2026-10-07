<?php
require __DIR__ . '/nm/article.php';

header('Content-Type: application/json; charset=utf-8');
$id = isset($_POST['newsid']) ? (int) $_POST['newsid'] : 0;
echo json_encode(array('ok' => nm_record_news_view($id)));
