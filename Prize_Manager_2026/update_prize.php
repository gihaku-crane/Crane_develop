<?php
// 1. 設定とエラーハンドラーの読み込み
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/app_errors.php';

try {
    // 必須パラメータのチェック
    $prize_id = $_POST['id'] ?? null;
    if (!$prize_id) {
        throw new Exception("更新対象の景品IDが送信されていません。");
    }

    $pdo->beginTransaction();

    // 1. 基本情報の更新
    $sql = "UPDATE prizes SET 
                name = ?, 
                series_id = ?, 
                title = ?, 
                prize_size = ?, 
                arrival_date = ?, 
                gravity_pred = ?, 
                gravity_actual = ? 
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['name'] ?? '', 
        $_POST['series_id'] ?? null, 
        $_POST['title'] ?? '', 
        $_POST['prize_size'] ?? '', 
        $_POST['arrival_date'] ?? null, 
        $_POST['gravity_pred'] ?? '', 
        $_POST['gravity_actual'] ?? '', 
        $prize_id
    ]);

    // 2. 店舗情報の更新（全削除して再挿入）
    $pdo->prepare("DELETE FROM prize_shops WHERE prize_id = ?")->execute([$prize_id]);
    if (!empty($_POST['shop_ids'])) {
        $stmt_shop = $pdo->prepare("INSERT INTO prize_shops (prize_id, shop_id) VALUES (?, ?)");
        foreach ($_POST['shop_ids'] as $shop_id) {
            $stmt_shop->execute([$prize_id, $shop_id]);
        }
    }

    // 3. ギャラリー画像処理
    if (!empty($_FILES['gallery_images']['tmp_name'])) {
        $image_columns = ['image_url1', 'image_url2', 'image_url3', 'image_url4'];
        
        foreach ($_FILES['gallery_images']['tmp_name'] as $index => $tmpName) {
            if ($index >= 4) break; 
            
            $save_value = null;
            if (!empty($tmpName) && is_uploaded_file($tmpName)) {
                $fileName = 'gallery_' . $prize_id . '_' . time() . '_' . $index . '.jpg';
                if (move_uploaded_file($tmpName, ROOT_PATH . '/uploads/' . $fileName)) {
                    $save_value = $fileName;
                }
            } elseif (!empty($_POST['gallery_urls'][$index])) {
                $save_value = $_POST['gallery_urls'][$index];
            }

            if ($save_value) {
                $col = $image_columns[$index];
                $stmt = $pdo->prepare("UPDATE prizes SET $col = ? WHERE id = ?");
                $stmt->execute([$save_value, $prize_id]);
            }
        }
    }

    $pdo->commit();

    // 成功時：詳細画面へ戻る
    header("Location: " . BASE_URL . "/item_detail.php?id=" . $prize_id);
    exit;

} catch (Exception $e) {
    // 失敗時：DBロールバックとエラー通知
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // 共通エラー処理（ログ出力 + リダイレクト）
    handleActionError($e, "景品情報の更新中にエラーが発生しました。");
}