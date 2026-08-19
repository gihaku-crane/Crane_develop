<?php


//　ヘルパー関数：景品リストに店舗名を付加する
require_once __DIR__ . '/../includes/functions.php';

// try/catch処理
require_once __DIR__ . '/../controllers/top_controller.php';

// 日付ごとにグループ化
// 3. 表示に必要なデータの整形（コントローラーが終わった後に行う）
$grouped_prizes = [];
if (!empty($upcoming_prizes)) {
    foreach ($upcoming_prizes as $p) {
        $grouped_prizes[$p['arrival_date']][] = $p;
    }
}
?>
<script src="js/common_slider.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // TOP画面のカルーセルスライダーを初期化
        initSlider('.prize-slider-wrapper', {
            isCarousel: true,
            trackSelector: '.prize-slider-track',
            cardSelector: '.item-card',
            visibleCount: 5
        });
    });
</script>

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
            <?php include 'sections/summary.php' ?>

            <div class="main-layout">
                <!-- メインコンテンツエリア（左〜中央） -->
                <div class="main-content">
                    <!-- TODAY'S ARRIVAL：本日入荷の景品リスト -->
                    <?php include 'sections/today_arrival.php' ?>

                    <!-- 近日入荷予定の注目パネル -->
                    <?php include 'sections/next_arrival.php' ?>

                    <!-- WEEKLY TARGETS：今週の曜日別スケジュール -->
                    <?php include 'sections/weekly_section.php' ?>
            </div>

            <!-- サイドバーエリア（右）カレンダー部分 -->
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