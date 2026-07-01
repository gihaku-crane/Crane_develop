<?php
session_start();

require_once __DIR__ . '/includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/prize_functions.php';

// セッションから前回の入力を取得（なければ空配列）
$old = isset($_SESSION['old_input']) ? $_SESSION['old_input'] : [];
// 表示後に消去（リロードで消えるように）
unset($_SESSION['old_input']);

$series_list = getSeriesList($pdo);
$shop_list   = getShopList($pdo);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>景品追加</title>
    <link rel="stylesheet" href="css/common.css">
    <link rel="stylesheet" href="css/add_prize.css">
    
</head>
<body>
    <div class="app-container">
        <div class="form-container">
            <a href="list.php" class="back-link">← 景品一覧に戻る</a>
            <h1>景品追加</h1>

            <?php if (!empty($_SESSION['errors'])): ?>
                <div class="error-container">
                    <?php foreach ($_SESSION['errors'] as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>

            <form action="actions/save_prize.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="required">景品名</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>">
                    <span class="error-msg" id="name-error"></span>
                </div>

                <div class="form-group">
                    <label class="required">シリーズ名</label>
                    <select name="series_id" id="series_id">
                        <option value="">シリーズを選択してください</option>
                        <?php foreach ($series_list as $s): ?>
                            <option value="<?= $s['id'] ?>" <?= ($old['series_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="error-msg" id="series_id-error"></span>
                </div>

                <div class="form-group">
                    <label class="required">作品タイトル名</label>
                    <input type="text" name="title" id="title" value="<?= htmlspecialchars($old['title'] ?? '') ?>">
                    <div id="new-title-fields" style="display: none;"></div>
                    <input type="hidden" id="new-title-display" name="new_title_name">
                </div>

                <div class="form-group">
                    <label>サイズ</label>
                    <input type="text" name="size" id="size" value="<?= htmlspecialchars($old['size'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>アソート</label>
                    <input type="text" name="assort" value="<?= htmlspecialchars($old['assort'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>入荷予定日</label>
                    <input type="date" name="arrival_date">
                </div>

                <div class="form-group">
                    <label>リリース年月（自動入力）</label>
                    <input type="text" name="release_month" id="release_month" placeholder="例: 2026-06">
                </div>

                <div class="form-group">
                    <label>メモ</label>
                    <textarea name="memo" rows="3" style="width:100%; background:#222; color:#fff; border:1px solid #444;"></textarea>
                </div>

                <div class="form-group">
                    <label>入荷店舗</label>
                    <div id="shopTags" class="shop-tags-container">
                    </div>

                    <div class="shop-add-wrapper" style="display: flex; gap: 10px; margin-top: 5px;">
                        <select id="shopSelect" class="shop-select">
                            <option value="">店舗を選択...</option>
                            <?php foreach ($shop_list as $shop): ?>
                                <option value="<?= htmlspecialchars($shop['id']) ?>"><?= htmlspecialchars($shop['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" id="addShopBtn" class="btn-add">追加</button>
                    </div>
                </div>

                <div class="form-group">
                    <label>重心予測</label>
                    <input type="text" name="gravity_prediction">
                </div>

                <div class="form-group">
                    <label>実際の重心</label>
                    <input type="text" name="actual_gravity">
                </div>

                <div class="form-group">
                    <label class="required">メイン画像</label>
                    <input type="file" name="main_image" accept="image/*">
                    <input type="text" name="main_image_url" placeholder="または画像URLを入力">
                </div>

                <div class="form-group" id="gallery-container">
                    <label>ギャラリー画像</label>
                    <div class="gallery-input-row">
                        <input type="file" name="gallery_images[]" accept="image/*">
                        <input type="text" name="gallery_urls[]" placeholder="または画像URLを入力">
                    </div>
                </div>

                <button type="button" id="add-gallery-btn" onclick="addGalleryInput()">
                    ＋ ギャラリー画像を追加
                </button>
                <button type="submit">登録する</button>
            </form>
        </div>
    </div>
    <script src="js/add_prize.js"></script>
</body>
</html>