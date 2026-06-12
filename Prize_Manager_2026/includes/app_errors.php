<?php
// includes/app_errors.php

/**
 * アクション失敗時のエラー切り分け関数
 */
function handleActionError($e, $user_message) {
    // 1. 開発者用ログ：原因を特定するために詳細を記録
    error_log("[" . date('Y-m-d H:i:s') . "] ACTION ERROR: " . $e->getMessage());
    
    // 2. ユーザー用通知：セッションにエラーを格納
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['errors'] = [$user_message];
    
    // 3. 一覧画面へ退避（リダイレクト）
    // BASE_URL が定義されている前提
    $redirect_url = defined('BASE_URL') ? BASE_URL . '/list.php' : '/list.php';
    header("Location: " . $redirect_url);
    exit;
}