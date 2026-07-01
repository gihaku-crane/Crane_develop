document.addEventListener('DOMContentLoaded', () => {
    // 1. モーダル本体があるか確認（ガード）
    const modal = document.getElementById('editModal');
    if (!modal) return;

    // すべてのフォーム要素（input, select, textarea）を配列で取得しておく
    const allFormElements = modal.querySelectorAll('input, select, textarea');

    // 2. モーダル内要素を取得
    const openBtn = document.getElementById('openEditModal');
    const closeBtn = document.getElementById('closeModalBtn');
    const shopSelect = document.getElementById('shopSelect');
    const addShopBtn = document.getElementById('addShopBtn');
    const shopTags   = document.getElementById('shopTags');
    const galleryContainer = document.getElementById('galleryContainer');
    const addGalleryBtn = document.getElementById('addGalleryBtn');
    
    // 入荷店舗のコンテナを取得する例
    const shopContainer = document.getElementById('shopTags');
    // 中身の input や select を操作するなら
    const inputs = shopContainer ? shopContainer.querySelectorAll('input, select') : [];


    // 共通のモーダル表示関数
    function openModal(targetId, initialValue = '') {
        modal.style.display = 'flex';

        // 1. 全フィールドを一旦無効化
        allFormElements.forEach(el => {
            el.disabled = (el.name !== 'id'); // ID以外は無効
        });

        // ★追加：値をフォームにセットする処理
        // targetId に応じて、モーダル内の対応する input の name 属性を指定します
        // 例えば、arrival_date であれば name="arrival_date" の input を探します
        if (initialValue) {
            // arrival_date の時は arrival_date を探すなど、必要に応じて調整してください
            const targetInput = modal.querySelector(`[name="${targetId === 'arrival_date' ? 'arrival_date' : targetId}"]`);
            if (targetInput) {
                targetInput.value = initialValue;
            }
        }

        // 2. ボタン類の制御（ここが重要です）
        // ギャラリー追加ボタンを targetId が 'all' の時以外は無効にする
        if (addGalleryBtn) {
            addGalleryBtn.disabled = (targetId !== 'all');
        }
        // 店舗追加ボタンも同様に制御
        if (addShopBtn) {
            addShopBtn.disabled = (targetId !== 'all');
        }
        // 店舗削除ボタンを全て一度無効化する
        if (shopTags) {
            shopTags.querySelectorAll('.remove-shop').forEach(btn => {
                btn.disabled = true;
            });
        }

        // 3. モード別の有効化処理
        if (targetId === 'all') {
            // 全編集モード
            allFormElements.forEach(el => el.disabled = false);
            // 削除ボタンも全有効化
            if (shopTags) {
                shopTags.querySelectorAll('.remove-shop').forEach(btn => btn.disabled = false);
            }
        } else {
            // 指定されたIDの要素を有効化
            const targetEl = document.getElementById(targetId);
            if (targetEl) {
                targetEl.disabled = false;
            }

            // 入荷店舗(shopTags)の場合の例外処理
            if (targetId === 'shopTags') {
                if (shopTags) {
                    shopTags.querySelectorAll('input, select, button').forEach(el => el.disabled = false);
                }
                // 店舗追加ボタンを有効化する
                if (addShopBtn) addShopBtn.disabled = false;
                // 店舗選択プルダウンも有効化
                if (shopSelect) shopSelect.disabled = false;
            }
        }
    }


    // 「編集する（全編集）」ボタンのイベント
    const openEditModalBtn = document.getElementById('openEditModal');
    if (openEditModalBtn) {
        openEditModalBtn.addEventListener('click', (e) => {
            e.preventDefault();
            openModal('all');
        });
    }

    // 3. モーダルの開閉処理
    if (openBtn) {
        openBtn.addEventListener('click', (e) => {
            e.preventDefault();
            modal.style.display = 'flex';
        });
    }

    // 各項目ごとの「✎（row-edit-btn）」のイベント
    document.querySelectorAll('.row-edit-btn, .edit-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            const targetId = e.currentTarget.getAttribute('data-target');
            const parentItem = e.currentTarget.closest('.spec-item');
            let dateValue = '';
            
            // 入荷予定日がどの要素に入っているか確認し、その値を取得
            if (targetId === 'arrival_date' && parentItem) {
                dateValue = parentItem.querySelector('.arrival-date-cell')?.innerText.trim() || '';
            }

            openModal(targetId, dateValue);
        });
    });

    const closeModal = () => { modal.style.display = 'none'; };
    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    // 4. 店舗の追加・削除処理
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

        // 店舗タグの削除処理
        shopTags.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-shop')) {
                const removeBtn = e.target.closest('.remove-shop');
                if (removeBtn && !removeBtn.disabled) {
                    removeBtn.parentElement.remove();
                }
            }
        });
    }

    // ギャラリー追加処理
    if (addGalleryBtn && galleryContainer) {
        addGalleryBtn.addEventListener('click', () => {
            const currentCount = galleryContainer.querySelectorAll('.gallery-input-row').length;
            
            if (currentCount < 4) {
                const newRow = document.createElement('div');
                newRow.className = 'gallery-input-row';
                newRow.style.marginBottom = '10px';
                newRow.innerHTML = `
                    <input type="file" name="gallery_images[]" accept="image/*">
                    <input type="text" name="gallery_urls[]" placeholder="URL直接入力">
                `;
                newRow.querySelectorAll('input').forEach(input => {
                    input.disabled = false;
                });
                galleryContainer.appendChild(newRow);
            } else {
                alert('ギャラリー画像は最大4つまでです。');
            }
        });
    }

    // 【修正】フォーム送信処理
    const editForm = modal.querySelector('form');
    if (editForm) {
        editForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            allFormElements.forEach(el => el.disabled = false);
            if (galleryContainer) {
                galleryContainer.querySelectorAll('input').forEach(input => input.disabled = false);
            }

            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'is_modal_update';
            hiddenInput.value = '1';
            editForm.appendChild(hiddenInput);

            const response = await fetch(editForm.action, {
                method: 'POST',
                body: new FormData(editForm)
            });
            const result = await response.json();

            if (result.success) {
                alert('更新しました');
                location.reload();
            } else {
                alert('エラー: ' + result.message);
            }
        });
    }
});