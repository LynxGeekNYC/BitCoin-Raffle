<?php
require 'db.php';

$stmt = $pdo->prepare("INSERT INTO raffle_games (start_time, end_time, total_pool, status) VALUES (NOW(), NOW() + INTERVAL 1 DAY, 0, 'active')");
$stmt->execute();
?>