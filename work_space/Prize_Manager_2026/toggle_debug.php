<?php
// toggle_debug.php
session_start();

// 現在のセッション値を反転（未設定ならfalseから開始）
$_SESSION['debug_mode'] = !($_SESSION['debug_mode'] ?? false);

// ボタンを押したページへ戻る
$redirect = $_SERVER['HTTP_REFERER'] ?? 'Top.php';
header('Location: ' . $redirect);
exit;