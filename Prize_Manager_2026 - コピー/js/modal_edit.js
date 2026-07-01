document.addEventListener('DOMContentLoaded', () => {
    // 1. モーダル本体があるか確認（ガード）
    const modal = document.getElementById('editModal');
    if (!modal) return;

    // 2. モーダル内要素を取得
    const openBtn = document.getElementById('openEditModal');
    const closeBtn = document.getElementById('closeModalBtn');
    const shopSelect = document.getElementById('shopSelect');
    const addShopBtn = document.getElementById('addShopBtn');
    const shopTags   = document.getElementById('shopTags');
    const galleryContainer = document.getElementById('galleryContainer');
    const addGalleryBtn = document.getElementById('addGalleryBtn');

    // 3. モーダルの開閉処理
    if (openBtn) {
        openBtn.addEventListener('click', (e) => {
            e.preventDefault();
            modal.style.display = 'flex';
        });
    }

    const closeModal = () => { modal.style.display = 'none'; };
    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    // 4. 店舗の追加・削除処理
    // 要素が一つでも欠けていれば登録せずエラーを回避
    if (shopSelect && addShopBtn && shopTags) {
        
        addShopBtn.addEventListener('click', () => {
            const selectedOption = shopSelect.options[shopSelect.selectedIndex];
            const shopId = selectedOption.value;
            const shopName = selectedOption.text;

            if (!shopId) {
                alert('店舗を選択してください');
                return;
            }

            // 重複チェック
            if (shopTags.querySelector(`input[value="${shopId}"]`)) {
                alert('その店舗はすでに追加されています');
                return;
            }

            // タグを追加
            const tag = document.createElement('span');
            tag.className = 'shop-tag';
            tag.innerHTML = `
                ${shopName} 
                <button type="button" class="remove-shop">×</button>
                <input type="hidden" name="shop_ids[]" value="${shopId}">
            `;
            shopTags.appendChild(tag);
            
            // プルダウンをリセット
            shopSelect.value = ""; 
        });

        // 店舗タグの削除処理（親要素でのイベントキャプチャ）
        shopTags.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-shop')) {
                e.target.parentElement.remove();
            }
        });

    if (addGalleryBtn) {
        addGalleryBtn.addEventListener('click', () => {
            // 現在の個数をカウント
            const currentCount = galleryContainer.querySelectorAll('.gallery-input-row').length;
            
            if (currentCount < 4) {
                const newRow = document.createElement('div');
                newRow.className = 'gallery-input-row';
                newRow.style.marginBottom = '10px';
                newRow.innerHTML = `
                    <input type="file" name="gallery_images[]" accept="image/*">
                    <input type="text" name="gallery_urls[]" placeholder="URL直接入力">
                `;
                galleryContainer.appendChild(newRow);
            } else {
                alert('ギャラリー画像は最大4つまでです。');
            }
        });
    }
    }
});