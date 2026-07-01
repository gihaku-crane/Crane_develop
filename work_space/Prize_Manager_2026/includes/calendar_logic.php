<?php
/**
 * カレンダー生成ロジック
 */

function getCalendarData($pdo, $year, $month) {
    // 前月・翌月の計算
    $prev_month = $month - 1;
    $prev_year  = $year;
    /* 今月が1月なら１２月にし年を去年にする */
    if($prev_month < 1){
        $prev_month = 12;
        $prev_year--;
    }

    $next_month = $month + 1;
    $next_year = $year;
    if ($next_month > 12) {
        /* 今月が12月なら1月にし年を来年にする */
        $next_month = 1;
        $next_year++;
    }


    // 1. 祝日取得 (外部APIを利用)
    $holidays = [];
    $holiday_url = "https://holiday-jp.github.io/api/v1/holidays.json";
    $json = @file_get_contents($holiday_url);
    if ($json) {
        $data = json_decode($json, true);
        if (is_array($data)) {
            foreach ($data as $item) {
                // API形式: [ {"date": "2024-01-01", "name": "元日"}, ... ]
                $d = $item['date'];
                if (strpos($d, sprintf("%04d", $year)) === 0) {
                    $holidays[$d] = $item['name'];
                }
            }
        }
    }

    // 2. 指定した月の景品入荷日を取得
    $prizes_in_month = [];

    try{
        $stmt = $pdo->prepare("SELECT arrival_date, is_favorite FROM prizes WHERE arrival_date LIKE ?");
        $stmt->execute([sprintf("%04d-%02d%%", $year, $month)]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $date = $row['arrival_date'];
            if (!isset($prizes_in_month[$date])) {
                $prizes_in_month[$date] = ['count' => 0, 'has_favorite' => false];
            }
            $prizes_in_month[$date]['count']++;
            if ($row['is_favorite']) {
                $prizes_in_month[$date]['has_favorite'] = true;
            }
        }
    } catch (Exception $e){
        //DBエラー時は空のまま進行
    }

    // 3. カレンダーの基本情報
    $first_day = mktime(0, 0, 0, $month, 1, $year);
    $days_in_month = date('t', $first_day);
    $start_weekday = date('w', $first_day); // 0:日 - 6:土

    $calendar_date = [];
    $day_count = 1;

    for ($w=0; $w < 6; $w++) {
        $week = array_fill(0, 7, []);
        $has_day = false;
        for ($d=0; $d < 7; $d++) { 
            if (($w === 0 && $d < $start_weekday) || $day_count > $days_in_month) {
                // code...
                continue;
            }
            $full_date = sprintf("%04d-%02d-%02d", $year, $month, $day_count);
            $week[$d] = [
                'day'          => $day_count,
                'prizes'       => $prizes_in_month[$full_date]['count'] ?? 0,
                'has_favorite' => $prizes_in_month[$full_date]['has_favorite'] ?? false,
                'is_holiday'   => isset($holidays[$full_date]),
                'is_today'     => ($full_date === date('Y-m-d'))
            ];
            $day_count++;
            $has_day_in_week = true;
        }
        if (!$has_day_in_week) break;
        $calendar_date[] = $week;
    }

    return [
        'year'          => $year,
        'month'         => $month,
        'prev_year'     => $prev_year,
        'prev_month'    => $prev_month,
        'next_year'     => $next_year,
        'next_month'    => $next_month,
        'calendar_date' => $calendar_date
    ];
}