<?php
session_start();

// 1. 必要なファイルの読み込み
// config.phpで BASE_URL と ROOT_PATH を定義済みと想定
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/app_errors.php';

// POSTリクエスト以外は一覧へ戻す
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/list.php");
    exit;
}

// エラー収集用
$errors = [];
if (empty(trim($_POST['name']))) $errors[] = "景品名は必須です。";
if (empty($_POST['series_id'])) $errors[] = "シリーズを選択してください。";
if (empty(trim($_POST['title']))) $errors[] = "作品タイトルを入力してください。";

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['old_input'] = $_POST;
    header("Location: " . BASE_URL . "/add_prize.php");
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. 作品タイトル処理：既存検索または新規作成
    $stmt = $pdo->prepare("SELECT id FROM titles WHERE name = ?");
    $stmt->execute([$_POST['title']]);
    $title_id = $stmt->fetchColumn();

    if (!$title_id) {
        $stmt = $pdo->prepare("INSERT INTO titles (name, search_alias, kana_index) VALUES (?, ?, ?)");
        $stmt->execute([
            $_POST['title'], 
            $_POST['new_title_abbr'] ?? '', 
            $_POST['new_title_index'] ?? ''
        ]);
        $title_id = $pdo->lastInsertId();
    }

    // 2. 景品本体の登録
    $sql = "INSERT INTO prizes (name, series_id, title_id, title, prize_size, arrival_date, gravity_pred, gravity_actual, image_url, assort, memo, release_month) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['name'],
        $_POST['series_id'],
        $title_id,
        $_POST['title'],
        $_POST['size'] ?? null,
        !empty($_POST['arrival_date']) ? $_POST['arrival_date'] : null,
        $_POST['gravity_prediction'] ?? null,
        $_POST['actual_gravity'] ?? null,
        $_POST['main_image_url'] ?? null,
        $_POST['assort'] ?? null,
        $_POST['memo'] ?? null,
        $_POST['release_month'] ?? null
    ]);
    $prize_id = $pdo->lastInsertId();

    // 3. 店舗登録処理
    if (!empty($_POST['shop_ids'])) {
        $stmt_shop = $pdo->prepare("INSERT INTO prize_shops (prize_id, shop_id) VALUES (?, ?)");
        foreach ($_POST['shop_ids'] as $shop_id) {
            $stmt_shop->execute([$prize_id, $shop_id]);
        }
    }

    // 4. ギャラリー画像登録処理
    $image_columns = ['image_url1', 'image_url2', 'image_url3', 'image_url4'];
    for ($i = 0; $i < 4; $i++) {
        $col = $image_columns[$i];
        $save_value = null;

        if (!empty($_FILES['gallery_images']['tmp_name'][$i]) && is_uploaded_file($_FILES['gallery_images']['tmp_name'][$i])) {
            $fileName = 'gallery_' . $prize_id . '_' . time() . '_' . $i . '.jpg';
            // ROOT_PATH を使って絶対パスで保存
            if (move_uploaded_file($_FILES['gallery_images']['tmp_name'][$i], ROOT_PATH . '/uploads/' . $fileName)) {
                $save_value = $fileName;
            }
        } elseif (!empty($_POST['gallery_urls'][$i])) {
            $save_value = $_POST['gallery_urls'][$i];
        }

        if ($save_value) {
            $stmt = $pdo->prepare("UPDATE prizes SET $col = ? WHERE id = ?");
            $stmt->execute([$save_value, $prize_id]);
        }
    }

    $pdo->commit();
    header("Location: " . BASE_URL . "/list.php");
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // 共通エラー処理（ログ出力と一覧画面へのリダイレクト）
    handleActionError($e, "景品の新規登録中にエラーが発生しました。");
}