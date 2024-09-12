<?php
session_start();

try {
    // Ensure that the user is logged in and is an admin
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
        throw new Exception('Access denied: You are not an admin.');
    }

    // Database connection
    require 'db.php';

    // Pagination settings
    $items_per_page = 10;

    // Active Users Pagination
    $page_active_users = isset($_GET['page_active_users']) ? (int)$_GET['page_active_users'] : 1;
    $offset_active_users = ($page_active_users - 1) * $items_per_page;
    
    $stmt_active_users = $pdo->prepare("SELECT * FROM users LIMIT ?, ?");
    $stmt_active_users->bindParam(1, $offset_active_users, PDO::PARAM_INT);
    $stmt_active_users->bindParam(2, $items_per_page, PDO::PARAM_INT);
    $stmt_active_users->execute();
    $active_users = $stmt_active_users->fetchAll();

    $total_active_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $total_pages_active_users = ceil($total_active_users / $items_per_page);

    // Payouts Pagination
    $page_payouts = isset($_GET['page_payouts']) ? (int)$_GET['page_payouts'] : 1;
    $offset_payouts = ($page_payouts - 1) * $items_per_page;
    
    $stmt_payouts = $pdo->prepare("SELECT * FROM payouts LIMIT ?, ?");
    $stmt_payouts->bindParam(1, $offset_payouts, PDO::PARAM_INT);
    $stmt_payouts->bindParam(2, $items_per_page, PDO::PARAM_INT);
    $stmt_payouts->execute();
    $payouts = $stmt_payouts->fetchAll();

    $total_payouts = $pdo->query("SELECT COUNT(*) FROM payouts")->fetchColumn();
    $total_pages_payouts = ceil($total_payouts / $items_per_page);

    // Payment Verification Pagination
    $page_verifications = isset($_GET['page_verifications']) ? (int)$_GET['page_verifications'] : 1;
    $offset_verifications = ($page_verifications - 1) * $items_per_page;
    
    $stmt_verifications = $pdo->prepare("SELECT * FROM verifications LIMIT ?, ?");
    $stmt_verifications->bindParam(1, $offset_verifications, PDO::PARAM_INT);
    $stmt_verifications->bindParam(2, $items_per_page, PDO::PARAM_INT);
    $stmt_verifications->execute();
    $verifications = $stmt_verifications->fetchAll();

    $total_verifications = $pdo->query("SELECT COUNT(*) FROM verifications")->fetchColumn();
    $total_pages_verifications = ceil($total_verifications / $items_per_page);

} catch (Exception $e) {
    // If there is any error, display a user-friendly message and log the detailed error
    error_log("Admin Page Error: " . $e->getMessage());
    die("An error occurred while loading the admin page. Please try again later.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            background-color: purple;
            color: white;
        }
        a {
            color: #ffc107;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .pagination a {
            color: white;
            padding: 8px 16px;
            text-decoration: none;
        }
        .pagination a:hover {
            background-color: #ffc107;
            color: purple;
        }
    </style>
</head>
<body>
    <h1>Admin Dashboard</h1>

    <!-- Active Users Section -->
    <h2>Active Users</h2>
    <table>
      <thead>
        <tr>
          <th>Username</th>
          <th>Email</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($active_users as $user): ?>
          <tr>
            <td><?= htmlspecialchars($user['username']); ?></td>
            <td><?= htmlspecialchars($user['email']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Active Users Pagination -->
    <div class="pagination">
      <?php for ($i = 1; $i <= $total_pages_active_users; $i++): ?>
        <a href="admin.php?page_active_users=<?= $i; ?>"><?= $i; ?></a>
      <?php endfor; ?>
    </div>

    <!-- Admin Payouts Section -->
    <h2>Admin Payouts</h2>
    <table>
      <thead>
        <tr>
          <th>Username</th>
          <th>Amount</th>
          <th>Payment Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($payouts as $payout): ?>
          <tr>
            <td><?= htmlspecialchars($payout['username']); ?></td>
            <td><?= htmlspecialchars($payout['amount']); ?></td>
            <td><?= htmlspecialchars($payout['payment_status']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Payouts Pagination -->
    <div class="pagination">
      <?php for ($i = 1; $i <= $total_pages_payouts; $i++): ?>
        <a href="admin.php?page_payouts=<?= $i; ?>"><?= $i; ?></a>
      <?php endfor; ?>
    </div>

    <!-- Admin Verify Payments Section -->
    <h2>Verify Payments</h2>
    <table>
      <thead>
        <tr>
          <th>Transaction ID</th>
          <th>User</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($verifications as $verification): ?>
          <tr>
            <td><?= htmlspecialchars($verification['transaction_id']); ?></td>
            <td><?= htmlspecialchars($verification['user']); ?></td>
            <td><?= htmlspecialchars($verification['status']); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Verify Payments Pagination -->
    <div class="pagination">
      <?php for ($i = 1; $i <= $total_pages_verifications; $i++): ?>
        <a href="admin.php?page_verifications=<?= $i; ?>"><?= $i; ?></a>
      <?php endfor; ?>
    </div>

</body>
</html>