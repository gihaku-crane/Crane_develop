// js/common.js
async function updatePrizeStatus(id, type, val) {
    const response = await fetch('update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}&type=${type}&val=${val}`
    });
    return await response.json();
}