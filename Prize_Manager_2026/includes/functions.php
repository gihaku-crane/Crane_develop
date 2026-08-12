<?php
// includes/functions.php

/**
 * ソートリンクのURLと矢印表示を生成する
 */
function getSortLink($column, $current_sort, $current_order) {
    $next_order = ($current_sort === $column && $current_order === 'ASC') ? 'DESC' : 'ASC';
    return "?sort={$column}&order={$next_order}";
}

/**
 * ソート状態に応じた矢印を返す
 */
function getSortArrow($column, $current_sort, $current_order) {
    if ($current_sort !== $column) return '';
    return ($current_order === 'ASC') ? ' ▲' : ' ▼';
}


/**
 * ヘルパー関数：景品リストに店舗名を付加する
 */
function attachShopNames($pdo, $prizes) {
    if (empty($prizes)) return [];
    foreach ($prizes as &$prize) {
        $stmt = $pdo->prepare("
            SELECT s.name 
            FROM shops s
            JOIN prize_shops ps ON s.id = ps.shop_id
            WHERE ps.prize_id = ?
        ");
        $stmt->execute([$prize['id']]);
        $shops = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $prize['linked_shop_names'] = !empty($shops) ? implode(' / ', $shops) : '未設定';
    }
    return $prizes;
}