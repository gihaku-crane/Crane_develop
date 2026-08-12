// 重心プロッターの制御
document.addEventListener('DOMContentLoaded', () => {
    const box = document.getElementById('gravity-box');
    const marker = document.getElementById('gravity-marker');
    const grid = document.getElementById('click-grid');

    grid.addEventListener('click', (e) => {
        // クリックしたセルの座標を取得してマーカーを移動
        const rect = box.getBoundingClientRect();
        // セルの中心計算などをここで行う
        // 簡単のため、セルの位置に合わせる実装例
        const cell = e.target.closest('.grid-cell');
        if (!cell) return;

        const cellRect = cell.getBoundingClientRect();
        const boxRect = box.getBoundingClientRect();

        const x = cellRect.left + cellRect.width / 2 - boxRect.left;
        const y = cellRect.top + cellRect.height / 2 - boxRect.top;

        marker.style.left = `${x}px`;
        marker.style.top = `${y}px`;
    });
});