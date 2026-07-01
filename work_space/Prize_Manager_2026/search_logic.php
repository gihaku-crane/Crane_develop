<?php
// ==========================================
// search_logic.php (リクエスト優先判定・完全不整合バグ防止版)
// ==========================================
/*
ファイル名: search_logic.php
役割: 
  - URLの検索パラメータを受け取り、SQLの条件文を組み立てる。
  - カレンダーから直接送信される値（arrival_date_start / end）と、
    本日ボタンや通常のプルダウンから送信される値（s_year等）の優先度・同期を厳密に行います。
*/

// --- 1. カレンダー操作によるリクエスト値の取得 ---
$arrival_date_start = $_GET['arrival_date_start'] ?? '';
$arrival_date_end   = $_GET['arrival_date_end'] ?? '';

// --- 2. 検索パラメータの取得 ---
$keyword        = isset($_GET['keyword']) ? trim(mb_convert_kana($_GET['keyword'], "s", "UTF-8")) : '';
$status         = $_GET['status'] ?? ''; 
$title_filter   = $_GET['title'] ?? '';
$shop_filter    = $_GET['shop'] ?? '';
$series_id      = $_GET['series_id'] ?? '';

// プルダウンセレクト値の初期受け取り
$s_y = $_GET['s_year'] ?? ''; 
$s_m = $_GET['s_month'] ?? ''; 
$s_d = $_GET['s_day'] ?? '';

$e_y = $_GET['e_year'] ?? ''; 
$e_m = $_GET['e_month'] ?? ''; 
$e_d = $_GET['e_day'] ?? '';

// 【重要：優先度判定ロジック】
// カレンダー（arrival_date_start / end）から直接値が飛んできている場合は、
// セレクトボックス側の値をカレンダーの日付で強制上書きして不整合を防止します。
if (!empty($arrival_date_start)) {
    $date_parts_s = explode('-', $arrival_date_start);
    if (count($date_parts_s) === 3) {
        $s_y = $date_parts_s[0];
        $s_m = $date_parts_s[1];
        $s_d = $date_parts_s[2];
    }
} else {
    // 逆に、カレンダーが空でセレクト側が入っている場合は、カレンダー用変数に日付を再合成して同期
    if ($s_y !== '' && $s_m !== '' && $s_d !== '') {
        $arrival_date_start = "{$s_y}-{$s_m}-{$s_d}";
    }
}

if (!empty($arrival_date_end)) {
    $date_parts_e = explode('-', $arrival_date_end);
    if (count($date_parts_e) === 3) {
        $e_y = $date_parts_e[0];
        $e_m = $date_parts_e[1];
        $e_d = $date_parts_e[2];
    }
} else {
    if ($e_y !== '' && $e_m !== '' && $e_d !== '') {
        $arrival_date_end = "{$e_y}-{$e_m}-{$e_d}";
    }
}

// ページング設定
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// フォーム側の再同期・状態保持用
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

// --- 3. SQLの組み立て ---
$where_clauses = [];
$params = [];

// ① キーワード検索
if ($keyword !== '') {
    $where_clauses[] = "p.name LIKE :keyword";
    $params[':keyword'] = '%' . $keyword . '%';
}

// ② 作品タイトル検索
if ($title_filter !== '') {
    $where_clauses[] = "p.title_id = :title_id";
    $params[':title_id'] = $title_filter;
}

// ③ シリーズ検索
if (!empty($series_id)) {
    $where_clauses[] = "p.SERIES_ID = :series_id";
    $params[':series_id'] = $series_id;
}

// ④ 入荷期間検索 (合成された優先日付で検索)
$final_start = ($s_y !== '') ? "{$s_y}-".(($s_m !== '') ? $s_m : '01')."-".(($s_d !== '') ? $s_d : '01') : null;
$final_end = null;
if ($e_y !== '') {
    if ($e_m === '') {
        $final_end = "{$e_y}-12-31";
    } elseif ($e_d === '') {
        $final_end = date("Y-m-t", strtotime("{$e_y}-{$e_m}-01"));
    } else {
        $final_end = "{$e_y}-{$e_m}-{$e_d}";
    }
}

if ($final_start && $final_end) {
    $where_clauses[] = "p.Arrival_date BETWEEN :start AND :end";
    $params[':start'] = $final_start; 
    $params[':end'] = $final_end;
} elseif ($final_start) {
    $where_clauses[] = "p.Arrival_date >= :start";
    $params[':start'] = $final_start;
} elseif ($final_end) {
    $where_clauses[] = "p.Arrival_date <= :end";
    $params[':end'] = $final_end;
}

// ⑤ 店舗検索
if ($shop_filter !== '') {
    $where_clauses[] = "EXISTS (SELECT 1 FROM prize_shops ps2 WHERE ps2.prize_id = p.id AND ps2.shop_id = :shop_id)";
    $params[':shop_id'] = $shop_filter;
}

// ⑥ 獲得ステータス
if ($status === 'got') {
    $where_clauses[] = "p.got_status = 'got'";
} elseif ($status === 'un') {
    $where_clauses[] = "(p.got_status = 'un' OR p.got_status IS NULL OR p.got_status = '')";
}

$where_sql = !empty($where_clauses) ? " WHERE " . implode(" AND ", $where_clauses) : "";

$base_sql = " FROM prizes p 
              LEFT JOIN titles t ON p.title_id = t.id 
              LEFT JOIN series s ON p.SERIES_ID = s.id 
              LEFT JOIN prize_shops ps ON p.id = ps.prize_id
              LEFT JOIN shops sh ON ps.shop_id = sh.id" 
            . $where_sql;

try {
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

} catch (PDOException $e) {
    echo "<div style='color:red; background:#fff; padding:10px; border:3px solid red;'>SQLエラーが発生しています：<br>" . htmlspecialchars($e->getMessage()) . "</div>";
}
?>