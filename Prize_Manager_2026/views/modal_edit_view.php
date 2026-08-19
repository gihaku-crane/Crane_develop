<div id="editModal" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <form action="<?= BASE_URL ?>/update_prize.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
            
            <div class="modal-header">
                <h3>景品情報の編集</h3>
            </div>

            <div class="form-group">
                <label>景品名</label>
                <input type="text" name="name" id="edit_name" value="<?= htmlspecialchars($row['name'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label>シリーズ</label>
                <select name="series_id" id="edit_series">
                    <?php foreach($series_list as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= ($row['series_id'] == $s['id'] ? 'selected' : '') ?>>
                            <?= htmlspecialchars($s['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>作品タイトル名</label>
                <input type="text" name="title" id="edit_title" value="<?= htmlspecialchars($row['title'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>サイズ</label>
                <input type="text" name="prize_size" id="edit_size" value="<?= htmlspecialchars($row['prize_size'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>入荷予定日</label>
                <input type="date" name="arrival_date" id="edit_date" value="<?= htmlspecialchars($row['arrival_date'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>入荷店舗</label>
                <div id="shopTags" class="shop-tags-container">
                    <?php if (isset($current_shops) && is_array($current_shops)):
                        foreach ($current_shops as $shop): ?>
                            <span class="shop-tag">
                                <?= htmlspecialchars($shop['name']) ?> 
                                <button type="button" class="remove-shop">×</button>
                                <input type="hidden" name="shop_ids[]" value="<?= htmlspecialchars($shop['id']) ?>">
                            </span>
                        <?php endforeach;
                    endif; ?>
                </div>

                <div class="shop-add-wrapper" style="display: flex; gap: 10px; margin-top: 5px;">
                    <select id="shopSelect" class="shop-select">
                        <option value="">店舗を選択...</option>
                        <?php foreach ($all_shops as $s): ?>
                            <option value="<?= htmlspecialchars($s['id']) ?>"><?= htmlspecialchars($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" id="addShopBtn" class="btn-add">追加</button>
                </div>
            </div>

            <div class="form-group">
                <label>重心予測</label>
                <input type="text" name="gravity_pred" id="edit_gravity" value="<?= htmlspecialchars($row['gravity_pred'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>実際の重心情報</label>
                <textarea name="gravity_actual" id="edit_gravity_actual"><?= htmlspecialchars($row['gravity_actual'] ?? '') ?></textarea>
            </div>

            <!-- メイン画像用(確認・差し替え用) -->
            <div class="form-group">
                <label>メイン画像</label>
                <div class="modal-image-group">
                    <input type="file" name= "main_image" accept="image/*">
                    <input type="text" name="main-image-url" value="<?= htmlspecialchars($row['image_url'] ?? '') ?>" placeholder="メイン画像のURL">
                </div>
            </div>

            <!-- ギャラリー画像用(固定3枚→7枚に拡大) -->
            <div class="form-group">
                <label>ギャラリー画像(最大7枚まで)</label>
                <div id="galleyContainer">
                    <?php
                    for($i=1; $i <= 7; $i++):
                        $colName = 'image_url'.$i;
                        $currentVal = $row[$colName] ?? '';
                    ?>
                        <div class="gallery-input-row">
                            <input type="file" name="gallery_images[]" accept="image/*">
                            <input type="text" name="gallery_urls[]" value="<?= htmlspecialchars($currentVal) ?>" placeholder="ギャラリー<?= $i ?> URL">
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <div class="modal-actions" style="text-align:right; margin-top: 20px;">
                <button type="submit" class="btn-save">保存する</button>
                <button type="button" id="closeModalBtn" class="btn-cancel">キャンセル</button>
            </div>
        </form>
    </div>
</div>