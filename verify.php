<?php
require 'db.php';

if (isset($_GET['payment_id'])) {
    $payment_id = $_GET['payment_id'];

    // Fetch the ticket that is awaiting verification
    $stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ? AND payment_verified = 0");
    $stmt->execute([$payment_id]);
    $ticket = $stmt->fetch();

    if ($ticket) {
        // Mark the payment as verified
        $stmt = $pdo->prepare("UPDATE tickets SET payment_verified = 1 WHERE id = ?");
        $stmt->execute([$payment_id]);

        echo "<script>
                alert('Payment Verified!');
                window.location.href='admin_verify_payments.php';
              </script>";
    } else {
        echo "Invalid or expired payment verification link.";
    }
} else {
    echo "Invalid request.";
}
?>