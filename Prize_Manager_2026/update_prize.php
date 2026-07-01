<?php
// 1. 設定とエラーハンドラーの読み込み
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/app_errors.php';

//
if (isset($_POST['is_modal_update'])) {
    header('Content-Type: application/json');

try {
        $pdo->beginTransaction();
        $prize_id = $_POST['id'];

        // 1. 基本情報の更新（受け取った項目だけ更新）
        $updates = [];
        $params = [':id' => $prize_id];
        $fields = ['name', 'series_id', 'title', 'prize_size', 'arrival_date', 'gravity_pred', 'gravity_actual'];
        
        foreach ($fields as $field) {
            if (isset($_POST[$field]) && $_POST[$field] !== '') {
                $updates[] = "$field = :$field";
                $params[":$field"] = $_POST[$field];
            }
        }
        
        if (!empty($updates)) {
            $sql = "UPDATE prizes SET " . implode(", ", $updates) . " WHERE id = :id";
            $pdo->prepare($sql)->execute($params);
        }

        // 2. 店舗情報の更新（POSTデータにある場合のみ実行）
        if (isset($_POST['shop_ids'])) {
            $pdo->prepare("DELETE FROM prize_shops WHERE prize_id = ?")->execute([$prize_id]);
            $stmt_shop = $pdo->prepare("INSERT INTO prize_shops (prize_id, shop_id) VALUES (?, ?)");
            foreach ($_POST['shop_ids'] as $shop_id) {
                $stmt_shop->execute([$prize_id, $shop_id]);
            }
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => '更新しました']);
    } catch (Exception $e) {
        if (isset($pdo)) $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit; // これにより既存処理には一切影響しません
}