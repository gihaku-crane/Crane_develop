<?php
/**
 * pager.php
 * ページングHTML生成 & 計算用関数
 * * 役割:
 * 1. 総件数と1ページあたりの件数から「総ページ数」を算出。
 * 2. SQLでデータを取得し始める位置（OFFSET）を計算（デバッグ用）。
 * 3. 前後のページ番号を計算し、URLパラメータを維持したままリンクを生成。
 */
function renderPager($page, $total_count, $limit) {
    // 引数を数値型として扱うことを保証（型エラー防止）
    $page = (int)$page;
    $total_count = (int)$total_count;
    $limit = (int)$limit > 0 ? (int)$limit : 20;

    // 1. 総ページ数を計算
    // 全件数を1ページ表示数で割り、端数を切り上げ(ceil)する
    $total_pages = ($total_count > 0) ? ceil($total_count / $limit) : 1;

 
    // --- ページング計算（OFFSET） ---
    // SQLの LIMIT 句と一緒に使う「何件目から取得するか」の計算式
    $offset = ($page - 1) * $limit;

    // =========================================
    // 📊 デバッグ表示エリア
    // 開発中にページングが正しく動いているか確認するためのパネル。
    // =========================================
    echo "<div style='background:#1a1a1a; color:#00ff00; padding:15px; margin:10px; border:2px solid #00ff00; border-radius:8px; font-family:monospace; line-height:1.6; z-index:9999; position:relative;'>";
    echo "<b style='color:#fff; border-bottom:1px solid #00ff00; display:block; margin-bottom:5px;'>📊 PAGING DEBUG MODE</b>";
    echo "現在のページ: <span style='font-size:1.2em;'>{$page}</span> / 全 {$total_pages} ページ<br>";
    echo "全データ件数: {$total_count} 件<br>";
    echo "1ページの表示枠: {$limit} 件<br>";
    echo "<hr style='border:0; border-top:1px dashed #00ff00;'>";
    echo "<b>SQLに渡すべき値:</b><br>";
    echo "LIMIT (件数): <span style='color:#ffcc00;'>{$limit}</span><br>";
    echo "OFFSET (開始位置): <span style='color:#ffcc00;'>{$offset}</span>";
    echo "</div>";

    // データが1ページに収まる場合は、ナビゲーションを表示せずに終了
    if ($total_pages == 0) return;

    // 2. 表示する数字ボタンの範囲を計算
    // カレントページを中心に、前後何件の数字を出すか（例: 2なら 1 2 [3] 4 5）
    $range = 2;
    $start_page = max(1, $page - $range);
    $end_page   = min($total_pages, $page + $range);

    // 現在の検索条件（キーワードなど）を維持するために $_GET をコピー
    $params = $_GET; 
    ?>
    <!-- ページャーUIの生成 -->
    <nav class="pager" style="display: flex; gap: 5px; justify-content: center; margin: 20px 0;">
        <?php 
        // 「最初へ」ボタン
        $params['page'] = 1; 
        ?>
        <a href="?<?= http_build_query($params) ?>" class="pager-edge <?= ($page <= 1) ? 'disabled' : '' ?>" style="padding:8px 12px; border:1px solid #ccc; text-decoration:none;">&lt;&lt;</a>
        
        <?php 
        // ページ番号ボタンのループ生成
        for ($i = $start_page; $i <= $end_page; $i++):
            $params['page'] = $i;
        ?>
            <a href="?<?= http_build_query($params) ?>" class="<?= ($i === $page) ? 'active' : '' ?>" style="padding:8px 12px; border:1px solid #ccc; text-decoration:none; <?= ($i === $page) ? 'background:#007bff; color:white;' : 'background:white; color:#333;' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <?php 
        // 「最後へ」ボタン
        $params['page'] = $total_pages; 
        ?>
        <a href="?<?= http_build_query($params) ?>" class="pager-edge <?= ($page >= $total_pages) ? 'disabled' : '' ?>" style="padding:8px 12px; border:1px solid #ccc; text-decoration:none;">&gt;&gt;</a>
    </nav>
<?php
}
?>