<?php
require 'db.php';
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}

// Fetch tickets with pending payment verification
$stmt = $pdo->prepare("
    SELECT tickets.*, users.username, users.email, raffle_games.game_name, raffle_games.ticket_price
    FROM tickets
    JOIN users ON tickets.user_id = users.id
    JOIN raffle_games ON tickets.game_id = raffle_games.id
    WHERE tickets.payment_verified = 0
");
$stmt->execute();
$tickets = $stmt->fetchAll();

if (!$tickets) {
    echo "No payments to verify.";
    exit();
}

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

<?php include 'header.php'; ?>

<h2 class="text-center">Verify Pending Payments</h2>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Game Name</th>
            <th>User</th>
            <th>Email</th>
            <th>Number of Tickets</th>
            <th>Ticket Price (BTC / USD)</th>
            <th>Total (BTC / USD)</th>
            <th>Transaction ID</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tickets as $ticket): 
            $totalPriceBtc = $ticket['quantity'] * $ticket['ticket_price'];
            $ticketPriceUsd = $ticket['ticket_price'] * $btcToUsdRate;
            $totalPriceUsd = $totalPriceBtc * $btcToUsdRate;
        ?>
            <tr>
                <td><?= htmlspecialchars($ticket['game_name']); ?></td>
                <td><?= htmlspecialchars($ticket['username']); ?></td>
                <td><?= htmlspecialchars($ticket['email']); ?></td>
                <td><?= $ticket['quantity']; ?></td>
                <td><?= number_format($ticket['ticket_price'], 8); ?> BTC / <?= number_format($ticketPriceUsd, 2); ?> USD</td>
                <td><?= number_format($totalPriceBtc, 8); ?> BTC / <?= number_format($totalPriceUsd, 2); ?> USD</td>
                <td>
                    <a href="https://www.blockchain.com/btc/tx/<?= htmlspecialchars($ticket['txid']); ?>" target="_blank">
                        <?= htmlspecialchars($ticket['txid']); ?>
                    </a>
                </td>
                <td>
                    <!-- Verify Payment Button -->
                    <a href="verify.php?payment_id=<?= $ticket['id']; ?>" class="btn btn-success">Verify Payment</a>

                    <!-- Not Verified Button (Reject Payment) -->
                    <a href="reject_payment.php?payment_id=<?= $ticket['id']; ?>" class="btn btn-danger">Not Verified</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Add a button to return to dashboard -->
<div class="text-center mt-3">
    <a href="dashboard.php" class="btn btn-primary">Return to Dashboard</a>
</div>

<?php include 'footer.php'; ?>