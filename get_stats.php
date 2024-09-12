<?php
require 'db.php';

// Fetch total number of players currently playing
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT user_id) AS total_players
    FROM tickets
    WHERE payment_verified = TRUE
");
$stmt->execute();
$total_players = $stmt->fetchColumn();

// Fetch total number of open games
$stmt = $pdo->prepare("
    SELECT COUNT(*) AS total_open_games
    FROM raffle_games
    WHERE status = 'active'
");
$stmt->execute();
$total_open_games = $stmt->fetchColumn();

// Fetch total Bitcoin and USD in combined pools from open games
$stmt = $pdo->prepare("
    SELECT SUM(total_pool) AS total_bitcoin_pools
    FROM raffle_games
    WHERE status = 'active'
");
$stmt->execute();
$total_bitcoin_pools = $stmt->fetchColumn();

// Fetch total Bitcoin won by players
$stmt = $pdo->prepare("
    SELECT SUM(total_pool * 0.70) AS total_bitcoin_won
    FROM raffle_games
    WHERE winner_id IS NOT NULL AND status = 'completed'
");
$stmt->execute();
$total_bitcoin_won = $stmt->fetchColumn();

// Fetch current BTC to USD exchange rate
$btcToUsdRate = 0;
try {
    $response = file_get_contents('https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=usd');
    $data = json_decode($response, true);
    $btcToUsdRate = $data['bitcoin']['usd'];
} catch (Exception $e) {
    $btcToUsdRate = 0; // In case of failure
}

// Calculate USD values
$total_usd_pools = $total_bitcoin_pools * $btcToUsdRate;
$total_usd_won = $total_bitcoin_won * $btcToUsdRate;

// Return data as JSON
echo json_encode([
    'total_players' => $total_players,
    'total_open_games' => $total_open_games,
    'total_bitcoin_pools' => number_format($total_bitcoin_pools, 8),
    'total_usd_pools' => number_format($total_usd_pools, 2),
    'total_bitcoin_won' => number_format($total_bitcoin_won, 8),
    'total_usd_won' => number_format($total_usd_won, 2),
]);
?>