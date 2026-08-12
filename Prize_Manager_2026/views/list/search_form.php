<?php
/**
 * search_form.php
 * 景品一覧画面（list.php）の上部に表示される検索フィルター。
 * ここで入力された値は GET リクエストとして list.php(list_controller.php) へ送信されます。
 */

// 変数が未定義の場合に初期化（エラー回避）
$sv_start = $sv_start ?? '';
$sv_end   = $sv_end ?? '';
$keyword  = $keyword ?? '';
$title_filter = $title_filter ?? '';
$s_y = $s_y ?? '';
$s_m = $s_m ?? '';
$s_d = $s_d ?? '';
$e_y = $e_y ?? '';
$e_m = $e_m ?? '';
$e_d = $e_d ?? '';
$status = $status ?? '';

// データベースから取得したショップ一覧（プルダウン用）
$shops_stmt = $pdo->query("SELECT * FROM shops ORDER BY priority ASC, name ASC");
$all_shops = $shops_stmt->fetchAll();
?>

<section class="filter-box">
    <h2 class="filter-title">SEARCH FILTERS</h2>
    <form action="list.php" method="GET" class="search-form" id="search-form">
    <input type="hidden" name="arrival_date_start" value="<?= htmlspecialchars($_GET['arrival_date_start'] ?? '') ?>">
    
        <div class="filter-wrapper">
            
            <!-- 左右項目を読み込み -->
            <?php include 'search_form_left.php'; ?>
            <?php include 'search_form_right.php'; ?>
            
        </div>
    </form>
</section>