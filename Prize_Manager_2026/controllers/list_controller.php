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

// 個別日付指定用変数
//開始日
$s_y = $_GET['s_year'] ?? '';
$s_m = $_GET['s_month'] ?? '';
$s_d = $_GET['s_day'] ?? '';
//終了日
$e_y = $_GET['e_year'] ?? '';
$e_m = $_GET['e_month'] ?? '';
$e_d = $_GET['e_day'] ?? '';

// --- 2. 優先度判定ロジック (そのまま) ---
// YYYY-MM-DD形式のパラメータがある場合は優先的に分解し、ない場合は個別の年月日指定を結合して日付文字列を作成
if (!empty($arrival_date_start)) {
    $date_parts_s = explode('-', $arrival_date_start);
    if (count($date_parts_s) === 3) {
        list($s_y, $s_m, $s_d) = $date_parts_s;
    }
} else {
    if ($s_y !== '' && $s_m !== '' && $s_d !== '') {
        $arrival_date_start = "{$s_y}-{$s_m}-{$s_d}";
    }
}
if (!empty($arrival_date_end)) {
    $date_parts_e = explode('-', $arrival_date_end);
    if (count($date_parts_e) === 3) {
        list($e_y, $e_m, $e_d) = $date_parts_e;
    }
} else {
    if ($e_y !== '' && $e_m !== '' && $e_d !== '') {
        $arrival_date_end = "{$e_y}-{$e_m}-{$e_d}";
    }
}

// ページング設定：デフォルト値は1ページあたり20件、1ページ目から開始
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page < 1) ? 0 : ($page - 1) * $limit;

// フォーム側の再同期・状態保持用配列：検索結果表示後も入力内容を維持するために使用
$search_values = [
    'keyword'            => $keyword,
    'status'             => $status,
    'title'              => $title_filter,
    'shop'               => $shop_filter,
    'series_id'          => $series_id,
    's_year'             => $s_y, 
    's_month'            => $s_m, 
    's_day'              => $s_d,
    'e_year'             => $e_y, 
    'e_month'            => $e_m, 
    'e_day'              => $e_d,
    'arrival_date_start' => $arrival_date_start,
    'arrival_date_end'   => $arrival_date_end
];


// --- 3. SQLの条件組み立て ---
$where_clauses = [];
$params = [];

// 商品名の部分一致検索用条件の構築
if ($keyword !== '') {
    $where_clauses[] = "p.name LIKE :keyword";
    $params[':keyword'] = '%' . $keyword . '%';
}

// 日付範囲指定検索用条件の構築（BETWEENまたは各境界値による絞り込み）
if (!empty($arrival_date_start) && !empty($arrival_date_end)) {
    $where_clauses[] = "p.Arrival_date BETWEEN :start AND :end";
    $params[':start'] = $arrival_date_start;
    $params[':end']   = $arrival_date_end;
} elseif (!empty($arrival_date_start)) {
    $where_clauses[] = "p.Arrival_date >= :start";
    $params[':start'] = $arrival_date_start;
} elseif (!empty($arrival_date_end)) {
    $where_clauses[] = "p.Arrival_date <= :end";
    $params[':end']   = $arrival_date_end;
}

// ID指定による厳密なフィルタリング条件の追加
if ($title_filter !== '') {
    $where_clauses[] = "p.title_id = :title_id"; $params[':title_id'] = $title_filter;
}

if (!empty($series_id)) {
    $where_clauses[] = "p.SERIES_ID = :series_id"; $params[':series_id'] = $series_id;
}

// 店舗検索：関連テーブル（prize_shops）を参照し、指定店舗で取り扱いがある商品のみを抽出
if ($shop_filter !== '') {
    $where_clauses[] = "EXISTS (SELECT 1 FROM prize_shops ps2 WHERE ps2.prize_id = p.id AND ps2.shop_id = :shop_id)";
     $params[':shop_id'] = $shop_filter;
 }

// 取得状況によるフィルタリング
if ($status === 'got') {
    $where_clauses[] = "p.got_status = 'got'";
} elseif ($status === 'un') {
    $where_clauses[] = "(p.got_status = 'un' OR p.got_status IS NULL OR p.got_status = '')";
}

// お気に入り登録状況によるフィルタリング
$favorite_filter = $_GET['favorite'] ?? '';

if ($favorite_filter !== '') {
    // 1(登録済)か0(未登録)で絞り込む
    $where_clauses[] = "p.is_favorite = :favorite";
    $params[':favorite'] = $favorite_filter;
}

// WHERE句の生成：条件がある場合のみ文字列を結合
$where_sql = !empty($where_clauses) ? " WHERE " . implode(" AND ", $where_clauses) : "";

// --- 4. モデル呼び出し ---
// 組み立てたSQL条件とパラメータを使用してDBから商品データを取得
$result = getPrizesData($pdo, $params, $where_sql, $limit, $offset);
$prizes = $result['prizes'];
$total_all_results = $result['total_count'];

// 検索フォーム用のセレクトボックス等に必要なマスターデータを取得
$masterData = getMasterData($pdo);
$all_titles = $masterData['titles'];
$all_series = $masterData['series'];
$all_shops  = $masterData['shops'];

// ページャー計算用変数
$current_page = $page;
$t_count = $total_all_results;
$t_limit = $limit;