<?php
require 'db.php';
session_start();

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current games
$stmt = $pdo->prepare("
    SELECT raffle_games.*, users.username AS creator_username, 
    (SELECT COUNT(*) FROM tickets WHERE game_id = raffle_games.id AND payment_verified = TRUE) AS player_count,
    (SELECT SUM(quantity * ticket_price) FROM tickets WHERE game_id = raffle_games.id AND payment_verified = TRUE) AS total_pool
    FROM raffle_games 
    JOIN users ON raffle_games.user_id = users.id
    WHERE raffle_games.status = 'active'
");
$stmt->execute();
$games = $stmt->fetchAll();

// Fetch current BTC to USD exchange rate
$btcToUsdRate = 0;
try {
    $response = file_get_contents('https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=usd');
    $data = json_decode($response, true);
    $btcToUsdRate = $data['bitcoin']['usd'];
} catch (Exception $e) {
    echo "Error fetching exchange rate.";
}

// Check for manual game stop
if (isset($_GET['stop_game'])) {
    $game_id = $_GET['stop_game'];
    $stmt = $pdo->prepare("UPDATE raffle_games SET status = 'completed' WHERE id = ? AND user_id = ? AND timed_event = 0");
    $stmt->execute([$game_id, $user_id]);
    header("Location: dashboard.php");
    exit();
}
?>

<?php include 'header.php'; ?>

<h2 class="text-center">Current Raffle Games</h2>

<table class="table table-bordered">
    <thead>
    <tr>
        <th>Game Name</th>
        <th>Ticket Price (BTC / USD)</th>
        <th>Total Pool (BTC)</th>
        <th>Players</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($games as $game): 
        $totalPool = $game['total_pool'] ? $game['total_pool'] : 0;
        $usdPrice = $game['ticket_price'] * $btcToUsdRate;
    ?>
        <tr>
            <td><?= htmlspecialchars($game['game_name']); ?></td>
            <td>
                <?= number_format($game['ticket_price'], 8); ?> BTC 
                (<?= number_format($usdPrice, 2); ?> USD)
            </td>
            <td><?= number_format($totalPool, 8); ?> BTC</td>
            <td><?= $game['player_count']; ?></td>
            <td>
                <a href="buy_tickets.php?game_id=<?= $game['id']; ?>" class="btn btn-primary">Join Game</a>
                <a href="view_game.php?game_id=<?= $game['id']; ?>" class="btn btn-info">View Game</a>
                
                <!-- Add Stop Game button for non-timed games -->
                <?php if ($game['timed_event'] == 0 && $game['status'] == 'active'): ?>
                    <a href="dashboard.php?stop_game=<?= $game['id']; ?>" class="btn btn-danger">Stop Game</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include 'footer.php'; ?>