<?php
require 'db.php';

if (isset($_GET['payment_id'])) {
    $payment_id = $_GET['payment_id'];

    // Fetch the ticket details
    $stmt = $pdo->prepare("
        SELECT tickets.*, users.email, users.username
        FROM tickets 
        JOIN users ON tickets.user_id = users.id
        WHERE tickets.id = ? AND tickets.payment_verified = 0
    ");
    $stmt->execute([$payment_id]);
    $ticket = $stmt->fetch();

    if ($ticket) {
        // Remove the ticket from the game or mark it as rejected
        $stmt = $pdo->prepare("DELETE FROM tickets WHERE id = ?");
        $stmt->execute([$payment_id]);

        // Send email to user notifying them of rejection
        $to = $ticket['email'];
        $subject = "Payment Not Verified";
        $message = "Dear " . htmlspecialchars($ticket['username']) . ",\n\n"
                 . "We regret to inform you that your payment for the game '"
                 . htmlspecialchars($ticket['game_name']) . "' has not been verified. "
                 . "If the amount was invalid, a refund will be issued to your wallet.\n\n"
                 . "Please contact support for more details.\n\nThank you.";
        $headers = "From: no-reply@yourdomain.com";
        mail($to, $subject, $message, $headers);

        // Redirect back to admin payment verification page
        echo "<script>
                alert('Payment rejected. User has been notified.');
                window.location.href = 'admin_verify_payments.php';
              </script>";
    } else {
        echo "Invalid or expired payment rejection link.";
    }
} else {
    echo "Invalid request.";
}
?>