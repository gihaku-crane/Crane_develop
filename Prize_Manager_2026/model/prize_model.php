<?php
/**
 * model/prize_model.php
 * 景品データ取得用モデル
 */

/**
 * ソート条件を考慮して景品一覧を取得する関数
 */
function getPrizes($pdo, $sort, $order, $limit = 20, $offset = 0) {
    // 許可するソート対象カラムのみ指定（ここに外部結合先のカラムは含めない）
    $allowed_sorts = ['name', 'arrival_date', 'got_status'];
    $sort = in_array($sort, $allowed_sorts) ? $sort : 'arrival_date';
    
    // 2. 順序の固定
    $order = (strtoupper($order) === 'ASC') ? 'ASC' : 'DESC';

    // SQLはシンプルに「p.カラム名」で指定できるようにする
    $sql = "SELECT p.*, t.name as official_name, s.name as series_name,
            GROUP_CONCAT(DISTINCT sh.short_name ORDER BY sh.priority ASC SEPARATOR ',') AS shop_short_names
            FROM prizes p 
            LEFT JOIN titles t ON p.title_id = t.id 
            LEFT JOIN series s ON p.series_id = s.id 
            LEFT JOIN prize_shops ps ON p.id = ps.prize_id
            LEFT JOIN shops sh ON ps.shop_id = sh.id
            GROUP BY p.id
            ORDER BY p.$sort $order 
            LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}