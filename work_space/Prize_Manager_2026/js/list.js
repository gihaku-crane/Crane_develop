console.log("list.js: システムを読み込みました");

/**
 * 獲得状況を非同期で更新する
 */
function changeStatus(id, newStatus) {
    const container = document.getElementById('status-container-' + id);
    if (!container) return;

    updatePrizeStatus(id, 'got_status', newStatus)
        .then(data => {
            if (data.success) {
                // DB更新成功時のみ、ボタンの表示を切り替える
                const isGot = (newStatus === 'got');
                const nextStatus = isGot ? 'un' : 'got';
                const btnClass = isGot ? 'badge-got' : 'badge-un';
                const btnText = isGot ? '獲得済' : '未獲得';

                container.innerHTML = `<button type="button" class="${btnClass}" onclick="changeStatus(${id}, '${nextStatus}')">${btnText}</button>`;
            } else {
                alert("更新に失敗しました");
            }
        })
        .catch(err => console.error(err));
}

/**
 * 2. 汎用トースト通知関数
 */
function showToast(message) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;

    container.appendChild(toast);

    // アニメーション終了後に削除 (2.5秒)
    setTimeout(() => {
        toast.remove();
    }, 2500);
}

/**
 * 3. シリーズ選択エリアの表示制御
 */
function toggleCheckboxArea() {
    const area = document.getElementById('checkbox-area');
    if (area) {
        area.style.display = area.style.display === 'block' ? 'none' : 'block';
    }
}

/**
 * シリーズ選択ラベルの更新 (○つ選択中)
 */
function updateSelectLabel() {
    const checkboxes = document.querySelectorAll('input[name="series_ids[]"]:checked');
    const label = document.getElementById('select-label');
    if (!label) return;

    if (checkboxes.length > 0) {
        label.innerText = checkboxes.length + " つ選択中";
    } else {
        label.innerText = "シリーズを選択（複数可）";
    }
}/**
 * カレンダーとプルダウンの連動関数（相互の隠しフィールドも更新）
 */
function syncDate(calId, yId, mId, dId) {
    const calEl = document.getElementById(calId);
    if (!calEl) return;

    calEl.addEventListener('change', function() {
        const val = this.value;
        if (!val) return;
        const p = val.split('-');
        
        const yEl = document.getElementById(yId);
        const mEl = document.getElementById(mId);
        const dEl = document.getElementById(dId);

        // 競合防止：値を代入する直前に一度プルダウンをクリア（目視では見えない速度で行われます）
        if (yEl) yEl.value = "";
        if (mEl) mEl.value = "";
        if (dEl) dEl.value = "";

        if (yEl) yEl.value = p[0];
        if (mEl) mEl.value = p[1];
        if (dEl) dEl.value = p[2];

        // 対応する隠しフィールド（arrival_date_start/end）にも値を同期させる
        const hiddenId = calId === 's_calendar' ? 'arrival_date_start' : 'arrival_date_end';
        const hiddenEl = document.getElementById(hiddenId);
        if (hiddenEl) {
            hiddenEl.value = val;
        }
    });
}

/**
 * 4. ページ読み込み時 & クリックイベントの初期設定
 */
