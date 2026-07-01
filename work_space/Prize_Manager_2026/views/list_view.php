<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <title>Prize_Manager_2026</title>
    <link rel="icon" href="img/favicon.png" type="image/png" sizes="32x32"><!-- ブラウザタブアイコン -->
    <link rel="apple-touch-icon" href="apple-touch-icon.png" sizes="180x180">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/list.css">
    <link rel="stylesheet" href="css/search_form.css">
    
    <script src="js/common_actions.js"></script>
    <script src="js/list.js"></script>


    <title>LIST | Prize_Manager_2026</title>
</head>
<body class="bg-common">
    <!-- ヘッダー部 -->
    <?php output_header('list'); ?>

    <div class="wrapper">
        <?php if (!empty($_SESSION['errors'])): ?>
            <div class="error-container" style="background:#fee; color:#c00; padding:15px; margin:20px 0; border:1px solid #c00; border-radius:5px;">
                <strong>発生したエラー:</strong>
                <ul style="margin:5px 0; padding-left:20px;">
                    <?php foreach ($_SESSION['errors'] as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php unset($_SESSION['errors']); ?>
        <?php endif; ?>
        <!-- 検索フォーム（外部ファイル）を読み込み -->
        <?php include 'search_form.php'; ?>

        <div class="list-utility-bar">
            <div class="total-count">
                検索結果：<span><?= $t_count ?></span>件
            </div>
        </div>
        <div class="result-stats">
            <div class="stats-left">
                <p class="total-count">全 <span><?= $t_count ?></span> 件</p>
                <p class="display-range">
                    <?php if ($t_count > 0): ?>
                        <!-- 現在表示されている範囲を計算 (例: 21 ～ 40件を表示) -->
                        <?= $offset + 1 ?> ～ <?= min($offset + $limit, $t_count) ?> 件を表示
                    <?php else: ?>
                        0 件を表示
                    <?php endif; ?>
                </p>
            </div>

            <!-- 表示件数切り替えプルダウン -->
            <div class="stats-right">
                <label for="limit-select">表示件数：</label>
                <select name="limit" id="limit-select" class="input-select-sm" form="search-form" onchange="this.form.submit()">
                    <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10件</option>
                    <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>20件</option>
                    <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50件</option>
                    <option value="100" <?= $limit == 100 ? 'selected' : '' ?>>100件</option>
                </select>
            </div>
        </div>

        <!-- ページャー（上部） -->
        <?php
        if (function_exists('renderPager')) {
            renderPager($current_page, $t_count, $t_limit);
        }
        ?>
        
        <div class="list-scroll-wrapper">
            <div class="list-container">
                <!-- テーブルヘッダー -->
                <div class="list-head">
                    <div class="cell-fav">★</div>
                    <div class="cell-img">景品画像</div>
                    <div class="cell-info">景品名 / 作品タイトル</div>
                    <div class="cell-series">シリーズ名</div>
                    <div class="cell-size">サイズ</div>
                    <div class="cell-release">入荷予定日</div>
                    <div class="cell-shop">店舗名</div>
                    <div class="cell-status">獲得状況</div>
                </div>

                <!-- 景品一覧の本体 -->
                <div class="list-body">
                    <?php if(!empty($prizes)): ?>
                        <?php foreach($prizes as $row): ?>
                            <div class="list-item">
                                <!-- お気に入りボタン（非同期JSで動作） -->
                                <div class="cell-fav">
                                    <?php $is_fav = (int)($row['is_favorite'] ?? 0); ?> 
                                    <button type="button" 
                                        class="btn-fav <?= ($is_fav === 1) ? 'star-on' : 'star-off' ?>" 
                                        onclick="toggleFavorite(<?= $row['id'] ?>, <?= $is_fav ?>)">
                                        <?= ($is_fav === 1) ? '★' : '☆' ?>
                                    </button>
                                </div>

                                <!-- 景品サムネイル画像 -->
                                <div class="cell-img">
                                    <div class="thumb-box">
                                        <?php if (!empty($row['image_url'])): ?>
                                            <a href="item_detail.php?id=<?= $row['id'] ?>">
                                                <img src="<?= htmlspecialchars($row['image_url']) ?>" class="prize-thumb">
                                            </a>
                                        <?php else: ?>
                                            <div class="no-image">
                                                <img src="img/gallery_sample1.png" class="prize-thumb">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- 景品名と作品タイトル -->
                                <div class="cell-info">
                                    <a href="item_detail.php?id=<?= $row['id'] ?>">
                                        <p class="name"><?= htmlspecialchars($row['name'] ?? '') ?></p>
                                        <p class="title"><?= htmlspecialchars($row['official_name'] ?? '未設定') ?></p>
                                    </a>
                                </div>

                                <!-- シリーズ名 -->
                                <div class="cell-series"><?= htmlspecialchars($row['series_name'] ?? 'なし') ?></div>

                                <!-- サイズ -->
                                <div class="cell-size">
                                    <p class="size-text"><?= htmlspecialchars($row['prize_size'] ?? '-') ?></p>
                                </div>

                                <!-- 入荷日Arrival_date -->
                                <div class="cell-release">
                                    <?php 
                                    // 表示ロジックの判定
                                    if (!empty($row['Arrival_date']) && $row['Arrival_date'] !== '0000-00-00') {
                                        // 具体的な入荷日がある場合
                                        echo htmlspecialchars($row['Arrival_date']);
                                    } elseif (!empty($row['release_month'])) {
                                        // 入荷日が未定で、発売月がある場合
                                        echo htmlspecialchars($row['release_month']) . "（予定）";
                                    } else {
                                        // どちらもない場合
                                        echo "未定";
                                    }
                                ?>
                                </div>

                                <!-- 獲得店舗（複数ある場合はカンマ区切りの文字列を分割して表示） -->
                                <div class="cell-shop">
                                    <?php
                                    $short_names = !empty($row['shop_short_names']) ? explode(',', $row['shop_short_names']) : [];
                                    $count = count($short_names);

                                    if ($count === 0): ?>
                                        <span style="color: #999;">未設定</span>
                                    <?php else: 
                                        $display_items = array_slice($short_names, 0, 2);
                                        ?>
                                        <a href="shop_list.php" style="text-decoration: none; color: inherit;">
                                            <?= htmlspecialchars(implode(' / ', $display_items)); ?>
                                            <?php if ($count > 2): ?>
                                                <span style="font-size: 0.85em; color: #666; margin-left: 4px;">
                                                    その他<?= ($count - 2) ?>店舗
                                                </span>
                                            <?php endif; ?>
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <!-- 獲得状況ステータスボタン（非同期JSで切替） -->
                                <div class="cell-status">
                                    <div id="status-container-<?= $row['id'] ?>">
                                        <?php $current_status = $row['got_status'] ?? 'un'; ?>
                                        <?php if($current_status === 'got'): ?>
                                            <button type="button" class="badge-got" onclick="changeStatus(<?= $row['id'] ?>, 'un')">獲得済</button>
                                        <?php else: ?>
                                            <button type="button" class="badge-un" onclick="changeStatus(<?= $row['id'] ?>, 'got')">未獲得</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="padding: 40px; text-align: center; color: var(--text-sub);">条件に一致する景品がありません。</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ページャー（下部） -->
        <?php 
        /* ページャー下配置 */
        if (function_exists('renderPager')) {
            renderPager($current_page, $t_count, $t_limit);
        }
        ?>
    </div>

    <div id="toast-container" class="toast-container"></div>
    
</body>
</html>