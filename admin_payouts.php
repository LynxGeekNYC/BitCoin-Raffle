<?php
require 'db.php';
session_start();

// Ensure the user is an admin
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header("Location: dashboard.php");
    exit();
}

// Fetch all completed games along with winner and creator wallet addresses
$stmt = $pdo->prepare("
    SELECT raffle_games.*, 
           winner.username AS winner_username, 
           winner.wallet_address AS winner_wallet, 
           creator.username AS creator_username, 
           creator.wallet_address AS creator_wallet 
    FROM raffle_games 
    LEFT JOIN users AS winner ON raffle_games.winner_id = winner.id 
    LEFT JOIN users AS creator ON raffle_games.user_id = creator.id 
    WHERE raffle_games.status = 'completed'
");
$stmt->execute();
$games = $stmt->fetchAll();

// Handle payment updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $game_id = $_POST['game_id'];
    $column = $_POST['column'];

    if ($column == 'winner_paid') {
        $stmt = $pdo->prepare("UPDATE raffle_games SET winner_paid_at = NOW() WHERE id = ?");
        $stmt->execute([$game_id]);
    }

    if ($column == 'creator_paid') {
        $stmt = $pdo->prepare("UPDATE raffle_games SET creator_paid_at = NOW() WHERE id = ?");
        $stmt->execute([$game_id]);
    }

    header("Location: admin_payouts.php");
    exit();
}
?>

<?php include 'header.php'; ?>

<h2 class="text-center">Admin Payouts - Completed Games</h2>

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
            <th>Actions</th>
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
                    <form method="post" class="d-inline">
                        <input type="hidden" name="game_id" value="<?= $game['id']; ?>">
                        <?php if (!$game['winner_paid_at']): ?>
                            <button type="submit" name="column" value="winner_paid" class="btn btn-success">Mark Winner Paid</button>
                        <?php else: ?>
                            <span class="text-success">Paid</span>
                        <?php endif; ?>
                    </form>
                    <form method="post" class="d-inline">
                        <input type="hidden" name="game_id" value="<?= $game['id']; ?>">
                        <?php if (!$game['creator_paid_at']): ?>
                            <button type="submit" name="column" value="creator_paid" class="btn btn-success">Mark Creator Paid</button>
                        <?php else: ?>
                            <span class="text-success">Paid</span>
                        <?php endif; ?>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'footer.php'; ?>