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

    // 1. 全店舗の情報を取得（店舗IDと店舗名のマッピング）
    $stmt = $pdo->query("SELECT id, name FROM shops");
    $shop_map = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // [1 => 'モアーズ', 2 => 'ダイス', ...]

    foreach ($prizes as &$prize) {
        // 2. 新しいフラグテーブルからこの景品の店舗フラグを取得
        $stmt = $pdo->prepare("SELECT * FROM prize_shops_flag WHERE prize_id = ?");
        $stmt->execute([$prize['id']]);
        $flags = $stmt->fetch(PDO::FETCH_ASSOC);

        $linked_shops = [];
        if ($flags) {
            // 3. 12店舗分をループしてフラグをチェック
            for ($i = 1; $i <= 12; $i++) {
                if ($flags['shop_' . $i] == 1) {
                    $linked_shops[] = $shop_map[$i] ?? '不明';
                }
            }
        }
        $prize['linked_shop_names'] = !empty($linked_shops) ? implode(', ', $linked_shops) : '未設定';
    }
    return $prizes;
}