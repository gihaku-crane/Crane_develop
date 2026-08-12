<!-- 近日入荷予定の注目パネル -->
<!-- 近日入荷予定の注目パネル（スライダー対応） -->
<section class="next-arrival" style="margin-top:40px;">
    <h2 class="section-title">次回の入荷予定
        <?php if($next_arrival_date): ?>
            <span style="font-size: 0.8em; font-weight: normal; margin-left:10px;">
                (<?php echo date('m/d', strtotime($next_arrival_date)); ?>)
            </span>
        <?php endif; ?>
    </h2>
    <?php if(!empty($next_prizes)): ?>
        <div class="prize-slider-wrapper">
            <button class="slider-btn prev-btn">&lt;</button>
            <div class="prize-slider-container">
                <div class="prize-slider-track">
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
            </div>
            <button class="slider-btn next-btn">&gt;</button>
        </div>
    <?php else: ?>
        <p>次回の入荷予定はありません。</p>
    <?php endif; ?>
</section>