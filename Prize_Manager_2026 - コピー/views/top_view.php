<?php
// 日付ごとにグループ化
$grouped_prizes = [];
foreach ($upcoming_prizes as $p) {
    $grouped_prizes[$p['arrival_date']][] = $p;
}

// ヘルパー関数：景品リストに店舗名を付加する
function attachShopNames($pdo, $prizes) {
    if (empty($prizes)) return [];
    foreach ($prizes as &$prize) {
        $stmt = $pdo->prepare("
            SELECT s.name 
            FROM shops s
            JOIN prize_shops ps ON s.id = ps.shop_id
            WHERE ps.prize_id = ?
        ");
        $stmt->execute([$prize['id']]);
        $shops = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $prize['linked_shop_names'] = !empty($shops) ? implode(' / ', $shops) : '未設定';
    }
    return $prizes;
}

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


<!DOCTYPE html>
<html lang="ja">
    <head>
    	<meta charset="utf-8">
    	<meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" href="favicon.ico" type="image/x-icon">
        <title>Prize_Manager_2026</title>
        <link rel="icon" href="img/favicon.png" type="image/png" sizes="32x32"><!-- ブラウザタブアイコン -->
        <link rel="apple-touch-icon" href="apple-touch-icon.png" sizes="180x180">
        <link rel="stylesheet" href="css/common.css"> <!-- 共通用CSSを使用可能に -->
        <link rel="stylesheet" href="css/top.css"> <!-- top用CSSを使用可能に -->
        <link rel="stylesheet" href="css/calendar.css"><!-- カレンダー用CSSを使用可能に -->
    </head>
    <body>
        <?php 
        // 2. 表示（View）
        output_header('top');
        ?>

        <!-- アプリ全体のメインコンテナ -->
        <div class="app-container">
            <!-- サマリーセクション：月間の統計数値を表示 -->
            <div class="summary-section">
                <div class="summary-card">
                    <span class="summary-value"><?= number_format($monthly_arrival_total) ?></span>
                    <span class="summary-label">MONTHLY ARRIVAL</span>
                    <span class="summary-label">【今月の入荷総数】</span>
                </div>
                <div class="summary-card">
                    <span class="summary-value"><?= number_format($favorite_monthly_total) ?></span>
                    <span class="summary-label">FAVORITES</span>
                    <span class="summary-label">【お気に入り登録数】</span>
                </div>
                <div class="summary-card">
                    <span class="summary-value"><?= number_format($got_total) ?></span>
                    <span class="summary-label">TOTAL GOT</span>
                    <span class="summary-label">【今月の獲得総数】</span>
                </div>
                </div>
        
            <div class="main-layout">
                <!-- メインコンテンツエリア（左〜中央） -->
                <div class="main-content">
                    <!-- TODAY'S ARRIVAL：本日入荷の景品リスト -->
                    <section class="arrival-section">
                        <h2 class="section-title">TODAY'S PRIZE</h2>
                        <div class="arrival-grid">
                            <?php
                            /**
                             * 【TODO: DB連携】
                             * SQL: SELECT * FROM prizes WHERE release_date = CURRENT_DATE()
                             * 取得した結果を foreach で回して .item-card を出力する
                             */
                            if(empty($today_prizes)): ?>
                                <p style="color:var(--text-color); opacity: 0.6; padding: 10px;">本日の入荷情報はありません。</p>
                            <?php else: ?>
                                <?php foreach ($today_prizes as $prize): ?>
                                    <div class="item-card">
                                        <div class="item-image">
                                            <?php if (!empty($prize['image_url'])): ?>
                                                <a href="item_detail.php?id=<?= htmlspecialchars($prize['id']) ?>">
                                                    <img src="<?php echo htmlspecialchars($prize['image_url']); ?>" alt="イン景品画像">
                                                </a>
                                            <?php else: ?>
                                            <div class="no-image">NO IMAGE</div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="item-info">
                                            <div class="item-name">
                                                <a href="item_detail.php?id=<?= htmlspecialchars($prize['id']) ?>"><?php echo htmlspecialchars($prize['name']); ?>
                                                </a>
                                            </div>
                                        </div>
                                        <!-- 店舗情報の表示（中間テーブルから取得したもの） -->
                                        <div class="item-location">
                                            <i class="icon-shop"></i>
                                            <?php 
                                                // 中間テーブル経由の店舗名があれば表示、なければ「店舗未設定」
                                                echo !empty($prize['linked_shop_names']) 
                                                    ? htmlspecialchars($prize['linked_shop_names']) 
                                                    : '<span class="text-muted">店舗未設定</span>'; 
                                            ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif;?>
                        </div>
                    </section>


                    <!-- 近日入荷予定の注目パネル -->
                    <section class="next-arrival" style="margin-top:40px;">
                        <h2 class="section-title">次回の入荷予定
                            <?php if($next_arrival_date): ?>
                                <span style="fornt-size: 0.8em; font-weight: normal; margin-left:10px;">
                                    (<?php echo date('m/d', strtotime($next_arrival_date)); ?>)
                                </span>
                            <?php endif; ?>
                        </h2>
                        <?php if(!empty($next_prizes)): ?>
                            <div class="arrival-grid">
                                <?php foreach ($next_prizes as $prize): ?>
                                    <div class="item-card">
                                        <div class="item-image">
                                            <?php if($prize['image_url']): ?>
                                                <a href="item_detail.php?id=<?= htmlspecialchars($prize['id']) ?>">
                                                <img src="<?php echo htmlspecialchars($prize['image_url']); ?>" alt="景品画像">
                                                </a>
                                            <?php else: ?>
                                                <div class="no-image"> NO IMAGE</div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="item-info">
                                            <div class="item-name">
                                                <a href="item_detail.php?id=<?= htmlspecialchars($prize['id']) ?>"> <?php echo htmlspecialchars($prize['name']); ?> 
                                                </a>
                                            </div>
                                            <div class="item-location">
                                                店舗：<?php echo htmlspecialchars($prize['linked_shop_names'] ?: '未設定'); ?>
                                            </div>
                                            <div class="item-status">
                                                <span style="color: #666;">入荷待ち</span>
                                            </div>
                                        </div>
                                    </div>
                               <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                        <p>次回の入荷予定はありません。</p>
                        <?php endif; ?>
                    </section>

                <!-- WEEKLY TARGETS：今週の曜日別スケジュール -->
                <section class="weekly-section">
                    <h2 class="section-title">来週の入荷予定</h2>
                    <div class="weekly-grid">
                        <div class="target-card-full">
                            <?php if (empty($grouped_prizes)): ?>
                                <p>来週の入荷予定はありません。</p>
                            <?php else: ?>
                                <?php 
                                $week_map = ['Mon'=>'月', 'Tue'=>'火', 'Wed'=>'水', 'Thu'=>'木', 'Fri'=>'金', 'Sat'=>'土', 'Sun'=>'日'];
                                foreach ($grouped_prizes as $date => $prizes): 
                                    $date_obj = new DateTime($date);
                                    $day_label = $date_obj->format('n/j') . '(' . $week_map[$date_obj->format('D')] . ')';
                                ?>
                                    <div class="weekly-day-group">
                                        <div class="weely-date"><?= $day_label ?></div>
                                        <ul class="weekly-list">
                                            <?php foreach ($prizes as $p): ?>
                                                <li>
                                                    <a href="item_detail.php?id=<?= htmlspecialchars($p['id'] ?? '') ?>">
                                                        <?= htmlspecialchars($p['prize_name']) ?>
                                                    </a>
                                                    <span class="series-name">
                                                        (<?= htmlspecialchars($p['series_name'] ?? 'シリーズなし') ?>)
                                                    </span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            </div>

            <!-- サイドバーエリア（右） -->
            <aside class="side-bar">
                <div class="today-panel">
                    <!-- 今日の日付表示パネル -->
                    <div class="today-label">TODAY</div>
                    <div class="today-date"><?php echo date('m/d'); ?></div>
                    <div class="today-day">(<?php echo strtoupper(date('D')); ?>)</div>
                    <!-- カレンダーパーツを読み込み -->
                    <div class="calendar-wrapper">
                        <?php 
                        // 明示的にグローバル変数を渡す、あるいは存在チェックを行う
                        if (isset($cal) && !empty($cal)) {
                            include 'includes/calendar_view.php';
                        } else {
                            echo "<div style='color:red; font-size:11px; padding:10px; border:1px solid red;'>";
                            echo "ERROR: カレンダーデータ(\$cal)が取得できていません。<br>";
                            echo "calendar_logic.php の中身を確認してください。";
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>
            </aside>
        </div>
    </body>
</html>