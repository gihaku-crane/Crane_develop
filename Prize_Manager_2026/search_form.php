<?php
/**
 * search_form.php
 * 景品一覧画面（list.php）の上部に表示される検索フィルター。
 * ここで入力された値は GET リクエストとして list.php(search_logic.php) へ送信されます。
 */

// 変数が未定義の場合に初期化（エラー回避）
$sv_start = $sv_start ?? '';
$sv_end   = $sv_end ?? '';
$keyword  = $keyword ?? '';
$title_filter = $title_filter ?? '';
$s_y = $s_y ?? '';
$s_m = $s_m ?? '';
$s_d = $s_d ?? '';
$e_y = $e_y ?? '';
$e_m = $e_m ?? '';
$e_d = $e_d ?? '';
$status = $status ?? '';

// データベースから取得したショップ一覧（プルダウン用）
$shops_stmt = $pdo->query("SELECT * FROM shops ORDER BY priority ASC, name ASC");
$all_shops = $shops_stmt->fetchAll();
?>

<section class="filter-box">
    <h2 class="filter-title">SEARCH FILTERS</h2>
    <!-- 
        method="GET" にすることで、検索条件がURL（?keyword=...）に付与されます。
        これにより、検索結果のページをブックマークしたり、ページングしても検索条件が維持されます。
    -->
    <form action="list.php" method="GET" class="search-form" id="search-form">
    <input type="hidden" name="arrival_date_start" value="<?= htmlspecialchars($_GET['arrival_date_start'] ?? '') ?>">
    
        <div class="filter-rows-container centered-frame">
            
            <!-- 1行目：基本キーワードと作品タイトル -->
            <div class="filter-row" style="display: flex; gap: 30px; margin-bottom: 20px; align-items: flex-start;">
                <!-- キーワード検索：景品名や作品名などを自由に検索 -->
                <div class="filter-group">
                    <label>キーワード</label>
                    <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" placeholder="景品名、作品名を入力..." style="width: 350px;">
                </div>

                <!-- 作品タイトル絞り込み：DBにある作品マスター(titlesテーブル)から選択 -->
                <div class="filter-group" style="flex: 1;">
                    <label>作品タイトル</label>
                    <div class="input-with-icon" style="display: flex; align-items: center; gap: 5px;">
                        <select name="title" style="width: 100%; max-width: 400px;">
                            <option value="">すべて表示</option>
                            <?php foreach ($all_titles as $t): ?>

                                <!-- 現在選択中のIDと一致すれば selected を付与 -->
                                <option value="<?= $t['id'] ?>" <?= (string)$title_filter === (string)$t['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>


            <!-- 2行目：シリーズ名・ショップ・獲得状態 -->
            <div class="filter-row" style="display: flex; gap: 30px; align-items: flex-start;">

                <!-- シリーズ名：一番くじやCorefulなどのシリーズ名で部分一致検索 -->
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


                <!-- 2行目：入荷日（期間指定） -->
                <!-- 開始入荷日 -->
                <div class="filter-group filter-group-double">
                    <label>入荷期間</label>
                    <div class="date-range-ui">
                        <!-- 本日ボタン -->
                        <button type="button" id="setTodayBtn" class="btn-today">本日</button>
                        
                        <!-- 開始入荷日選択 -->
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
            </div>

            <!-- 獲得状態検索：ラジオボタン形式 -->
            <div class="filter-row filter-row-flex">
                <!-- 店舗検索 -->
                <div class="filter-group">
                    <label>入荷店舗</label>
                    <select name="shop">
                        <option value="">すべての店舗</option>
                        <?php
                        // 店舗マスタを全取得して回す
                        $all_shops = $pdo->query("SELECT id, name FROM shops ORDER BY priority ASC")->fetchAll();
                        foreach ($all_shops as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= (($_GET['shop'] ?? '') == $s['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- 獲得状態検索 -->
                <div class="filler-group filter-group-half">
                    <label>獲得状態</label>
                    <div class="filter-radio-group filter-radio-group">
                        <label>
                            <input type="radio" name="status" value=""<?= ($status === '') ? ' checked' : '' ?>> 全て 
                        </label>
                        <label>
                            <input type="radio" name="status" value="got" <?= ($status === 'got') ? 'checked' : '' ?>> 獲得済
                        </label>
                        <label>
                            <input type="radio" name="status" value="un" <?= ($status === 'un') ? 'checked' : '' ?>> 未獲得
                        </label>

                    </div>
                </div>
            </div>

            <!-- 検索アクションボタン -->
            <div class="filter-buttons" style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <!-- 
                    条件リセット：
                    URLパラメータなしで list.php を呼び出すことで、すべての検索条件を初期化します。
                -->
                <button type="button" class="btn-reset" onclick="location.href='list.php'">条件リセット</button>
                <button type="submit" class="btn-search">検索</button>
            </div>
        </div>
    </form>
</section>