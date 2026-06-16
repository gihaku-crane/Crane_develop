<?php
// controllers/top_controller.php
require_once __DIR__ . '/../models/top_model.php';

// Top.php 冒頭のロジック部分に追加
$today = new DateTime();
$next_week_start = clone $today;
$next_week_start->modify('next monday');
$next_week_end = clone $next_week_start;
$next_week_end->modify('+6 days');

// DBからデータを取得
$upcoming_prizes = getUpcomingPrizes($pdo, $next_week_start->format('Y-m-d'), $next_week_end->format('Y-m-d'));
?>