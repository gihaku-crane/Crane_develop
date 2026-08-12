/**
 * 景品カルーセルスライダー制御用スクリプト
 * ・一定時間ごとの自動スライド（オートプレイ）
 * ・左右のボタンによる手動スライド
 * ・カードが5枚未満の場合はボタンを非表示にするなどの安全設計
 */
document.addEventListener("DOMContentLoaded", function () {
    // ページ内にあるすべてのスライダーラッパー要素を取得する
    const sliders = document.querySelectorAll(".prize-slider-wrapper");

    sliders.forEach(slider => {
        // スライダー内の各パーツ（トラック、カード、左右ボタン）を取得
        const track = slider.querySelector(".prize-slider-track");
        const cards = track ? track.querySelectorAll(".item-card") : [];
        const prevBtn = slider.querySelector(".prev-btn");
        const nextBtn = slider.querySelector(".next-btn");

        // トラックまたはカードが存在しない場合は処理を中断
        if (!track || cards.length === 0) return;

        let currentIndex = 0;
        const visibleCount = 5; // 一度に画面内に表示するカードの枚数
        // スライドできる最大のインデックス番号を計算（マイナスにならないよう 0 と比較）
        const maxIndex = Math.max(0, cards.length - visibleCount);
        let autoPlayTimer; // 自動スライド用のタイマー変数

        // カードが一度に表示できる枚数（5枚）以下の場合は、左右ボタンを隠す
        if (cards.length <= visibleCount) {
            if (prevBtn) prevBtn.style.display = "none";
            if (nextBtn) nextBtn.style.display = "none";
            return; // スライドさせる必要がないためここで終了
        }

        /**
         * 現在のインデックスに応じてトラックの位置（translateX）を移動させる関数
         */
        function updateSlidePosition() {
            // 1枚あたりのカードの実際の幅を取得
            const cardWidth = cards[0].getBoundingClientRect().width;
            const gap = 12; // CSSで設定しているカード間の隙間（px）
            // 移動する総ピクセル数を計算
            const moveAmount = currentIndex * (cardWidth + gap);
            // CSSのtransformを使って横方向にスライドさせる
            track.style.transform = `translateX(-${moveAmount}px)`;
        }

        /**
         * 次のカードへ進む関数
         */
        function slideNext() {
            if (currentIndex < maxIndex) {
                currentIndex++; // まだ端にきていなければ次へ
            } else {
                currentIndex = 0; // 最後までいったら最初の位置に戻る（ループ）
            }
            updateSlidePosition();
        }

        /**
         * 前のカードに戻る関数
         */
        function slidePrev() {
            if (currentIndex > 0) {
                currentIndex--; // 先頭でなければ前へ
            } else {
                currentIndex = maxIndex; // 先頭にいる状態で押されたら一番最後に飛ぶ
            }
            updateSlidePosition();
        }

        // 【右ボタン（次へ）がクリックされた時の処理】
        if (nextBtn) {
            nextBtn.addEventListener("click", () => {
                slideNext();
                resetAutoPlay(); // 手動操作されたら自動タイマーをリセットして再カウント
            });
        }

        // 【左ボタン（前へ）がクリックされた時の処理】
        if (prevBtn) {
            prevBtn.addEventListener("click", () => {
                slidePrev();
                resetAutoPlay(); // 手動操作されたら自動タイマーをリセットして再カウント
            });
        }

        /**
         * 自動スライド（オートプレイ）を開始する関数
         * 例: 4000ミリ秒（4秒）ごとに slideNext を実行
         */
        function startAutoPlay() {
            if (maxIndex > 0) {
                autoPlayTimer = setInterval(slideNext, 4000);
            }
        }

        /**
         * ユーザーが手動でボタンを押した時に、タイマーを一度クリアして再スタートする関数
         * （ボタンを押した直後にすぐ自動スライドしてしまうのを防ぐため）
         */
        function resetAutoPlay() {
            clearInterval(autoPlayTimer);
            startAutoPlay();
        }

        // ページ読み込み完了時にオートプレイを開始
        startAutoPlay();
    });
});