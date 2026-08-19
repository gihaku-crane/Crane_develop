document.addEventListener('DOMContentLoaded', () => {
    const totalItems = typeof galleryImages !== 'undefined' ? galleryImages.length : 0;
    if (totalItems === 0) return;

    let currentIndex = 0;
    let autoPlayTimer = null;
    const intervalTime = 4000; // 自動スライドの間隔（ミリ秒：4秒）

    const mainItems = document.querySelectorAll('.main-img');
    const thumbItems = document.querySelectorAll('.thumb-item');
    const subImgList = document.getElementById('subImgList');

    function updateGallery() {
        // 1. CSS変数に総枚数をセット
        if (subImgList) {
            subImgList.style.setProperty('--total-items', totalItems);
        }

        // 2. メイン画像の切り替え
        mainItems.forEach((item, idx) => {
            if (idx === currentIndex) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });

        // 3. サムネイルのハイライト切り替え
        thumbItems.forEach((item, idx) => {
            if (idx === currentIndex) {
                item.style.border = '2px solid #00d4ff'; // アクセントカラー
                item.style.opacity = '1';
                item.style.backgroundColor = 'rgba(0, 212, 255, 0.1)';
            } else {
                item.style.border = '1px solid #30363d'; // 通常枠
                item.style.opacity = '0.4';
                item.style.backgroundColor = 'transparent';
            }
        });

        // 4. サムネイル一覧の横スライド位置計算
        const offsetPerItem = 100 / totalItems;
        const offset = currentIndex * offsetPerItem;
        
        if (subImgList) {
            subImgList.style.transform = `translateX(-${offset}%)`;
        }
    }

    // 次へ進む処理
    function nextSlide() {
        currentIndex = (currentIndex + 1) % totalItems;
        updateGallery();
    }

    // 前へ戻る処理
    function prevSlide() {
        currentIndex = (currentIndex - 1 + totalItems) % totalItems;
        updateGallery();
    }

    // 自動スライドのタイマーリセット（ボタン操作時などにタイマーをリセットして仕切り直す）
    function resetAutoPlay() {
        clearInterval(autoPlayTimer);
        startAutoPlay();
    }

    function startAutoPlay() {
        // 画像が複数ある場合のみ自動スライドを有効にする
        if (totalItems > 1) {
            autoPlayTimer = setInterval(nextSlide, intervalTime);
        }
    }

    // 「次へ」ボタン（>）
    const nextBtn = document.getElementById('galleryNextBtn');
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            nextSlide();
            resetAutoPlay();
        });
    }

    // 「前へ」ボタン（<）
    const prevBtn = document.getElementById('galleryPrevBtn');
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            prevSlide();
            resetAutoPlay();
        });
    }

    // 各サムネイルをクリックしたとき
    thumbItems.forEach((thumb) => {
        thumb.addEventListener('click', () => {
            const idx = parseInt(thumb.getAttribute('data-index'), 10);
            if (!isNaN(idx)) {
                currentIndex = idx;
                updateGallery();
                resetAutoPlay();
            }
        });
    });

    // 初期化実行 ＆ 自動スライド開始
    updateGallery();
    startAutoPlay();
});