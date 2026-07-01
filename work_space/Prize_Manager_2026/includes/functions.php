<?php
// includes/functions.php

/**
 * ソートリンクのURLと矢印表示を生成する
 */
function getSortLink($column, $current_sort, $current_order) {
    $next_order = ($current_sort === $column && $current_order === 'ASC') ? 'DESC' : 'ASC';
    return "?sort={$column}&order={$next_order}";
}

/**
 * ソート状態に応じた矢印を返す
 */
function getSortArrow($column, $current_sort, $current_order) {
    if ($current_sort !== $column) return '';
    return ($current_order === 'ASC') ? ' ▲' : ' ▼';
}