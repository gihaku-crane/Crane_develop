/**
 * 完全に共通化した汎用スライダー初期化関数
 * 
 * @param {string} wrapperSelector - スライダー全体のコンテナ要素のセレクタ
 * @param {Object} options - 動作に必要な設定や要素のセレクタをすべて引数で渡す
 */
function initSlider(wrapperSelector, options = {}) {
    // 1. デフォルト設定と、ユーザーから渡された引数をマージする
    const config = Object.assign({
        trackSelector: null,     // カルーセル用のレールセレクタ
        cardSelector: null,      // カルーセル用のカードセレクタ
        mainImgSelector: null,   // シングルモード用のメイン画像セレクタ
        thumbSelector: null,     // シングルモード用のサムネイルセレクタ
        prevBtnSelector: '.prev-btn', // 前へボタンのセレクタ
        nextBtnSelector: '.next-btn', // 次へボタンのセレクタ
        visibleCount: 5,         // 一度に表示する枚数（カルーセル用）
        autoPlay: true,          // 自動再生の有無
        interval: 4000,          // 自動再生の間隔
        isCarousel: true         // trueならカルーセル、falseなら1枚切り替え(single)
    }, options);

    // 2. スライダー全体の要素を取得
    const slider = document.querySelector(wrapperSelector);
    if (!slider) return;

    // 3. モードに応じたターゲット要素の収集
    let items = [];
    if (config.isCarousel && config.trackSelector) {
        const track = slider.querySelector(config.trackSelector);
        items = track ? track.querySelectorAll(config.cardSelector) : [];
        if (!track || items.length === 0) return;
        // カード数が表示枚数以下ならスライド不要なのでボタンを隠す
        if (items.length <= config.visibleCount) {
            const pBtn = slider.querySelector(config.prevBtnSelector);
            const nBtn = slider.querySelector(config.nextBtnSelector);
            if (pBtn) pBtn.style.display = "none";
            if (nBtn) nBtn.style.display = "none";
            return;
        }
    } else if (!config.isCarousel && config.thumbSelector) {
        items = slider.querySelectorAll(config.thumbSelector);
        if (items.length <= 1) return;
    }

    let currentIndex = 0;
    const maxIndex = config.isCarousel 
        ? Math.max(0, items.length - config.visibleCount) 
        : items.length - 1;
    let autoPlayTimer;

    /**
     * 4. 共通の表示更新ロジック
     */
    function updateDisplay() {
        if (config.isCarousel) {
            const track = slider.querySelector(config.trackSelector);
            const cardWidth = items[0].getBoundingClientRect().width;
            const moveAmount = currentIndex * (cardWidth + 12); // 12pxはギャップ
            track.style.transform = `translateX(-${moveAmount}px)`;
        } else {
            const mainImg = slider.querySelector(config.mainImgSelector);
            const images = typeof galleryImages !== 'undefined' ? galleryImages : [];
            if (mainImg && images[currentIndex]) {
                mainImg.src = images[currentIndex];
            }
            items.forEach((thumb, idx) => {
                thumb.style.borderColor = (idx === currentIndex) ? "#4CAF50" : "transparent";
            });
        }
    }

    /**
     * 5. 次へ / 前へ移動する共通関数
     */
    function slideNext() {
        currentIndex = (currentIndex < maxIndex) ? currentIndex + 1 : 0;
        updateDisplay();
    }

    function slidePrev() {
        currentIndex = (currentIndex > 0) ? currentIndex - 1 : maxIndex;
        updateDisplay();
    }

    // 6. ボタンのイベント紐付け
    const nextBtn = slider.querySelector(config.nextBtnSelector);
    const prevBtn = slider.querySelector(config.prevBtnSelector);

    if (nextBtn) nextBtn.addEventListener("click", () => { slideNext(); resetAutoPlay(); });
    if (prevBtn) prevBtn.addEventListener("click", () => { slidePrev(); resetAutoPlay(); });

    // 7. サムネイルクリック対応（シングルモード用）
    if (!config.isCarousel && config.thumbSelector) {
        items.forEach((thumb, idx) => {
            thumb.addEventListener("click", () => {
                currentIndex = idx;
                updateDisplay();
                resetAutoPlay();
            });
        });
    }

    /**
     * 8. 自動再生タイマーの制御
     */
    function resetAutoPlay() {
        clearInterval(autoPlayTimer);
        startAutoPlay();
    }

    function startAutoPlay() {
        if (config.autoPlay) {
            autoPlayTimer = setInterval(slideNext, config.interval);
        }
    }

    startAutoPlay();
}