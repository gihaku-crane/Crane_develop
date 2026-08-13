<?php
// models/detail_model.php

/**
 * 指定したIDの景品詳細情報を取得する
 * 関連するシリーズ名やショップ一覧も結合して取得
 */
function getPrizeById($pdo, $id) {
    // JOIN先を prize_shops_flag (psf) に変更
    $sql = "SELECT p.*, s.name as series_name, psf.*
            FROM prizes p
            LEFT JOIN series s ON p.series_id = s.id
            LEFT JOIN prize_shops_flag psf ON p.id = psf.prize_id
            WHERE p.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $prize = $stmt->fetch(PDO::FETCH_ASSOC);

    // ここでショップ名を組み立てる
    if ($prize) {
        // 店舗マスタを優先度順で取得
        $stmt_shops = $pdo->query("SELECT id, name FROM shops ORDER BY priority ASC");
        $all_shops = $stmt_shops->fetchAll(PDO::FETCH_KEY_PAIR);

        $shop_names = [];
        // shop_1 から shop_12 までのフラグをチェック
        for ($i = 1; $i <= 12; $i++) {
            if (!empty($prize['shop_' . $i]) && $prize['shop_' . $i] == 1) {
                if (isset($all_shops[$i])) {
                    $shop_names[] = $all_shops[$i];
                }
            }
        }
        // 表示テンプレート側が期待する <br> 区切りの文字列を作成
        $prize['shop_list'] = !empty($shop_names) ? implode('<br>', $shop_names) : '';
    }

    return $prize;
}

/**
 * 同じタイトルの関連景品を取得する
 * 引数のIDを持つ景品自身は除外される
 */
function getRelatedPrizes($pdo, $title_id, $exclude_id) {
    $stmt = $pdo->prepare("SELECT id, name, image_url FROM prizes WHERE title_id = ? AND id != ? ORDER BY Arrival_date DESC LIMIT 4");
    $stmt->execute([$title_id, $exclude_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * ショップ一覧を名前順で取得する
 */
function getAllShops($pdo) {
    return $pdo->query("SELECT id, name FROM shops ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * シリーズ一覧を名前順で取得する
 */
function getAllSeries($pdo) {
    return $pdo->query("SELECT id, name FROM series ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
}