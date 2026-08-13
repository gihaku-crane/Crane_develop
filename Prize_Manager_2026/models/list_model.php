<?php
// models/list_model.php

/**
 * 景品一覧データを取得する関数
 */
function getAllPrizes($pdo) {
    // 既存の list.php にある SQL をここにコピーしてください
    // 例：
    $sql = "SELECT p.*, s.name AS series_name 
            FROM prizes p 
            LEFT JOIN series s ON p.series_id = s.id 
            ORDER BY p.arrival_date DESC";
    
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * 景品データの検索と取得を行う
 */
function getPrizesData($pdo, $params, $where_sql, $limit, $offset) {
    // prize_shops_flagテーブルを使用するように変更
    $base_sql = " FROM prizes p 
              LEFT JOIN titles t ON p.title_id = t.id 
              LEFT JOIN series s ON p.SERIES_ID = s.id 
              LEFT JOIN prize_shops_flag psf ON p.id = psf.prize_id"
            . $where_sql;

    // 全ヒット件数の算出
    $count_sql = "SELECT COUNT(DISTINCT p.id) " . $base_sql;
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_all_results = (int)$count_stmt->fetchColumn();

    // 景品データの取得
    // 各ショップのフラグ情報をそのまま取得する
    // psf.* を指定して店舗フラグ情報を取得するように変更
    $data_sql = "SELECT p.*, t.name as official_name, s.name as series_name, psf.* "
                . $base_sql 
                . " ORDER BY p.Arrival_date DESC, p.id DESC "
                . " LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($data_sql);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    $prizes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 各景品のフラグ（shop_1～12）を解析し、店舗名リスト（shop_short_names）を再構築する処理
    if (!empty($prizes)) {
        $stmt_shops = $pdo->query("SELECT id, short_name FROM shops ORDER BY priority ASC");
        $all_shops = $stmt_shops->fetchAll(PDO::FETCH_KEY_PAIR); // [1 => '店舗名1', 2 => '店舗名2', ...]

        foreach ($prizes as &$prize) {
            $short_names = [];
            // shop_1 から shop_12 までのフラグを順次判定
            for ($i = 1; $i <= 12; $i++) {
                if (!empty($prize['shop_' . $i]) && $prize['shop_' . $i] == 1) {
                    if (isset($all_shops[$i])) {
                        $short_names[] = $all_shops[$i];
                    }
                }
            }
            // 画面側（list_view.php）が参照している変数名に合わせる
            $prize['shop_short_names'] = !empty($short_names) ? implode(',', $short_names) : '';
        }
        unset($prize);
    }
    // 関数からデータを返すようにする
    return [
        'prizes' => $prizes,
        'total_count' => $total_all_results
    ];
}

// マスタデータ取得用関数
function getMasterData($pdo) {
    return [
        'titles' => $pdo->query("SELECT * FROM titles ORDER BY kana_index ASC, name ASC")->fetchAll(),
        'series' => $pdo->query("SELECT * FROM series ORDER BY name ASC")->fetchAll(),
        'shops'  => $pdo->query("SELECT * FROM shops ORDER BY priority ASC")->fetchAll()
    ];
}