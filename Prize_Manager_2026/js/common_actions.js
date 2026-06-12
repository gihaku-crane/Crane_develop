/**
 * 汎用ステータス更新関数
 * @param {number} id - 景品ID
 * @param {string} type - 更新対象 ('got_status' or 'is_favorite')
 * @param {string|number} newVal - 新しい値
 */
function updatePrizeStatus(id, type, newVal) {
    return fetch('update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}&type=${type}&val=${newVal}`
    })
    .then(response => response.json());
}