window.addEventListener('DOMContentLoaded', () => {
    updateSelectLabel();
    
    // システム起動確認
    showToast("システム準備完了");

    // 各カレンダーとプルダウンの動的連動を初期化
    syncDate('s_calendar', 's_year', 's_month', 's_day');
    syncDate('e_calendar', 'e_year', 'e_month', 'e_day');

    // プルダウン変更時、隠しフィールドのパラメータ(YYYY-MM-DD)も同期してリクエストを維持
    const syncSelectToHidden = (prefix) => {
        const yEl = document.getElementById(prefix + '_year');
        const mEl = document.getElementById(prefix + '_month');
        const dEl = document.getElementById(prefix + '_day');
        const hiddenEl = document.getElementById('arrival_date_' + (prefix === 's' ? 'start' : 'end'));

        if (!yEl || !mEl || !dEl || !hiddenEl) return;

        const handleSelectChange = () => {
            const y = yEl.value;
            const m = mEl.value;
            const d = dEl.value;
            if (y && m && d) {
                hiddenEl.value = `${y}-${m}-${d}`;
            } else {
                hiddenEl.value = '';
            }
        };

        yEl.addEventListener('change', handleSelectChange);
        mEl.addEventListener('change', handleSelectChange);
        dEl.addEventListener('change', handleSelectChange);
    };
    syncSelectToHidden('s');
    syncSelectToHidden('e');

    // 「本日」ボタン押下時のイベントリスナー登録
    const setTodayBtn = document.getElementById('setTodayBtn');
    if (setTodayBtn) {
        setTodayBtn.addEventListener('click', () => {
            const today = new Date();
            const y = today.getFullYear();
            const m = String(today.getMonth() + 1).padStart(2, '0');
            const d = String(today.getDate()).padStart(2, '0');
            const formattedDate = `${y}-${m}-${d}`;

            // プルダウンオブジェクト
            const sY = document.getElementById('s_year');
            const sM = document.getElementById('s_month');
            const sD = document.getElementById('s_day');
            const eY = document.getElementById('e_year');
            const eM = document.getElementById('e_month');
            const eD = document.getElementById('e_day');

            // カレンダーおよび隠しフィールド
            const sCal = document.getElementById('s_calendar');
            const eCal = document.getElementById('e_calendar');
            const sHidden = document.getElementById('arrival_date_start');
            const eHidden = document.getElementById('arrival_date_end');

            // 1. 直前の過去日付を内部リセット
            if (sY) sY.value = ""; if (sM) sM.value = ""; if (sD) sD.value = "";
            if (eY) eY.value = ""; if (eM) eM.value = ""; if (eD) eD.value = "";
            if (sCal) sCal.value = ""; if (eCal) eCal.value = "";
            if (sHidden) sHidden.value = ""; if (eHidden) eHidden.value = "";

            // 2. 本日の日付を一瞬で代入
            if (sY) sY.value = y;
            if (sM) sM.value = m;
            if (sD) sD.value = d;

            if (eY) eY.value = y;
            if (eM) eM.value = m;
            if (eD) eD.value = d;

            if (sCal) sCal.value = formattedDate;
            if (eCal) eCal.value = formattedDate;

            if (sHidden) sHidden.value = formattedDate;
            if (eHidden) eHidden.value = formattedDate;
        });
    }
});



/**
 * 画面クリックでセレクトボックスを閉じる処理
 */
window.addEventListener('click', (event) => {
    if (!event.target.closest('.custom-select-container')) {
        const area = document.getElementById('checkbox-area');
        if (area) area.style.display = 'none';
    }
});


// カレンダーとプルダウンの連動関数
function syncDate(calId, yId, mId, dId) {
    const calendar = document.getElementById(calId);
    // カレンダー要素が存在する場合のみ処理を実行
    if (calendar) {
        calendar.addEventListener('change', function() {
            const val = this.value;
            if (!val) return;
            const p = val.split('-');
            const yearEl = document.getElementById(yId);
            const monthEl = document.getElementById(mId);
            const dayEl = document.getElementById(dId);
            
            if (yearEl) yearEl.value = p[0];
            if (monthEl) monthEl.value = p[1];
            if (dayEl) dayEl.value = p[2];
        });
    }
}

// 実行する際もチェックする
syncDate('s_calendar', 's_year', 's_month', 's_day');
syncDate('e_calendar', 'e_year', 'e_month', 'e_day');

/**
 * お気に入りの状態を非同期で切り替える
 */
function toggleFavorite(id, currentVal) {
    // 現在が1(ON)なら0(OFF)へ、0なら1へ
    const newVal = (currentVal === 1) ? 0 : 1;

    fetch(`update_status.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}&type=is_favorite&val=${newVal}`
    })
    .then(response => {
        if (!response.ok) throw new Error('サーバーエラー');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // 一旦確実な反映のためリロード。
            // 慣れてきたらここをDOM操作で書き換えるとよりスムーズになります。
            location.reload();
        } else {
            alert("お気に入りの更新に失敗しました。");
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("通信に失敗しました。");
    });
}