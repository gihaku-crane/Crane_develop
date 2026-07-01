<?php
// models/detail_model.php

/**
 * 指定したIDの景品詳細情報を取得する
 * 関連するシリーズ名やショップ一覧も結合して取得
 */
function getPrizeById($pdo, $id) {
    $sql = "SELECT p.*, s.name as series_name, 
                   GROUP_CONCAT(sh.name SEPARATOR '<br>') as shop_list
            FROM prizes p
            LEFT JOIN series s ON p.series_id = s.id
            LEFT JOIN prize_shops ps ON p.id = ps.prize_id
            LEFT JOIN shops sh ON ps.shop_id = sh.id
            WHERE p.id = ? GROUP BY p.id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
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