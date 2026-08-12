<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Prize_Manager_2026/models/series_model.php';

class SeriesController {
    private $model;
    public function __construct($pdo) { $this->model = new SeriesModel($pdo); }

    public function index() {
        // メソッド名を getGroupedSeries() に合わせます
        $all_data = $this->model->getGroupedSeries();
        
        $grouped = [];
        foreach ($all_data as $row) {
            $maker = $row['manufacturer_name'] ?? '未分類';
            $grouped[$maker][] = $row;
        }
        
        require_once __DIR__ . '/../views/series_list_view.php';
    }
}