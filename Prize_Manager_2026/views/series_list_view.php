<?php
// 環境設定（BASE_URLなど）を読み込む
require_once __DIR__ . '/../config/env.php'; // パスはプロジェクトの構成に合わせて調整してください
// ヘッダー出力関数などが定義されているファイルを読み込む
require_once __DIR__ . '/../includes/header.php';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>シリーズ一覧 | Prize_Manager_2026</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/common.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/series_list.css">
</head>
<body class="bg-common">
    <?php
    // ヘッダーを出力
    output_header('series');
    ?>

    <div class="wrapper">
        <h1 class="page-title">シリーズ一覧</h1>
        <?php foreach ($grouped as $maker_name => $series_list): ?>
            <section class="maker-section">
                <h2><?php echo htmlspecialchars($maker_name); ?></h2>
                <div class="series-grid">
                    <?php foreach ($series_list as $series): ?>
                        <div class="series-card">
                            <?php if (!empty($series['OFFICIAL_URL'])): ?>
                                <a href="<?php echo htmlspecialchars($series['OFFICIAL_URL']); ?>" target="_blank" rel="noopener noreferrer" class="series-link">
                            <?php endif; ?>
                            <?php if (!empty($series['IMAGE'])): ?>
                                <img src="<?php echo htmlspecialchars($series['IMAGE']); ?>" alt="<?php echo htmlspecialchars($series['name']); ?>" class="series-img">
                            <?php else: ?>
                                <span class="series-name-fallback"><?php echo htmlspecialchars($series['name']); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($series['OFFICIAL_URL'])): ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
</body>
</html>