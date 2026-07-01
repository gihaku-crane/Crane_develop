<?php
// controllers/detail_controller.php
require_once __DIR__ . '/../models/detail_model.php';

// 1. セッション開始
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. IDの取得とチェック
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: list.php');
    exit;
}

// 3. メイン景品データの取得
$row = getPrizeById($pdo, $id);
if (!$row) {
    echo "<p>景品が見つかりません。</p>";
    exit;
}

// 4. 表示用データの加工
$status_label = ($row['got_status'] === 'got') ? '獲得済' : '未獲得';
$status_class = ($row['got_status'] === 'got') ? 'badge-got' : 'badge-un';

// 5. ギャラリー用画像の準備
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

// 6. マスタデータ・関連景品の取得
// ※ すべて models/detail_model.php で定義した関数を使用します
$related_prizes = !empty($row['title_id']) ? getRelatedPrizes($pdo, $row['title_id'], $id) : [];
$all_shops      = getAllShops($pdo);
$series_list    = getAllSeries($pdo);


// *****************************************
// ** **
// ** モーダル用データ                     **
// ** **
// *****************************************

// 1. 全店舗リスト（プルダウン用）
$modal_all_shops = $pdo->query("SELECT id, name FROM shops")->fetchAll(PDO::FETCH_ASSOC);

// 現在登録されている店舗情報を取得
$stmt_current_shops = $pdo->prepare("
    SELECT s.id, s.name 
    FROM shops s
    JOIN prize_shops ps ON s.id = ps.shop_id
    WHERE ps.prize_id = ?
");
$stmt_current_shops->execute([$id]);
$current_shops = $stmt_current_shops->fetchAll(PDO::FETCH_ASSOC);
