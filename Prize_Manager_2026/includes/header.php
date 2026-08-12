<?php
/**
 * header.php
 * 全画面共通のヘッダー出力を管理
 * 
 */

/**
 * 共通ヘッダーを出力する関数
 * @param string $current_page 現在表示中のページ識別子（'top', 'list' 等）
 */
function output_header($current_page) {
    // セッションを開始（まだ開始されていない場合）
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $is_debug_mode = $_SESSION['debug_mode'] ?? false;

    // 2. ナビゲーションメニューの定義
    // 'キー' => 'リンク先ファイル名'
    $nav_items = [
        'top'    => 'Top.php',
        'list'   => 'list.php',
        'shop'   => 'shop_list.php',
        'series' => 'series_list.php',
        'menu'   => 'menu.php'
    ];
?>
    <header>
        <!-- ヘッダー上部の装飾用バナーエリア -->
        <div class="header-banner"></div>
        <div class="header-content">

            <!-- サイトロゴ・タイトルエリア -->
            <h1 class="header-title">
                <!-- BASE_URL は env.php で定義されているサイトのルートURLです -->
                <a href="<?php echo BASE_URL; ?>Top.php">Prize_Manager_2026</a>
            </h1>
            
            <nav class="g-nav">
                <!-- ナビゲーションメニューをforeachで表示 -->
                <?php foreach ($nav_items as $key => $file): ?>
                    <!-- 
                         現在のページ($current_page)とメニューのキー($key)が一致する場合、
                         CSSで強調表示するための 'active' クラスを付与します。
                    -->
                    <?php
                    // 現在のページなら 'active' クラスを付与
                    $is_active = ($current_page === $key) ? ' active' : '';
                    ?>
                        <a href="<?php echo BASE_URL . $file; ?>" class="g-nav-link <?php echo $is_active; ?>">
                        <!-- strtoupper() 関数で、全て大文字に変換して表示 -->
                        <?php echo strtoupper($key); ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="header-right-group">
                <form action="<?php echo BASE_URL; ?>toggle_debug.php" method="POST" class="debug-form">
                    <button type="submit" class="debug-btn <?php echo $is_debug_mode ? 'is-on' : ''; ?>">
                        DEBUG: <?php echo $is_debug_mode ? 'ON' : 'OFF'; ?>
                    </button>
                </form>

                <!-- 景品追加用のアクションボタン（リンク先は今後実装予定） -->
                <a href="add_prize.php" class="add-btn">+ ADD PRIZE</a>
            </div>
        </div>
    </header>
    <?php
}
