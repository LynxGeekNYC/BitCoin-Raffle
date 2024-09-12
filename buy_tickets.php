<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$game_id = $_GET['game_id'];

// Fetch game details including ticket price
$stmt = $pdo->prepare("SELECT * FROM raffle_games WHERE id = ?");
$stmt->execute([$game_id]);
$game = $stmt->fetch();

$ticket_price_btc = $game['ticket_price']; // Ticket price in BTC

// Cache the BTC to USD exchange rate for 5 minutes
$cache_file = 'btc_usd_rate.json';
$cache_lifetime = 300; // 5 minutes
$btcToUsdRate = 0;

// Check if cache file exists and is still valid
if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_lifetime)) {
    $cache_data = json_decode(file_get_contents($cache_file), true);
    if (isset($cache_data['btcToUsdRate'])) {
        $btcToUsdRate = $cache_data['btcToUsdRate'];
    }
} else {
    // Fetch current BTC to USD exchange rate
    try {
        $response = file_get_contents('https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=usd');
        $data = json_decode($response, true);
        if (isset($data['bitcoin']['usd'])) {
            $btcToUsdRate = $data['bitcoin']['usd'];

            // Save the rate in the cache file
            file_put_contents($cache_file, json_encode(['btcToUsdRate' => $btcToUsdRate]));
        } else {
            echo "Error fetching exchange rate.";
        }
    } catch (Exception $e) {
        echo "Error fetching exchange rate.";
    }
}

// Calculate ticket price in USD
$ticket_price_usd = $btcToUsdRate ? ($ticket_price_btc * $btcToUsdRate) : 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $quantity = intval($_POST['quantity']);
    $txid = trim($_POST['txid']);  // Get the Bitcoin transaction ID

    // Ensure TXID is provided
    if (empty($txid)) {
        echo "Transaction ID is required.";
    } else {
        try {
            // Insert the ticket purchase with the provided TXID
            $stmt = $pdo->prepare("INSERT INTO tickets (user_id, game_id, quantity, txid, payment_verified) VALUES (?, ?, ?, ?, 0)");
            $stmt->execute([$user_id, $game_id, $quantity, $txid]);

            // Redirect to the View Game page after purchase
            header("Location: view_game.php?game_id=$game_id");
            exit();
        } catch (Exception $e) {
            echo "Error inserting ticket: " . $e->getMessage();
        }
    }
}
?>

<?php include 'header.php'; ?>

<h2 class="text-center">Buy Tickets for Game <?= htmlspecialchars($game['game_name']); ?></h2>

<div class="card mb-3">
    <div class="card-header">
        Game Details
    </div>
    <div class="card-body">
        <p><strong>Ticket Price (BTC):</strong> <?= number_format($ticket_price_btc, 8); ?> BTC</p>
        <p><strong>Ticket Price (USD):</strong> <?= number_format($ticket_price_usd, 2); ?> USD</p>
    </div>
</div>

<form method="post">
    <div class="form-group">
        <label for="quantity">Number of Tickets:</label>
        <input type="number" class="form-control" name="quantity" id="quantity" required oninput="updateTotalPrice()">
    </div>
    <div class="form-group">
        <label>Total Price (BTC):</label>
        <input type="text" class="form-control" id="totalPriceBtc" readonly>
    </div>
    <div class="form-group">
        <label>Total Price (USD):</label>
        <input type="text" class="form-control" id="totalPriceUsd" readonly>
    </div>
    
    <!-- Bitcoin TXID Field -->
    <div class="form-group">
        <label for="txid">Bitcoin Transaction ID (TXID):</label>
        <input type="text" class="form-control" name="txid" id="txid" required>
    </div>

    <!-- Display Box for Deposit Instructions -->
    <div class="alert alert-info">
        <p><strong>Please deposit</strong> <span id="totalDisplay"></span> to this Bitcoin wallet: <strong>bc1qslhkrcl2csfcj30qdscnms2z09088l4yetny84</strong></p>
        <p>Make sure to copy the transaction ID (TXID) before purchasing a ticket. If a wrong amount is entered, a refund will be issued, minus any transaction fees from the BlockChain Network.</p>
    </div>

    <!-- QR Code for Bitcoin Payment -->
    <div class="form-group text-center">
        <p>Scan to Pay (coming soon):</p>
        <img id="qrCode" alt="Bitcoin QR Code" />
    </div>

    <!-- Buy Button -->
    <button type="submit" class="btn btn-primary btn-block">Buy Tickets</button>
</form>

<script>
let ticketPriceBtc = <?= $ticket_price_btc ?>;
let ticketPriceUsd = <?= $ticket_price_usd ?>;
let bitcoinAddress = "bc1qslhkrcl2csfcj30qdscnms2z09088l4yetny84";

function updateTotalPrice() {
    const quantity = document.getElementById('quantity').value;
    const totalPriceBtc = (ticketPriceBtc * quantity).toFixed(8);
    const totalPriceUsd = (ticketPriceUsd * quantity).toFixed(2);

    document.getElementById('totalPriceBtc').value = totalPriceBtc + " BTC";
    document.getElementById('totalPriceUsd').value = "$" + totalPriceUsd;

    // Update the display message
    document.getElementById('totalDisplay').innerHTML = totalPriceBtc + " BTC (" + totalPriceUsd + " USD)";

    // Correctly formatted QR code URL
    let qrCodeUrl = `https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=bitcoin:${bitcoinAddress}&amount=${totalPriceBtc}`;
    document.getElementById('qrCode').src = qrCodeUrl;
}
</script>

<?php include 'footer.php'; ?>