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
    // search_logicから移植したSQL組み立て・実行ロジックをここに書く
    $base_sql = " FROM prizes p 
              LEFT JOIN titles t ON p.title_id = t.id 
              LEFT JOIN series s ON p.SERIES_ID = s.id 
              LEFT JOIN prize_shops ps ON p.id = ps.prize_id
              LEFT JOIN shops sh ON ps.shop_id = sh.id" 
            . $where_sql;

    // 全ヒット件数の算出
    $count_sql = "SELECT COUNT(DISTINCT p.id) " . $base_sql;
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_all_results = (int)$count_stmt->fetchColumn();

    // 景品データの取得（入荷予定日の降順、かつIDの降順）
    $data_sql = "SELECT p.*, t.name as official_name, s.name as series_name,
                 GROUP_CONCAT(DISTINCT sh.short_name ORDER BY sh.priority ASC SEPARATOR ',') AS shop_short_names "
                . $base_sql 
                . " GROUP BY p.id "
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

    // プルダウン等のマスタ表示データ
    $all_titles = $pdo->query("SELECT * FROM titles ORDER BY kana_index ASC, name ASC")->fetchAll();
    $all_series = $pdo->query("SELECT * FROM series ORDER BY name ASC")->fetchAll();
    $all_shops  = $pdo->query("SELECT * FROM shops ORDER BY priority ASC")->fetchAll();

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