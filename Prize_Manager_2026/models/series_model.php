<?php
class SeriesModel {
    private $pdo;
    public function __construct($pdo) { $this->pdo = $pdo; }

    // メーカーごとに整理するためのデータをまとめて取得するメソッド
    public function getGroupedSeries() {
        $sql = "SELECT s.*, m.name as manufacturer_name 
                FROM series s
                LEFT JOIN manufacturers m ON s.manufacturer_id = m.id
                ORDER BY m.id ASC, s.id ASC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}