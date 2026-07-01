<?php
/**
 * list.php
 * 景品一覧表示画面。検索、ページング、ステータス変更等のメイン画面。
 */
// セッション開始（エラー表示用）
if (session_status() === PHP_SESSION_NONE) {
    session_start();

    // ヘッダーと同じフラグを参照
    $is_debug_mode = $_SESSION['debug_mode'] ?? false;

    if ($is_debug_mode) {
        $session_debug = "セッションを開始します";
    }
} else {
    if ($is_debug_mode) {
        $session_debug = "セッションは既に開始しています";
    }
}

// 共通接続ファイルを読み込み
require_once 'config/env.php';
require_once __DIR__ . '/includes/config.php';
require_once 'includes/db_connect.php';// $pdo を作成

// 検索・取得ロジックを読み込み
// ここで $prizes(現在のページのデータ) と $total_all_results(全件数) が作られる

require_once 'includes/pager.php'; // ページャー関数用
require_once 'includes/header.php';// ヘッダー関数用

// 2. コントローラーの呼び出し
// ここでモデルを呼び、データを取得し、変数にセットする
require_once 'controllers/list_controller.php';

// 3. ビューの呼び出し
// コントローラーで準備した変数を使ってHTMLを表示する
require_once 'views/list_view.php';


// --- ページャー表示用の変数を整理 ---
$current_page = isset($page) ? (int)$page : 1;
// ページング計算には、LIMITをかける前の「全件数」を渡す
$t_count      = isset($total_all_results) ? (int)$total_all_results : 0;
$t_limit      = isset($limit) ? (int)$limit : 20;

?>