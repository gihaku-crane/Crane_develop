<?php
/**
 * item_detail.php
 * スペック情報（右側）の幅を以前のレイアウトに合わせて広げ、
 * ギャラリーを常に4枠（空きはサンプル画像）表示するよう調整。
 */
require_once 'config/env.php';
require_once __DIR__ . '/includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/header.php';

// MVCの呼び出し
require_once 'models/detail_model.php';
require_once 'controllers/detail_controller.php';

require_once 'views/detail_view.php';
?>