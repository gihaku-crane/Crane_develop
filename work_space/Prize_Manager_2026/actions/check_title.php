<?php
/*
// 1. エラー表示を物理的に遮断
ini_set('display_errors', 0);
error_reporting(0);

// 2. 出力バッファリングを開始（require先の警告もここでキャッチして捨てる）
ob_start();
*/


//////////////////////////////////////////////////////////

//デバッグ用
// エラーを隠さない設定にする
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/db_connect.php';


//////////////////////////////////////////////////////////





// 3. データベース接続
// ここで万が一DB接続エラー(Warning)が発生しても、
// 下記の ob_end_clean() で全て消し去ります
require_once '../includes/db_connect.php';

// ここでバッファを破棄（警告があっても全て消去）
ob_end_clean(); 

$title = $_GET['q'] ?? '';

// DB操作
$stmt = $pdo->prepare("SELECT COUNT(*) FROM titles WHERE name = ?");
$stmt->execute([$title]);
$exists = $stmt->fetchColumn() > 0;

// JSONのみを確実に出力
header('Content-Type: application/json');
die(json_encode(['exists' => $exists]));