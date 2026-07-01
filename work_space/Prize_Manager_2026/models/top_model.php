<?php
//models/top_model.php
function getUpcomingPrizes($pdo, $start_date, $end_date) {
    $sql_upcoming = "
    SELECT p.id, p.name AS prize_name, s.name AS series_name, p.arrival_date 
    FROM prizes p
    LEFT JOIN series s ON p.series_id = s.id
    WHERE p.arrival_date BETWEEN ? AND ?
    ORDER BY p.arrival_date ASC
";

    $stmt_up = $pdo->prepare($sql_upcoming);
    $stmt_up->execute([$start_date, $end_date]);
    return $stmt_up->fetchAll(PDO::FETCH_ASSOC);
}