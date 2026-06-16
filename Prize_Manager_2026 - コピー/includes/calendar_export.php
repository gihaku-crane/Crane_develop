<?php
require_once 'includes/db_connect.php';

// カレンダー形式（iCal）としてブラウザに認識させるためのヘッダー
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="prizes_schedule.ics"');

// データの取得（お気に入り 且つ 入荷予定日があるもの）
$sql = "SELECT name, Arrival_date, shop, series FROM prizes 
        WHERE is_favorite = 1 AND Arrival_date IS NOT NULL AND Arrival_date != ''";
$stmt = $dbh->query($sql);
$prizes = $stmt->fetchAll();

// iCal形式の書き出し開始
echo "BEGIN:VCALENDAR\r\n";
echo "VERSION:2.0\r\n";
echo "PRODID:-//PrizeManager//NONSGML v1.0//JP\r\n";
echo "X-WR-CALNAME:景品入荷予定リスト\r\n"; // カレンダーの名前
echo "CALSCALE:GREGORIAN\r\n";
echo "METHOD:PUBLISH\r\n";

foreach ($prizes as $prize) {
    // 日付フォーマットの変換（YYYY-MM-DD -> YYYYMMDD）
    $date = str_replace('-', '', $prize['Arrival_date']);
    
    echo "BEGIN:VEVENT\r\n";
    echo "SUMMARY:【入荷】" . $prize['name'] . "\r\n";
    echo "DTSTART;VALUE=DATE:" . $date . "\r\n"; // 終日の予定として登録
    echo "DTEND;VALUE=DATE:" . $date . "\r\n";
    echo "DESCRIPTION:シリーズ: " . $prize['series'] . "\\n店舗: " . $prize['shop'] . "\r\n";
    echo "END:VEVENT\r\n";
}

echo "END:VCALENDAR\r\n";