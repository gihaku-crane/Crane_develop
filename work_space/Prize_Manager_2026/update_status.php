<?php
// PHPファイルの先頭にはスペースや改行を一切入れないこと！
require_once __DIR__ . '/includes/config.php';
require_once 'includes/db_connect.php';

$id     = $_REQUEST['id'] ?? null;
$status = $_GET['status'] ?? null;
$type   = $_POST['type'] ?? null;
$val    = $_POST['val'] ?? null;

// JSONレスポンス専用にするため、ここでバッファリングを開始
ob_start();

try {
    if (!$id) {
        throw new Exception('ID missing');
    }

    $success = false;
    
    // DB更新処理 ... (前回のコードのまま)
    if ($type) {
        $allowed = ['got_status', 'is_favorite'];
        if (in_array($type, $allowed)) {
            if ($type === 'got_status') {
                $val = ($val === '1' || $val === 'got') ? 'got' : 'un';
                $get_date = ($val === 'got') ? date('Y-m-d') : null;
                $stmt = $pdo->prepare("UPDATE prizes SET got_status = ?, get_date = ? WHERE id = ?");
                $success = $stmt->execute([$val, $get_date, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE prizes SET $type = ? WHERE id = ?");
                $success = $stmt->execute([$val, $id]);
            }
        }
    } elseif ($status !== null) {
        $stmt = $pdo->prepare("UPDATE prizes SET got_status = ? WHERE id = ?");
        $success = $stmt->execute([$status, $id]);
    }

    // バッファをクリアしてJSONだけを出力
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => $success]);

} catch (Exception $e) {
    ob_end_clean();
    header('Content-Type: application/json', true, 500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
exit;