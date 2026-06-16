<?php
/**
 * Top.php
 * デザイン崩れを修正し、安定したレイアウトを提供します。
 */
session_start();
require_once 'config/env.php';
require_once __DIR__ . '/includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/header.php';

require_once 'includes/calendar_logic.php'; // カレンダーロジックの読み込み

// 1. 機能・ロジック処理（コントローラー）を読み込み、変数を準備
require_once 'controllers/top_controller.php';

// 2. 表示（View）
require_once 'views/top_view.php'; // ここにすべてのHTMLが記述されている
//require_once 'includes/footer.php';