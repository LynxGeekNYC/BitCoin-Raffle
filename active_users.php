<?php
require 'db.php';
session_start();


// Fetch active users and their activity data
$stmt = $pdo->prepare("
    SELECT users.id, users.username, users.email,
           (SELECT SUM(tickets.quantity * raffle_games.ticket_price) FROM tickets JOIN raffle_games ON tickets.game_id = raffle_games.id WHERE tickets.user_id = users.id AND tickets.payment_verified = TRUE) AS total_btc_paid,
           (SELECT SUM(tickets.quantity * raffle_games.ticket_price * :btcToUsdRate) FROM tickets JOIN raffle_games ON tickets.game_id = raffle_games.id WHERE tickets.user_id = users.id AND tickets.payment_verified = TRUE) AS total_usd_paid,
           (SELECT SUM(raffle_games.total_pool * 0.70) FROM raffle_games WHERE raffle_games.winner_id = users.id AND raffle_games.status = 'completed') AS total_won_btc,
           (SELECT COUNT(*) FROM raffle_games WHERE raffle_games.user_id = users.id) AS total_games_created,
           (SELECT SUM(raffle_games.total_pool * 0.20) FROM raffle_games WHERE raffle_games.user_id = users.id AND raffle_games.status = 'completed') AS total_creator_earnings
    FROM users
");
$btcToUsdRate = 0;
try {
    // Fetch the current BTC to USD exchange rate
    $response = file_get_contents('https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=usd');
    $data = json_decode($response, true);
    $btcToUsdRate = $data['bitcoin']['usd'];
} catch (Exception $e) {
    echo "Error fetching exchange rate.";
}
$stmt->execute(['btcToUsdRate' => $btcToUsdRate]);
$active_users = $stmt->fetchAll();
?>

<?php include 'header.php'; ?>

<h2 class="text-center">Active Users</h2>

<table class="table table-bordered">
    <thead>
    <tr>
        <th>Username</th>
        <th>Email</th>
        <th>Total BTC Paid</th>
        <th>Total USD Paid</th>
        <th>Total BTC Won</th>
        <th>Games Created</th>
        <th>Total Creator Earnings (20%)</th>
    </tr>
    </thead>
    <tbody>
        <?php foreach ($active_users as $user): ?>
            <tr>
                <td><?= $user['username']; ?></td>
                <td><?= $user['email']; ?></td>
                <td><?= number_format($user['total_btc_paid'], 8); ?> BTC</td>
                <td><?= number_format($user['total_usd_paid'], 2); ?> USD</td>
                <td><?= number_format($user['total_won_btc'], 8); ?> BTC</td>
                <td><?= $user['total_games_created']; ?></td>
                <td><?= number_format($user['total_creator_earnings'], 8); ?> BTC</td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'footer.php'; ?>