<!-- サマリーセクション：月間の統計数値を表示 -->
<div class="summary-section">
    <div class="summary-card">
        <span class="summary-value"><?= number_format($monthly_arrival_total) ?></span>
        <span class="summary-label">MONTHLY ARRIVAL</span>
        <span class="summary-label">【今月の入荷総数】</span>
    </div>
    <div class="summary-card">
        <span class="summary-value"><?= number_format($favorite_monthly_total) ?></span>
        <span class="summary-label">FAVORITES</span>
        <span class="summary-label">【お気に入り登録数】</span>
    </div>
    <div class="summary-card">
        <span class="summary-value"><?= number_format($got_total) ?></span>
        <span class="summary-label">TOTAL GOT</span>
        <span class="summary-label">【今月の獲得総数】</span>
    </div>
</div>