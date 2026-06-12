<?php
/**
 * カレンダー表示コンポーネント
 * * 修正内容:
 * 1. sprintf 内の $02d を %02d に修正
 * 2. foreach の終端を endif; から endforeach; に修正
 * 3. クラス属性変数名のタイポ修正 ($calsses_attr -> $class_attr)
 * 4. 属性の結合時にスペースを確保
 */



// このブロックは本番公開時には削除するかコメントアウトしてください
$debug_mode = true; 

if ($debug_mode) {
    echo "<!-- DEBUG START -->";
    echo "<div style='display:none;'>"; // 開発者ツールのソース表示でのみ確認したい場合
    echo "DEBUG: pdo exists? " . (isset($pdo) ? 'Yes' : 'No') . "\n";
    echo "DEBUG: cal exists? " . (isset($cal) ? 'Yes' : 'No') . "\n";
    echo "DEBUG: year = " . ($year ?? 'NULL') . "\n";
    echo "DEBUG: month = " . ($month ?? 'NULL') . "\n";
    echo "</div>";

}

// 1. 変数の存在確認
if (!isset($cal)) {
    echo "<div style='border:2px solid red; padding:10px; background:#fff0f0; font-size:12px; color:red;'>";
    echo "<strong>[DEBUG ERROR]</strong> 変数 \$cal が定義されていません。<br>";
    echo "Top.php で calendar_logic.php を読み込んだ後、このファイルを include しているか確認してください。";
    echo "</div>";
    return;
}
// ロジックからのデータがない場合は中断
if (!isset($cal) || !isset($cal['calendar_date'])) {
    echo "<!-- カレンダーデータがありません -->";
    echo "<div style='border:2px solid orange; padding:10px; background:#fffaf0; font-size:12px; color:orange;'>";
    echo "<strong>[DEBUG WARNING]</strong> \$cal は存在しますが、内容が正しくありません。<br>";
    echo "calendar_logic.php 内で \$cal['calendar_date'] を正しく生成しているか確認してください。<br>";
    echo "現在の \$cal の型: " . gettype($cal) . "<br>";
    echo "中身のプレビュー: <pre style='font-size:10px;'>" . htmlspecialchars(print_r($cal, true)) . "</pre>";
    echo "</div>";
    return;
}
// データの受信チェック
if (!isset($cal) || !isset($cal['calendar_date']) || !is_array($cal['calendar_date'])) {
    echo "<div style='color:red; font-size:12px; padding:10px;'>カレンダーデータが正しく読み込めていません。</div>";
    return;
}

$year          = $cal['year'];
$month         = $cal['month'];
$calendar_date = $cal['calendar_date'];

// calendar_logic.phpで計算したナビゲーション用データ
$prev_y = $cal['prev_year'];
$prev_m = $cal['prev_month'];
$next_y = $cal['next_year'];
$next_m = $cal['next_month'];

// 今月の日付を取得(今月ボタン用)
$today_y = (int)date('Y');
$today_m = (int)date('n');
$today_d = (int)date('j');

// カレンダー前月・翌月 (dateのフォーマットを修正)
$prev_month = date('Y-m', mktime(0, 0, 0, (int)$month - 1, 1, (int)$year));
$next_month = date('Y-m', mktime(0, 0, 0, (int)$month + 1, 1, (int)$year));
?>

<div class="calendar-container"><!-- カレンダー全体を囲む枠 -->
	
    <div class="calendar-header"><!-- 年月を表示するエリアを囲むヘッダー部 -->
        <h3 class="calendar-title"><?php echo (int)$year; ?>年 <?php echo (int)$month; ?>月</h3>
    </div>
    <!-- カレンダーヘッダ：前月・今月・翌月のリンク -->
	<div class="calendar-header-nav">
		<div class="nav-main">
			<a href="Top.php?y=<?php echo $cal['prev_year']; ?>&m=<?php echo $cal['prev_month']; ?>" class="nav-arrow prev-link">«</a>
            <!-- 今月へ戻るリンク -->
			<div class="nav-sub">
				<a href="?y=<?php echo $today_y; ?>&m=<?php echo $today_m; ?>" class="today-link">今月</a>
			</div>
            <a href="Top.php?y=<?php echo $cal['next_year']; ?>&m=<?php echo $cal['next_month']; ?>" class="nav-arrow next-link">»</a>
		</div>
	</div>
    <table class="calendar-table"><!-- カレンダーを構成する表示 -->
        <thead>
            <tr>
                <th class="sun">日</th>
                <th>月</th>
                <th>火</th>
                <th>水</th>
                <th>木</th>
                <th>金</th>
                <th class="sat">土</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            /* 週ごとのループ */
            foreach ($calendar_date as $week): 
            ?>
                <tr>
                    <?php 
                    /* 1日ごとのループ */
                    foreach ($week as $day_info): 
                        $classes = [];
                        
                        if (empty($day_info['day'])) {
                            $classes[] = 'empty';
                        } else {
                            // 曜日判定 (0:日, 6:土)
                            if (isset($day_info['dw'])) {
                                if ($day_info['dw'] == 0) $classes[] = 'holiday-cell';
                                if ($day_info['dw'] == 6) $classes[] = 'sat-cell';
                            }

                            // 今日の判定
                            if (isset($day_info['is_today']) && $day_info['is_today']) {
                                $classes[] = 'today-cell';
                            }

                            // 景品入荷日の判定
                            if (!empty($day_info['prizes'])) {
                                $classes[] = 'prizes-cell';
                            }

                            // お気に入り判定
                            if (isset($day_info['has_favorite']) && $day_info['has_favorite']) {
                                $classes[] = 'has-favorite';
                            }
                        }

                        // クラス属性の組み立て（スペースを考慮）
                        $class_attr = !empty($classes) ? ' class="' . implode(' ', $classes) . '"' : '';
                    ?>
                        <td<?php echo $class_attr; ?>>
                            <?php if (!empty($day_info['day'])): ?>
                                <?php 
                                    // %02d に修正済み
                                $target_date = sprintf('%04d-%02d-%02d', (int)$year, (int)$month, (int)$day_info['day']);
                                //$target_date = sprintf('%04d-%02d-%02d', (int)$year, (int)$month, (int)$day_info['day']);
                                $link_url = "list.php?arrival_date_start=" . htmlspecialchars($target_date, ENT_QUOTES, 'UTF-8') . "&search=1";
                                //$link_url = "list.php?date=" . htmlspecialchars($target_date, ENT_QUOTES, 'UTF-8');
                                ?>
                                
                                <?php if (!empty($day_info['prizes'])): ?>
                                    <!-- 景品がある日はリンクにする -->
                                    <a href="<?php echo $link_url; ?>">
                                        <span class="day-num"><?php echo (int)$day_info['day']; ?></span>
                                        <?php if (!empty($day_info['has_favorite']) && $day_info['has_favorite']): ?>
                                            <span class="star-mark">★</span>
                                        <?php endif; ?>
                                    </a>
                                <?php else: ?>
                                    <!-- 景品がない日は数字のみ -->
                                    <span class="day-num"><?php echo (int)$day_info['day']; ?></span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>