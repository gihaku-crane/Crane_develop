<?php
/**
 * db_connect.php
 * データベースへの接続を確立するための共通ファイル。
 * 各ページ（list.php, item_detail.php 等）の冒頭で require して使用します。
 */

// --- 1. 接続用パラメータの設定 ---
$host 		= 'localhost';			// データベースが稼働しているサーバーのアドレス
$dbname 	= 'crane_game_db';		// 使用するデータベース名
$user 		='root';				// 接続ユーザー名（開発環境のデフォルトは root が多い）
$password 	='';					// 接続パスワード（環境に合わせて設定が必要）
$charset 	='utf8mb4';				// 文字コード設定（絵文字なども扱える utf8mb4 を推奨）


// --- 2. DSN (Data Source Name) の構築 ---
// PHPのPDOクラスがどこに、どうやって接続するかを定義する文字列
$dsn ="mysql:host=$host;dbname=$dbname;charset=$charset";


// --- 3. PDOの動作オプション設定 ---
$options=[
	// SQL実行時にエラーが発生した場合、例外（PDOException）を投げる設定
	PDO::ATTR_ERRMODE 				=> PDO::ERRMODE_EXCEPTION,	//エラー時に例外を投げる

	// データの取得形式をデフォルトで「カラム名をキーとした連想配列」にする設定
    // これにより $row['name'] のような形式で値を取り出せるようになる
	PDO::ATTR_DEFAULT_FETCH_MODE 	=> PDO::FETCH_ASSOC,		//連想配列を取得

	// 静的プレースホルダ（エミュレートOFF）を使用する設定
    // SQLインジェクション対策としてより安全であり、データの型も正しく扱える
	PDO::ATTR_EMULATE_PREPARES 		=> false, 					//型を正しく扱う
];


// --- 4. データベース接続の実行 ---
try {
    $pdo = new PDO($dsn, $user, $password, $options);
} catch (PDOException $e) {
    // 現在実行中のファイル名を取得
    $script_name = basename($_SERVER['SCRIPT_NAME']);
    $error_msg = 'データベース接続に失敗しました: ' . $e->getMessage();

    // check_title.php から呼ばれた場合のみ、JSONで返す
    if ($script_name === 'check_title.php') {
        header('Content-Type: application/json', true, 500);
        die(json_encode(['error' => $error_msg]));
    }

    // 通常のページからの場合は、今まで通りexitして画面に表示
    exit($error_msg);
}

/**
 * 【補足】
 * このファイルが読み込まれると、呼び出し元のスクリプトで変数 $pdo が利用可能になります。
 * SQLを実行する際は、この $pdo オブジェクトのメソッド（prepare, query 等）を使用します。
 */