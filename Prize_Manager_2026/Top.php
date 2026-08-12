<?php
/**
 * Top.php
 * アプリケーションのトップページを表示
 */

// 1. セッションと環境設定
session_start();
require_once 'config/env.php';
require_once __DIR__ . '/includes/config.php';

// 2. 外部ライブラリ・共通機能の読み込み
require_once 'includes/db_connect.php'; // データベース接続
require_once 'includes/header.php';// 共通ヘッダー
require_once 'includes/calendar_logic.php'; // カレンダー生成ロジック

// 3. データ取得・ロジック処理（コントローラー）
// 表示に必要なデータ ($upcoming_prizes 等) を準備する
require_once 'controllers/top_controller.php';

// 4. 画面出力（ビュー）
// ロジックで準備された変数を使い、HTMLを表示する
require_once 'views/top_view.php';

// (将来的な拡張用)
//require_once 'includes/footer.php';