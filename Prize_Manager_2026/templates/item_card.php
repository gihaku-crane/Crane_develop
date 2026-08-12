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