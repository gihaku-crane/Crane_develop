<?php
/**
 * shop_detail.php
 * 未登録情報を「情報なし」と表示し、公式HPとX(Twitter)ボタンを設置
 */

// 共通接続ファイルを読み込み
require_once 'config/env.php';
require_once __DIR__ . '/includes/config.php';
require_once 'includes/db_connect.php'; 

require_once 'includes/header.php';

$shop_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

try {
    $stmt = $pdo->prepare("SELECT * FROM shops WHERE id = :id");
    $stmt->bindValue(':id', $shop_id, PDO::PARAM_INT);
    $stmt->execute();
    $shop = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$shop) {
        die('店舗データが見つかりませんでした。');
    }
} catch (PDOException $e) {
    die('システムエラーが発生しました。');
}

// タグ生成ロジック
$feature_tags = [];
if (!empty($shop['has_prizeon'])) $feature_tags[] = 'PrizeON導入店';
if (!empty($shop['has_elec_money'])) $feature_tags[] = '電子マネー対応';
if (!empty($shop['location_detail'])) $feature_tags[] = $shop['location_detail'];
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($shop['name']); ?> - PrizeON Hub</title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/shop_detail.css">
</head>
<body>
    <div class="app-container">
        <!-- ヘッダー部 -->
    <?php output_header('shop_detail'); ?>

        <!-- パンくずリスト -->
        <nav class="breadcrumb">
            <a href="shop_list.php">店舗一覧</a> <span>/</span> <?php echo htmlspecialchars($shop['name']); ?>
        </nav>

        <main class="detail-main">
            <!-- ヒーローセクション -->
            <section class="shop-hero">
                <div class="hero-content">
                    <h1 class="shop-name-title"><?php echo htmlspecialchars($shop['name']); ?></h1>
                    <div class="shop-tags">
                        <?php if (empty($feature_tags)): ?>
                            <span class="feature-tag">基本店舗</span>
                        <?php else: ?>
                            <?php foreach($feature_tags as $tag): ?>
                                <span class="feature-tag"><?php echo htmlspecialchars($tag); ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="prizeon-status-panel <?php echo $shop['has_prizeon'] ? 'status-active' : 'status-inactive'; ?>">
                    <div class="status-indicator"></div>
                    <div class="status-label">PrizeON SYSTEM</div>
                    <div class="status-value"><?php echo $shop['has_prizeon'] ? 'CONNECTED' : 'OFFLINE'; ?></div>
                </div>
            </section>

            <div class="detail-grid">
                <!-- 左側：店舗基本情報 -->
                <div class="info-column">
                    <section class="info-card">
                        <h2 class="section-title">STORE INFO</h2>
                        <div class="info-list">
                            <div class="info-item">
                                <span class="label">ADDRESS</span>
                                <p class="value">
                                    <?php echo !empty($shop['address']) ? htmlspecialchars($shop['address']) : '情報なし'; ?>
                                </p>
                            </div>
                            <div class="info-item">
                                <span class="label">HOURS</span>
                                <p class="value">
                                    <?php echo !empty($shop['business_hours']) ? htmlspecialchars($shop['business_hours']) : '情報なし'; ?>
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- SOCIAL / LINKS -->
                    <section class="info-card">
                        <h2 class="section-title">SOCIAL / LINKS</h2>
                        <div class="social-links">
                            <!-- 公式HPボタン -->
                            <?php if (!empty($shop['official_url'])): ?>
                                <a href="<?php echo htmlspecialchars($shop['official_url']); ?>" target="_blank" class="social-btn web">
                                    公式サイト
                                </a>
                            <?php else: ?>
                                <span class="social-btn web" style="opacity: 0.5; pointer-events: none;">HP情報なし</span>
                            <?php endif; ?>

                            <!-- X(Twitter)ボタン -->
                            <?php if (!empty($shop['twitter_id'])): ?>
                                <a href="https://x.com/<?php echo htmlspecialchars($shop['twitter_id']); ?>" target="_blank" class="social-btn tw">
                                    公式 X (Twitter)
                                </a>
                            <?php else: ?>
                                <span class="social-btn tw" style="opacity: 0.5; pointer-events: none;">SNS情報なし</span>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>

                <!-- 右側：マップ・サブ情報 -->
                <div class="map-column">
                    <div class="map-container">
                        <?php if (!empty($shop['map_url'])): ?>
            <?php 
                // DBにiframeタグごと保存されているか、URLだけかを判定して整形
                $src_url = $shop['map_url'];
                if (strpos($shop['map_url'], '<iframe') !== false) {
                    // タグからsrc属性の中身だけを抽出（念のためURLのみで扱う場合）
                    preg_match('/src="([^"]+)"/', $shop['map_url'], $match);
                    $src_url = $match[1] ?? '';
                }
            ?>
            <!-- 実際の地図を表示 -->
            <iframe 
                src="<?php echo htmlspecialchars($src_url); ?>" 
                width="100%" 
                height="300" 
                style="border:0; border-radius: 8px;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
            
            <!-- 別タブで開くボタンも残しておくと親切です -->
            <a href="<?php echo htmlspecialchars($src_url); ?>" target="_blank" class="map-link-btn" style="margin-top: 10px;">
                大きな地図で開く
            </a>
        <?php else: ?>
            <!-- データがない場合のフォールバック -->
            <div class="map-placeholder">
                <div class="map-grid-overlay"></div>
                <span class="map-text">MAP DATA NOT FOUND</span>
            </div>
            <div class="map-link-btn" style="background: #0f172a; color: #475569; pointer-events: none;">
                マップ情報なし
            </div>
        <?php endif; ?>
    </div>
    
    <div class="update-info">
        最終更新: <?php echo date('Y-m-d', strtotime($shop['updated_at'])); ?>[cite: 2]
    </div>
</div>

            <div class="action-footer">
                <a href="shop_list.php" class="back-btn">一覧に戻る</a>
            </div>
        </main>
    </div>
</body>
</html>