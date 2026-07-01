document.addEventListener('DOMContentLoaded', () => {
    console.log('JS loaded: 初期化開始');

    // --- 1. お気に入り切り替え処理 ---
    const favBtn = document.querySelector('.js-favorite-toggle');
    if (favBtn) { // 要素がある時だけ実行
        favBtn.addEventListener('click', function() {
            const prizeId = this.getAttribute('data-id');
            const currentFav = parseInt(this.getAttribute('data-fav'));
            const nextFav = (currentFav === 1) ? 0 : 1;
            fetch('update_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${prizeId}&type=is_favorite&val=${nextFav}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.setAttribute('data-fav', nextFav);
                    this.textContent = (nextFav === 1) ? '★' : '☆';
                }
            });
        });
    }

    // --- 2. 獲得情報の切り替え ---
    const statusBadge = document.querySelector('.js-status-toggle');
    if (statusBadge) { // 要素がある時だけ実行
        statusBadge.addEventListener('click', async function() {
            console.log("現在、関数はこれに見えています:", typeof window.updatePrizeStatus);

            const prizeId = this.getAttribute('data-id');
            const currentStatus = this.getAttribute('data-status');
            const nextStatus = (currentStatus === 'got') ? 'un' : 'got';

            // 共通関数を呼び出す
            // window. をつけることで確実に読み込みに行きます
            if (typeof window.updatePrizeStatus === 'function') {
                const data = await window.updatePrizeStatus(prizeId, 'got_status', nextStatus);

                if (data.success) {
                    this.setAttribute('data-status', nextStatus);
                    this.textContent = (nextStatus === 'got') ? '獲得済' : '未獲得';
                    this.className = `js-status-toggle ${nextStatus === 'got' ? 'badge-got' : 'badge-un'}`;
                } 
            }else {
                console.error("Update failed");
                console.error("updatePrizeStatus がまだ読み込まれていません");
            }
        });
    }
});