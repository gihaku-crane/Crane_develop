<?php
/**
 * item_detail.php
 * スペック情報（右側）の幅を以前のレイアウトに合わせて広げ、
 * ギャラリーを常に4枠（空きはサンプル画像）表示するよう調整。
 */
require_once 'config/env.php';
require_once __DIR__ . '/includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/header.php';



// シリーズ一覧を取得
$series_list = $pdo->query("SELECT id, name FROM series ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

$id = $_GET['id'] ?? null;
if (!$id) {
    header('location: list.php');
    exit;
}

// 1. メインの景品情報を取得
$stmt = $pdo->prepare("
    SELECT 
        p.*, 
        p.arrival_date,
        s.name as series_name, 
        GROUP_CONCAT(sh.name SEPARATOR '<br>') as shop_list
    FROM prizes p
    LEFT JOIN series s ON p.series_id = s.id
    LEFT JOIN prize_shops ps ON p.id = ps.prize_id
    LEFT JOIN shops sh ON ps.shop_id = sh.id
    WHERE p.id = ?
    GROUP BY p.id
");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

// データチェック
if (!$row) {
    echo "<p>景品が見つかりません。</p>";
    exit;
}

$status_label = ($row['got_status'] === 'got') ? '獲得済' : '未獲得';
$status_class = ($row['got_status'] === 'got') ? 'badge-got' : 'badge-un';

// 2. ギャラリー用画像の準備（常に4つ用意する）
$imgCols = ['image_url', 'image_url1', 'image_url2', 'image_url3'];
$displayImages = [];
$sample_img = 'img/gallery_sample1.png';

foreach ($imgCols as $col) {
    if (!empty($row[$col]) && $row[$col] !== 'null') {
        $displayImages[] = $row[$col];
    } else {
        $displayImages[] = $sample_img;
    }
}

// 3. 関連景品の取得
$related_prizes = [];
if (!empty($row['title_id'])) {
    $stmt_rel = $pdo->prepare("
        SELECT id, name, image_url 
        FROM prizes 
        WHERE title_id = ? AND id != ? 
        ORDER BY Arrival_date DESC, id DESC 
        LIMIT 4
    ");
    $stmt_rel->execute([$row['title_id'], $id]);
    $related_prizes = $stmt_rel->fetchAll();
}

$all_shops = $pdo->query("SELECT id, name FROM shops ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
include 'includes/modal_edit.php';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <title><?= htmlspecialchars($row['name'] ?? '景品詳細') ?> | Prize_Manager_2026</title>
    <link rel="icon" href="img/favicon.png" type="image/png" sizes="32x32"><!-- ブラウザタブアイコン -->
    <link rel="apple-touch-icon" href="apple-touch-icon.png" sizes="180x180">
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/item_detail.css">
    <link rel="stylesheet" href="css/modal_edit.css">


</head>
<body class="bg-common">
    <?php output_header('detail'); ?>

    <div class="wrapper detail-wrapper">
        <!-- ヘッダー -->
        <section class="item-detail-header-zone">
            <div class="header-left">
                <h1 class="detail-name"><?= htmlspecialchars($row['name']) ?></h1>
                <button class="btn-favorite js-favorite-toggle <?= ($row['is_favorite'] == 1) ? 'active' : '' ?>"
                        data-id="<?= $row['id'] ?>"
                        data-fav="<?= $row['is_favorite'] ?>">
                    <?= ($row['is_favorite'] == 1) ? '★' : '☆' ?>
                </button>
            </div>

            <div class="header-status-toggle">
                <span class="status-badge <?= $status_class ?> js-status-toggle"
                      id="status-update-target"
                      data-id="<?= (int)$id ?>"
                      data-status="<?= htmlspecialchars($row['got_status'] ?: 'un') ?>">
                    <?= $status_label ?>
                </span>
            </div>
        </section>

        <div class="detail-main-layout">
            <!-- 左側：ギャラリー（常に4枚表示） -->
            <div class="gallery-container">
                <?php for($i=0; $i<4; $i++): ?>
                    <input type="radio" name="gallery" id="img<?= $i ?>" <?= $i === 0 ? 'checked' : '' ?>>
                <?php endfor; ?>
                
                <div class="main-img-box">
                    <?php for($i=0; $i<4; $i++): ?>
                    <div class="main-img main-i<?= $i ?>">
                        <img src="<?= htmlspecialchars($displayImages[$i]) ?>" alt="景品画像<?= $i + 1 ?>">
                    </div>
                    <?php endfor; ?>
                </div>

                <div class="sub-img-list">
                    <?php for($i=0; $i<4; $i++): ?>
                        <label for="img<?= $i ?>" class="thumb thumb<?= $i ?>">
                            <img src="<?= htmlspecialchars($displayImages[$i]); ?>" alt="サムネイル<?= $i + 1 ?>">
                        </label>
                    <?php endfor; ?>
                </div>
                <div class="prize-memo-container">
                    <h3 class="memo-title">景品説明</h3>
                    <div class="memo-content">
                        <?= nl2br(htmlspecialchars($row['memo'] ?? '説明文はありません。')) ?>
                    </div>
                </div>
            </div>

            <!-- 右側：スペック情報（以前の幅広レイアウトを再現） -->
            <div class="specs-and-actions">
                <div class="specs-container">
                    <!-- シリーズ -->
                    <div class="spec-item">
                        <div class="spec-info">
                            <label>シリーズ</label>
                            <div class="spec-value"><?= htmlspecialchars($row['series_name'] ?? 'なし') ?></div>
                        </div>
                        <button class="row-edit-btn">✎</button>
                    </div>

                    <!-- 作品タイトル -->
                    <div class="spec-item">
                        <div class="spec-info">
                            <label>作品タイトル名</label>
                            <div class="spec-value"><?= htmlspecialchars($row['title'] ?? '未設定') ?></div>
                        </div>
                        <button class="row-edit-btn">✎</button>
                    </div>

                    <!-- サイズ -->
                    <div class="spec-item">
                        <div class="spec-info">
                            <label>サイズ</label>
                            <div class="spec-value"><?= htmlspecialchars($row['prize_size'] ?? '---') ?>
                            </div>
                        </div>
                    </div>

                    <!-- 入荷予定日 -->
                    <div class="spec-item">
                        <div class="spec-info">
                            <label>入荷予定日</label>
                            <div class="spec-value">
                                <?= !empty($row['Arrival_date']) ? htmlspecialchars($row['Arrival_date']) : '未定' ?>
                            </div>
                        </div>
                        <button class="row-edit-btn">✎</button>
                    </div>

                    <!-- 入荷店舗 -->
                    <div class="spec-item">
                        <div class="spec-info">
                            <label>入荷店舗</label>
                            <div class="spec-value">
                                <?= !empty($row['shop_list']) ? $row['shop_list'] : '未設定' ?>
                            </div>
                        </div>
                    </div>
                

                <!-- 重心予測 -->
                    <div class="spec-item">
                        <div class="spec-info">
                            <label>重心予測</label>
                            <div class="spec-value highlight">
                                <span class="gravity-text">
                                    <?php 
                                        $g_info = !empty($row['gravity_info']) ? "【" . htmlspecialchars($row['gravity_info']) . "】" : "調査中";
                                        echo $g_info . "重心";
                                    ?>
                                </span>
                                <label for="modal-toggle" class="evidence-link-text">（※画像で確認）</label>
                            </div>
                        </div>
                        <button class="row-edit-btn">✎</button>
                        
                        <input type="checkbox" id="modal-toggle" class="modal-checker">
                        <div class="modal-overlay">
                            <label for="modal-toggle" class="modal-close-bg"></label>
                            <div class="modal-content">
                                <img src="<?= !empty($row['gravity_img_url']) ? htmlspecialchars($row['gravity_img_url']) : 'img/no_image.png' ?>" alt="重心画像">
                                <label for="modal-toggle" class="btn-close">×</label>
                            </div>
                        </div>
                    </div>

                    <!-- 実際の重心 -->
                    <div class="spec-item">
                        <div class="spec-info">
                            <label>実際の重心情報</label>
                            <div class="spec-value"><?= !empty($row['gravity_actual ']) ? $row['gravity_actual  '] : '未確認' ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="main-action-area">
                    <!--<button class="btn-edit-main" onclick="location.href='edit.php?id=<?= (int)$id ?>'">編集する</button> -->
                    <div class="edit-wrapper">
                        <button type="button" id="openEditModal" class="btn-edit-main">編集する</button>
                    </div>
                    <button class="btn-delete-main">削除</button>
                </div>
            </div>
        </div>

        <!-- 関連景品セクション -->
        <section class="related-prizes">
    <h3 class="section-title">RELATED PRIZES <span>関連景品</span></h3>
    
    <?php 
    // 枚数を取得
    $prize_count = count($related_prizes);
    // 2枚以下の時だけ特別なスタイル（幅広など）を適用するためのクラスを決定
    $list_class = ($prize_count <= 2) ? 'related-list wide-layout' : 'related-list';
    ?>

    <div class="<?= $list_class ?>">
        <?php if ($prize_count > 0): ?>
            <?php foreach ($related_prizes as $rel): ?>
                <a href="item_detail.php?id=<?= $rel['id'] ?>" class="related-item-link" title="<?= htmlspecialchars($rel['name']) ?>">
                    <div class="related-item">
                        <img src="<?= !empty($rel['image_url']) ? htmlspecialchars($rel['image_url']) : 'img/no_image.png' ?>" alt="<?= htmlspecialchars($rel['name']) ?>">
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="not_related-list">関連する景品はありません</p>
        <?php endif; ?>
    </div>
</section>


        <div class="action-footer">
            <button class="btn-back" onclick="location.href='list.php'">← 一覧に戻る</button>
        </div>
    </div>

    <script src="js/common.js"></script>
    <script src="js/item_detail.js"></script>
    <script src="js/modal_edit.js"></script>

</body>
</html>