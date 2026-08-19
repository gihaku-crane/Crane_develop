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

// 5. ギャラリー用画像の準備（動的ループ版）
$displayImages = [];
$sample_img = 'img/gallery_sample1.png';

// 0番目（image_url）と、1〜7番目（image_url1 〜 image_url7）をループで処理
for ($i = 0; $i <= 7; $i++) {
    // 0番目は 'image_url'、1番目以降は 'image_url1', 'image_url2' ... となるようにする
    $col = ($i === 0) ? 'image_url' : 'image_url' . $i;
    
    // カラムが存在し、データが入っており、かつ 'null' でなければ追加
    if (isset($row[$col]) && !empty($row[$col]) && $row[$col] !== 'null') {
        $displayImages[] = $row[$col];
    }
}

// もし1枚も画像が登録されていない場合のフォールバック
if (empty($displayImages)) {
    $displayImages[] = $sample_img;
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
$modal_all_shops = $pdo->query("SELECT id, name FROM shops ORDER BY priority ASC")->fetchAll(PDO::FETCH_ASSOC);

// 現在登録されている店舗情報を取得
$stmt_current_shops = $pdo->prepare("
    SELECT * 
    FROM prize_shops_flag 
    WHERE prize_id = ?
");
$stmt_current_shops->execute([$id]);
$current_shop_row = $stmt_current_shops->fetch(PDO::FETCH_ASSOC);

$current_shops = [];
if ($current_shop_row) {
    // 店舗マスタの配列を取得
    $all_shops_master = $pdo->query("SELECT id, name FROM shops")->fetchAll(PDO::FETCH_KEY_PAIR);
    // フラグが立っている店舗IDを取得して配列にする
    for ($i = 1; $i <= 12; $i++) {
        if (!empty($current_shop_row['shop_' . $i]) && $current_shop_row['shop_' . $i] == 1) {
            if (isset($all_shops_master[$i])) {
                $current_shops[] = [
                    'id'   => $i,
                    'name' => $all_shops_master[$i]
                ];
            }
        }
    }
}