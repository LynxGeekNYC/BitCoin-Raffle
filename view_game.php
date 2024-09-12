<?php
require 'db.php';
include 'header.php';

$game_id = $_GET['game_id'];

// Fetch game details
$stmt = $pdo->prepare("
    SELECT raffle_games.*, 
           (SELECT SUM(quantity) FROM tickets WHERE game_id = raffle_games.id AND payment_verified = TRUE) AS total_tickets,
           (SELECT SUM(quantity * ticket_price) FROM tickets WHERE game_id = raffle_games.id AND payment_verified = TRUE) AS total_pool,
           users.username AS creator_username
    FROM raffle_games 
    JOIN users ON raffle_games.user_id = users.id
    WHERE raffle_games.id = ?
");
$stmt->execute([$game_id]);
$game = $stmt->fetch();

// Fetch players and their ticket counts
$stmt = $pdo->prepare("
    SELECT users.username, SUM(tickets.quantity) AS total_tickets 
    FROM tickets 
    JOIN users ON tickets.user_id = users.id 
    WHERE tickets.game_id = ? AND tickets.payment_verified = TRUE
    GROUP BY users.username
");
$stmt->execute([$game_id]);
$players = $stmt->fetchAll();

// Fetch current BTC to USD exchange rate
$btcToUsdRate = 0;
try {
    $response = file_get_contents('https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=usd');
    $data = json_decode($response, true);
    $btcToUsdRate = $data['bitcoin']['usd'];
} catch (Exception $e) {
    echo "Error fetching exchange rate.";
}
?>

<h2 class="text-center">Game Details for <?= htmlspecialchars($game['game_name']); ?></h2>

<p><strong>Created by:</strong> <?= htmlspecialchars($game['creator_username']); ?></p>
<p><strong>Total Pool:</strong> <?= number_format($game['total_pool'], 8); ?> BTC (<?= number_format($game['total_pool'] * $btcToUsdRate, 2); ?> USD)</p>
<p><strong>Total Tickets Bought (Combined):</strong> <?= $game['total_tickets']; ?></p>

<h3>Players</h3>
<table class="table table-bordered">
    <thead>
    <tr>
        <th>Player</th>
        <th>Total Tickets</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($players as $player): ?>
        <tr>
            <td><?= htmlspecialchars($player['username']); ?></td>
            <td><?= $player['total_tickets']; ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include 'footer.php'; ?>