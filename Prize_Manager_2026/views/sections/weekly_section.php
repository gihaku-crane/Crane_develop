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
                // ループ処理前にデータを整形(計算はここでやっておく)
                $formatted_data = [];
                foreach ($grouped_prizes as $date => $prizes): 
                    $date_obj = new DateTime($date);
                    $formatted_data[] = [
                        'label' => $date_obj->format('n/j'). '(' . $week_map[$date_obj->format('D')] . ')',
                        'items' => $prizes
                    ];
                endforeach;

                //HTML側では整理された変数を使うだけにする
                foreach($formatted_data as $day):
                ?>
                    <div class="weekly-day-group">
                        <div class="weely-date"><?= $day['label'] ?></div>
                        <ul class="weekly-list">
                            <?php foreach ($day['items'] as $p): ?>
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