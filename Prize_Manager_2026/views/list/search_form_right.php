<!--
* search_form_right.php
* 検索フォーム右側の項目表示
*　作品タイトル名、入荷期間、獲得状況、お気に入り状況
*
-->
<div class="filter-col-right">
    <div class="filter-group">
        <label>作品タイトル</label>
        <select name="title">
            <option value="">すべて表示</option>
            <?php foreach ($all_titles as $t): ?>
                <option value="<?= $t['id'] ?>" <?= (string)$title_filter === (string)$t['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="filter-group">
        <label>入荷期間</label>
        <div class="date-range-ui">
            <button type="button" id="setTodayBtn" class="btn-today">本日</button>
            <div class="date-select-wrapper">
                <select name="s_year" id="s_year" style="width: 75px;">
                    <option value="">----</option>
                    <?php for($y=2024; $y<=2027; $y++): ?>
                        <option value="<?= $y ?>" <?= ((string)$s_y === (string)$y) ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select> 年
                <select name="s_month" id="s_month" style="width: 55px;">
                    <option value="">--</option>
                    <?php for($m=1; $m<=12; $m++): ?>
                        <?php $m_padded = sprintf('%02d', $m); ?>
                        <option value="<?= $m_padded ?>" <?= ((string)$s_m === $m_padded) ? 'selected' : '' ?>><?= $m ?></option>
                    <?php endfor; ?>
                </select> 月
                <select name="s_day" id="s_day" style="width: 55px;">
                    <option value="">--</option>
                    <?php for($d=1; $d<=31; $d++): ?>
                        <?php $d_padded = sprintf('%02d', $d); ?>
                        <option value="<?= $d_padded ?>" <?= ((string)$s_d === $d_padded) ? 'selected' : '' ?>><?= $d ?></option>
                    <?php endfor; ?>
                </select> 日

                <!-- 開始カレンダー（IDを s_calendar にマッピング） -->
                <div class="pure-calendar-trigger">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <input type="date" id="s_calendar" value="<?= htmlspecialchars($sv_start) ?>">
                </div>
            </div>
            <span class="range-sep">～</span>

            <!-- 終了入荷日選択 -->
            <div class="date-select-wrapper">
                <select name="e_year" id="e_year" style="width: 75px;">
                    <option value="">----</option>
                    <?php for($y=2024; $y<=2027; $y++): ?>
                        <option value="<?= $y ?>" <?= ((string)$e_y === (string)$y) ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>年
                <select name="e_month" id="e_month" style="width: 55px;">
                    <option value="">--</option>
                    <?php for($i=1; $i<=12; $i++): ?>
                        <option value="<?= sprintf('%02d', $i) ?>" <?= ((string)$e_m === sprintf('%02d', $i)) ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>月
                <select name="e_day" id="e_day" style="width: 55px;">
                    <option value="">--</option>
                    <?php for($i=1; $i<=31; $i++): ?>
                        <option value="<?= sprintf('%02d', $i) ?>" <?= ((string)$e_d === sprintf('%02d', $i)) ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>日

                <!-- 終了カレンダー（IDを e_calendar にマッピング） -->
                <div class="pure-calendar-trigger">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <input type="date" id="e_calendar" value="<?= htmlspecialchars($sv_end) ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="status-row">
        <div class="filter-group filter-group-half">
            <label>獲得状態</label>
            <div class="filter-radio-group">
                <label><input type="radio" name="status" value=""<?= ($status === '') ? ' checked' : '' ?>> 全て </label>
                <label><input type="radio" name="status" value="got" <?= ($status === 'got') ? 'checked' : '' ?>> 獲得済</label>
                <label><input type="radio" name="status" value="un" <?= ($status === 'un') ? 'checked' : '' ?>> 未獲得</label>
            </div>
        </div>
        <div class="filter-group filter-group-half">
            <label>お気に入り状態</label>
            <div class="filter-radio-group">
                <label><input type="radio" name="favorite" value=""<?= (($_GET['favorite'] ?? '') === '') ? 'checked' : '' ?>>全て</label>
                <label><input type="radio" name="favorite" value="1" <?= (($_GET['favorite'] ?? '') === '1') ? 'checked' : '' ?>> ★</label>
                <label><input type="radio" name="favorite" value="0" <?= (($_GET['favorite'] ?? '') === '0') ? 'checked' : '' ?>> ☆</label>
            </div>
        </div>
    </div>

    <div class="filter-buttons">
        <button type="button" class="btn-reset" onclick="location.href='list.php'">条件リセット</button>
        <button type="submit" class="btn-search">検索</button>
    </div>
</div>