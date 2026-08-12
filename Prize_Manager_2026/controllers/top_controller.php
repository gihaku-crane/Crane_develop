<?php
// controllers/top_controller.php
require_once __DIR__ . '/../models/top_model.php';
// controllers/top_controller.php
require_once __DIR__ . '/../models/top_model.php';
// 関数ファイルはここで再度念のため読み込んでおく
require_once __DIR__ . '/../includes/functions.php';

// Top.php 冒頭のロジック部分に追加
$today = new DateTime();
$next_week_start = clone $today;
$next_week_start->modify('next monday');
$next_week_end = clone $next_week_start;
$next_week_end->modify('+6 days');

// DBからデータを取得
$upcoming_prizes = getUpcomingPrizes($pdo, $next_week_start->format('Y-m-d'), $next_week_end->format('Y-m-d'));

//try/catch
try {
    $year = isset($_GET['y']) ? (int)$_GET['y'] : (int)date('Y');
    $month = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('n');


    // $pdo が正しく接続されているか確認
    if (!isset($pdo)) {
        die("DEBUG ERROR: \$pdo が定義されていません。db_connect.php を確認してください。");
    }
    
    // --- サマリー用データ取得（ここを修正） ---
    $this_month = date('Y-m');

    // --- 統計データ取得ブロック ---
    $this_month_start = date('Y-m-01');
    $this_month_end   = date('Y-m-t');

    // 1. 今月の入荷総数
    $stmt1 = $pdo->prepare("SELECT COUNT(*) FROM prizes WHERE arrival_date LIKE ?");
    $stmt1->execute([$this_month . '%']);
    $monthly_arrival_total = (int)$stmt1->fetchColumn();

    // 2. お気に入り登録総数 (is_favoriteカラムがある前提)
    $stmt_fav = $pdo->prepare("
        SELECT COUNT(*) 
        FROM prizes 
        WHERE is_favorite = 1 
        AND arrival_date BETWEEN ? AND ?
    ");
    $stmt_fav->execute([$this_month_start, $this_month_end]);
    $favorite_monthly_total = (int)$stmt_fav->fetchColumn();

    // 3. 獲得総数 (got_status が 'got' の場合)
    $stmt3 = $pdo->prepare("
        SELECT COUNT(*) 
        FROM prizes 
        WHERE got_status = 'got' 
        AND get_date BETWEEN ? AND ?
    ");
    $stmt3->execute([$this_month_start, $this_month_end]);
    $got_total = (int)$stmt3->fetchColumn();

    // カレンダーデータの取得
    $cal = getCalendarData($pdo, $year, $month);

    // 本日の入荷データ取得
    $today = date('Y-m-d');
    $stmt_today = $pdo->prepare("SELECT * FROM prizes WHERE arrival_date = ? ORDER BY id DESC");
    $stmt_today->execute([$today]);
    $today_prizes = attachShopNames($pdo, $stmt_today->fetchAll(PDO::FETCH_ASSOC));

    // 次回の入荷予定取得
    $stmt_next_date = $pdo->prepare("SELECT arrival_date FROM prizes WHERE arrival_date > ? ORDER BY arrival_date ASC LIMIT 1");
    $stmt_next_date->execute([$today]);
    $next_arrival_date = $stmt_next_date->fetchColumn();

    $next_prizes = [];
    if ($next_arrival_date) {
        $stmt_next = $pdo->prepare("SELECT * FROM prizes WHERE arrival_date = ? ORDER BY id ASC");
        $stmt_next->execute([$next_arrival_date]);
        $next_prizes = attachShopNames($pdo, $stmt_next->fetchAll(PDO::FETCH_ASSOC));
    }

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
    $favorite_monthly_total = 0;
}
?>