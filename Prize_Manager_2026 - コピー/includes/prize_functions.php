<?php

/**
 * シリーズ一覧を取得する
 */
function getSeriesList($pdo) {
    $stmt = $pdo->query("SELECT id, name FROM series ORDER BY name ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * 店舗一覧を取得する
 */
function getShopList($pdo) {
    $stmt = $pdo->query("SELECT id, name FROM shops ORDER BY name ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}