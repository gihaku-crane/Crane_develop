<?php
/**
 * header.php
 * 全画面共通のヘッダー部分を生成する関数を定義しています。
 * * @param string $current_page 現在表示中のページ識別子（'top', 'list' など）
 */

function output_header($current_page) {
    // --- 1. メニュー項目とファイル名の対応設定 ---
    // 左側のキーが $current_page と比較される名前、右側が実際のリンク先ファイル名です。
    $nav_items = [
        'top'    => 'Top.php',
        'list'   => 'list.php',
        /*'detail'   => 'item_detail.php',*/
        'shop'   => 'shop_list.php',
/*        'shop_detail'   => 'shop_detail.php',*/
        'series' => 'series.php',
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
                    <a href="<?php echo BASE_URL . $file; ?>" 
                       class="g-nav-link<?php echo ($current_page === $key) ? ' active' : ''; ?>">
                       <!-- strtoupper() 関数で、'top' を 'TOP' のように全て大文字に変換して表示 -->
                        <?php echo strtoupper($key); ?>

                    </a>
                <?php endforeach; ?>
            </nav>
            <!-- 景品追加用のアクションボタン（リンク先は今後実装予定） -->
            <a href="add_prize.php" class="add-btn">+ ADD PRIZE</a>
        </div>
    </header>
    <?php
}
?>