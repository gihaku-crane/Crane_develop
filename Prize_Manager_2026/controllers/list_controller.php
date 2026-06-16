<?php
// controllers/list_controller.php
require_once __DIR__ . '/../models/list_model.php';

// --- 1. パラメータ受け取りと整理 (ここまではOK) ---
$arrival_date_start = $_GET['arrival_date_start'] ?? '';
$arrival_date_end   = $_GET['arrival_date_end'] ?? '';
$keyword            = isset($_GET['keyword']) ? trim(mb_convert_kana($_GET['keyword'], "s", "UTF-8")) : '';
$status             = $_GET['status'] ?? ''; 
$title_filter       = $_GET['title'] ?? '';
$shop_filter        = $_GET['shop'] ?? '';
$series_id          = $_GET['series_id'] ?? '';
$s_y = $_GET['s_year'] ?? ''; $s_m = $_GET['s_month'] ?? ''; $s_d = $_GET['s_day'] ?? '';
$e_y = $_GET['e_year'] ?? ''; $e_m = $_GET['e_month'] ?? ''; $e_d = $_GET['e_day'] ?? '';

// --- 2. 優先度判定ロジック (そのまま) ---
if (!empty($arrival_date_start)) {
    $date_parts_s = explode('-', $arrival_date_start);
    if (count($date_parts_s) === 3) { list($s_y, $s_m, $s_d) = $date_parts_s; }
} else {
    if ($s_y !== '' && $s_m !== '' && $s_d !== '') { $arrival_date_start = "{$s_y}-{$s_m}-{$s_d}"; }
}
if (!empty($arrival_date_end)) {
    $date_parts_e = explode('-', $arrival_date_end);
    if (count($date_parts_e) === 3) { list($e_y, $e_m, $e_d) = $date_parts_e; }
} else {
    if ($e_y !== '' && $e_m !== '' && $e_d !== '') { $arrival_date_end = "{$e_y}-{$e_m}-{$e_d}"; }
}

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page < 1) ? 0 : ($page - 1) * $limit;

// --- 3. SQLの条件組み立て ---
$where_clauses = [];
$params = [];
if ($keyword !== '') { $where_clauses[] = "p.name LIKE :keyword"; $params[':keyword'] = '%' . $keyword . '%'; }
if ($title_filter !== '') { $where_clauses[] = "p.title_id = :title_id"; $params[':title_id'] = $title_filter; }
if (!empty($series_id)) { $where_clauses[] = "p.SERIES_ID = :series_id"; $params[':series_id'] = $series_id; }
// ... (日付などのWHERE条件組み立てはここ) ...
if ($shop_filter !== '') { $where_clauses[] = "EXISTS (SELECT 1 FROM prize_shops ps2 WHERE ps2.prize_id = p.id AND ps2.shop_id = :shop_id)"; $params[':shop_id'] = $shop_filter; }
if ($status === 'got') { $where_clauses[] = "p.got_status = 'got'"; } 
elseif ($status === 'un') { $where_clauses[] = "(p.got_status = 'un' OR p.got_status IS NULL OR p.got_status = '')"; }

$where_sql = !empty($where_clauses) ? " WHERE " . implode(" AND ", $where_clauses) : "";

// --- 4. モデル呼び出し (ここが最重要) ---
$result = getPrizesData($pdo, $params, $where_sql, $limit, $offset);
$prizes = $result['prizes'];
$total_all_results = $result['total_count'];

$masterData = getMasterData($pdo);
$all_titles = $masterData['titles'];
$all_series = $masterData['series'];
$all_shops  = $masterData['shops'];