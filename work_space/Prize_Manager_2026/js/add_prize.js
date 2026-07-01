// js/add_prize.js

document.addEventListener('DOMContentLoaded', function() {

    // 1. 店舗タグのバッチ処理（追加画面用）
    const shopTags = document.getElementById('shopTags');
    const shopSelect = document.getElementById('shopSelect');
    const addShopBtn = document.getElementById('addShopBtn');

    if (shopTags && shopSelect && addShopBtn) {
        addShopBtn.addEventListener('click', () => {
            const selectedOption = shopSelect.options[shopSelect.selectedIndex];
            if (!selectedOption.value) return;
            // 重複チェック
            if (shopTags.querySelector(`input[value="${selectedOption.value}"]`)) return;

            const tag = document.createElement('span');
            tag.className = 'shop-tag';
            // 見た目のクラス（既存のCSSがあれば調整してください）
            tag.innerHTML = `${selectedOption.text} <button type="button" class="remove-shop">×</button><input type="hidden" name="shop_ids[]" value="${selectedOption.value}">`;
            shopTags.appendChild(tag);
        });

        shopTags.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-shop')) {
                e.target.parentElement.remove();
            }
        });
    }

    // 2. フォーム送信時のチェック（バリデーション）
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            let hasError = false;
            const targets = [
                { id: 'name', msg: '景品名は必須項目です' },
                { id: 'series_id', msg: 'シリーズ名は必須項目です' },
                { id: 'title', msg: '作品タイトル名は必須項目です' }
            ];

            // エラー表示をクリア
            document.querySelectorAll('.error-msg').forEach(el => el.innerText = '');

            targets.forEach(t => {
                const input = document.getElementById(t.id);
                if (input && !input.value.trim()) {
                    const err = document.getElementById(t.id + '-error');
                    if (err) {
                        err.innerText = t.msg;
                        err.style.color = 'red';
                    }
                    hasError = true;
                }
            });

            if (hasError) {
                e.preventDefault();
                return false;
            }
        });
    }

    // 3. タイトル入力時の存在確認（blurイベント）
    const titleInput = document.getElementById('title');
    const newTitleContainer = document.getElementById('new-title-fields');

    if (titleInput && newTitleContainer) {
        titleInput.addEventListener('blur', function() {
            const val = this.value.trim();
            if (!val) return;

            fetch('actions/check_title.php?q=' + encodeURIComponent(val))
                .then(res => res.json())
                .then(data => {
                    if (!data.exists) {
                        newTitleContainer.style.display = 'block';
                        newTitleContainer.innerHTML = `
                            <div class="new-title-box">
                                <p>新規タイトルです。詳細を入力してください。</p>
                                <div class="new-title-field">
                                    <label>略称：</label>
                                    <input type="text" name="new_title_abbr" placeholder="略称を入力" required>
                                </div>
                                <div class="new-title-field">
                                    <label>五十音：</label>
                                    <input type="text" name="new_title_index" placeholder="五十音を入力" required>
                                </div>
                            </div>
                        `;
                    } else {
                        newTitleContainer.style.display = 'none';
                        newTitleContainer.innerHTML = '';
                    }
                })
                .catch(err => console.error("Fetch error:", err));
        });
    }
});

// 4. ギャラリー画像追加関数
function addGalleryInput() {
    const container = document.getElementById('gallery-container');
    if (!container) return;
    
    const rows = container.getElementsByClassName('gallery-input-row');
    if (rows.length < 4) {
        const newRow = document.createElement('div');
        newRow.className = 'gallery-input-row';
        newRow.style.marginTop = '10px';
        newRow.innerHTML = `
            <input type="file" name="gallery_images[]" accept="image/*">
            <input type="text" name="gallery_urls[]" placeholder="画像URL">
        `;
        container.appendChild(newRow);
    } else {
        alert("ギャラリー画像は最大4つまでです。");
    }
}

// --- リリース年月の自動算出処理 ---
const arrivalDateInput = document.querySelector('input[name="arrival_date"]');
const releaseMonthInput = document.getElementById('release_month');

if (arrivalDateInput && releaseMonthInput) {
    arrivalDateInput.addEventListener('change', function() {
        const date = new Date(this.value);
        if (!isNaN(date.getTime())) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            releaseMonthInput.value = `${year}-${month}`;
        }
    });
}