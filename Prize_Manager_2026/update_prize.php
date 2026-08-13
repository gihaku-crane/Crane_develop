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
            // ① まず、対象の景品IDのフラグレコードが存在するか確認（なければ作る、またはUPSERT）
            // すべてのショップフラグ（shop_1 〜 shop_12）を一旦 0 にリセットするための配列を準備
            $flag_data = [];
            for ($i = 1; $i <= 12; $i++) {
                $flag_data['shop_' . $i] = 0;
            }

            // 送信されてきたチェック済みショップIDのフラグを 1 に立てる
            // $_POST['shop_ids'] には選択された店舗のID（1〜12）が入っている想定
            foreach ($_POST['shop_ids'] as $shop_id) {
                $shop_id = (int)$shop_id;
                if ($shop_id >= 1 && $shop_id <= 12) {
                    $flag_data['shop_' . $shop_id] = 1;
                }
            }

            // ② レコードが存在するかチェック
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM prize_shops_flag WHERE prize_id = ?");
            $check_stmt->execute([$prize_id]);
            $exists = $check_stmt->fetchColumn();

            if ($exists) {
                // 既存レコードがある場合は UPDATE
                $set_clauses = [];
                $params_flag = [':prize_id' => $prize_id];
                for ($i = 1; $i <= 12; $i++) {
                    $set_clauses[] = "shop_$i = :shop_$i";
                    $params_flag[":shop_$i"] = $flag_data['shop_' . $i];
                }
                $sql_update = "UPDATE prize_shops_flag SET " . implode(", ", $set_clauses) . " WHERE prize_id = :prize_id";
                $pdo->prepare($sql_update)->execute($params_flag);
            } else {
                // レコードがない場合は INSERT
                $cols = ['prize_id'];
                $vals = [':prize_id'];
                $params_flag = [':prize_id' => $prize_id];
                for ($i = 1; $i <= 12; $i++) {
                    $cols[] = "shop_$i";
                    $vals[] = ":shop_$i";
                    $params_flag[":shop_$i"] = $flag_data['shop_' . $i];
                }
                $sql_insert = "INSERT INTO prize_shops_flag (" . implode(", ", $cols) . ") VALUES (" . implode(", ", $vals) . ")";
                $pdo->prepare($sql_insert)->execute($params_flag);
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