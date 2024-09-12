<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch completed games
$stmt = $pdo->prepare("
    SELECT raffle_games.*, 
           winner.username AS winner_username, 
           winner.wallet_address AS winner_wallet, 
           creator.username AS creator_username, 
           creator.wallet_address AS creator_wallet, 
           raffle_games.winner_paid_at, raffle_games.creator_paid_at
    FROM raffle_games
    LEFT JOIN users AS winner ON raffle_games.winner_id = winner.id 
    LEFT JOIN users AS creator ON raffle_games.user_id = creator.id 
    WHERE raffle_games.status = 'completed'
");
$stmt->execute();
$games = $stmt->fetchAll();
?>

<?php include 'header.php'; ?>

<h2 class="text-center">Prior Games Played</h2>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Game ID</th>
            <th>Winner</th>
            <th>Winner Wallet</th>
            <th>Creator</th>
            <th>Creator Wallet</th>
            <th>Total Pool (BTC)</th>
            <th>Winner Payout (70%)</th>
            <th>Creator Payout (20%)</th>
            <th>Winner Paid Status</th>
            <th>Creator Paid Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($games as $game): ?>
            <?php
            $total_pool = $game['total_pool'];
            $winner_payout = $total_pool * 0.70;
            $creator_payout = $total_pool * 0.20;
            ?>
            <tr>
                <td><?= $game['id']; ?></td>
                <td><?= $game['winner_username'] ?: 'N/A'; ?></td>
                <td><?= $game['winner_wallet'] ?: 'N/A'; ?></td>
                <td><?= $game['creator_username']; ?></td>
                <td><?= $game['creator_wallet']; ?></td>
                <td><?= number_format($total_pool, 8); ?> BTC</td>
                <td><?= number_format($winner_payout, 8); ?> BTC</td>
                <td><?= number_format($creator_payout, 8); ?> BTC</td>
                <td>
                    <?php if ($game['winner_paid_at']): ?>
                        Paid on <?= date('Y-m-d H:i:s', strtotime($game['winner_paid_at'])); ?>
                    <?php else: ?>
                        Not Paid
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($game['creator_paid_at']): ?>
                        Paid on <?= date('Y-m-d H:i:s', strtotime($game['creator_paid_at'])); ?>
                    <?php else: ?>
                        Not Paid
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'footer.php'; ?>