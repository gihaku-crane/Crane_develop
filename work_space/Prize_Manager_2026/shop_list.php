<?php
/**
 * shop_list.php
 * 店舗一覧をテーブル形式で表示します。
 */
require_once 'config/env.php';
require_once __DIR__ . '/includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/header.php';

/**
 * 修正ポイント: 
 * image_3cb476.jpg のテーブル構造に基づきSELECT文を修正
 * address -> location_detail 
 * business_hours -> (今回は表示から外すか、一旦除外)
 */
try {
    // priority順、またはエリア順でソート
    $sql = "SELECT 
                id, 
                name, 
                area_group,       -- 都道府県/エリア
                location_detail,  -- 住所（市区町村以下）
                has_prizeon,      -- PrizeON対応
                has_elec_money    -- 電子マネー対応
            FROM shops 
            ORDER BY priority ASC, id ASC";
    $stmt = $pdo->query($sql);
    $shops = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $shops = []; 
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>店舗一覧 - PrizeON Hub</title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/shop_list.css">
</head>
<body>
    <div class="app-container">
        <?php output_header('shop'); ?>
        <header class="page-header">
            <h1 class="page-title">店舗一覧</h1>
            <p class="page-subtitle">近隣のゲームセンターとPrizeON対応状況</p>
        </header>

        <main class="content-area">
            <?php if (empty($shops)): ?>
                <div class="no-data-container">
                    <p class="no-data">登録されている店舗がありません。</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="shop-table">
                        <thead>
                            <tr>
                                <th>都道府県</th>
                                <th>店舗名</th>
                                <th>PrizeON 有/無</th>
                                <th>住所</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($shops as $shop): ?>
                                <tr>
                                    <td class="col-area">
                                        <span class="area-tag"><?php echo htmlspecialchars($shop['area_group'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </td>
                                    <td class="col-name">
                                        <a href="shop_detail.php?id=<?php echo (int)$shop['id']; ?>" class="shop-link">
                                            <?php echo htmlspecialchars($shop['name'], ENT_QUOTES, 'UTF-8'); ?>
                                        </a>
                                    </td>
                                    <td class="col-prizeon">
                                        <?php if ($shop['has_prizeon'] == 1): ?>
                                            <span class="status-on">有</span>
                                        <?php else: ?>
                                            <span class="status-off">無</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="col-address">
                                        <?php echo htmlspecialchars($shop['location_detail'], ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>