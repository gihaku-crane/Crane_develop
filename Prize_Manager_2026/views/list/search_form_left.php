<!--
* search_form_left.php
* 検索フォーム左側の項目表示
*　キーワード、シリーズ名、入荷店舗
*
-->
<div class="filter-col-left">
    <div class="filter-group">
        <label>キーワード</label>
        <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" placeholder="景品名、作品名を入力...">
    </div>
    <div class="filter-group">
        <label>シリーズ名</label>
        <select name="series_id"> 
            <option value="">全てのシリーズ</option>
            <?php foreach ($all_series as $s): ?>
                <option value="<?= $s['id'] ?>" <?= (isset($_GET['series_id']) && $_GET['series_id'] == $s['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filter-group">
        <label>入荷店舗</label>
        <select name="shop">
            <option value="">すべての店舗</option>
            <?php foreach ($all_shops as $s): ?>
                <option value="<?= $s['id'] ?>" <?= (($_GET['shop'] ?? '') == $s['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>