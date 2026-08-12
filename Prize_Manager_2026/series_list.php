<?php
// 必ず db_connect.php を読み込んで $pdo を作成してから呼び出す
require_once 'includes/db_connect.php';
require_once 'controllers/series_controller.php';

// ここで $pdo が存在しているかチェック
if (!isset($pdo)) {
    die("エラー：データベース接続が確立されていません。");
}

$controller = new SeriesController($pdo);
$controller->index();