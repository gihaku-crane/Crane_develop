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
            <div class="prize-slider-wrapper">
            <button class="slider-btn prev-btn">&lt;</button>
            <div class="prize-slider-container">
                <div class="prize-slider-track">
                <?php foreach ($today_prizes as $prize): ?>
                    <?php include 'templates/item_card.php';/**/ ?>
                <?php endforeach; ?>
                </div>
            </div>
            <button class="slider-btn next-btn">&gt;</button>
        </div>
        <?php endif;?>
    </div>
</section>