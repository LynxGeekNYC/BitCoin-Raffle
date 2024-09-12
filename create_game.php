<?php
require 'db.php';
session_start();

// Error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if the BTC to USD rate is already cached in the session
if (isset($_SESSION['btcToUsdRate']) && isset($_SESSION['btcRateTimestamp'])) {
    // Use cached rate if it was retrieved within the last 10 minutes
    if (time() - $_SESSION['btcRateTimestamp'] < 600) {
        $btcToUsdRate = $_SESSION['btcToUsdRate'];
    } else {
        unset($_SESSION['btcToUsdRate']);
        unset($_SESSION['btcRateTimestamp']);
    }
}

if (!isset($btcToUsdRate)) {
    // Fetch the BTC to USD exchange rate if not cached
    try {
        $response = @file_get_contents('https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=usd');
        if ($response === FALSE) {
            throw new Exception('API request failed. Using fallback rate.');
        }

        $data = json_decode($response, true);
        $btcToUsdRate = $data['bitcoin']['usd'] ?? null;

        if ($btcToUsdRate === null) {
            throw new Exception('Error processing API response. Using fallback rate.');
        }

        // Cache the rate and timestamp in the session
        $_SESSION['btcToUsdRate'] = $btcToUsdRate;
        $_SESSION['btcRateTimestamp'] = time();
    } catch (Exception $e) {
        // Fallback rate in case of failure
        $btcToUsdRate = 25000; // Use a static fallback value (adjust as needed)
        echo "Error: " . $e->getMessage();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        if (!empty($_POST['usdAmount'])) {
            $usdAmount = floatval($_POST['usdAmount']);
            $btcAmount = $usdAmount / $btcToUsdRate;
        } else {
            $btcAmount = floatval($_POST['btcAmount']);
        }

        $min_players = $_POST['min_players'];
        $game_name = $_POST['game_name'];
        $timed_event = isset($_POST['timed_event']) ? 1 : 0;
        $end_time = !empty($_POST['end_time']) ? $_POST['end_time'] : NULL;

        $stmt = $pdo->prepare("INSERT INTO raffle_games (user_id, ticket_price, min_players, game_name, timed_event, end_time) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $btcAmount, $min_players, $game_name, $timed_event, $end_time]);

        header("Location: dashboard.php");
        exit();
    } catch (PDOException $e) {
        echo "Error creating game: " . $e->getMessage();
    }
}
?>

<?php include 'header.php'; ?>

<h2 class="text-center">Create a New Raffle Game</h2>

<form method="post">
    <div class="form-group">
        <label for="game_name">Game Name / Description:</label>
        <input type="text" class="form-control" id="game_name" name="game_name" placeholder="Enter game name or description" required>
    </div>
    <div class="form-group">
        <label for="usdAmount">Ticket Price in USD:</label>
        <input type="number" class="form-control" id="usdAmount" name="usdAmount" oninput="convertCurrency('usd')" placeholder="Enter USD price" step="0.01">
    </div>
    <div class="form-group">
        <label for="btcAmount">Ticket Price in BTC:</label>
        <input type="number" class="form-control" id="btcAmount" name="btcAmount" oninput="convertCurrency('btc')" placeholder="Enter BTC price" step="0.00000001">
    </div>
    <div class="form-group">
        <label for="min_players">Minimum Players:</label>
        <input type="number" class="form-control" name="min_players" id="min_players" required>
    </div>
    <div class="form-group">
        <label for="timed_event">Timed Event:</label>
        <input type="checkbox" name="timed_event" id="timed_event" value="1">
    </div>
    <div class="form-group">
        <label for="end_time">End Time (optional for timed events):</label>
        <input type="datetime-local" class="form-control" name="end_time" id="end_time">
    </div>

    <button type="submit" class="btn btn-primary btn-block">Create Game</button>
</form>

<script>
let btcToUsdRate = <?= $btcToUsdRate ?>;

function convertCurrency(inputType) {
    const usdInput = document.getElementById('usdAmount');
    const btcInput = document.getElementById('btcAmount');
    const btcAmountText = document.getElementById('btcAmountText');

    if (inputType === 'usd' && btcToUsdRate > 0) {
        const usdValue = parseFloat(usdInput.value);
        const btcValue = (usdValue / btcToUsdRate).toFixed(8);
        btcInput.value = btcValue;
        btcAmountText.innerText = btcValue;
    } else if (inputType === 'btc' && btcToUsdRate > 0) {
        const btcValue = parseFloat(btcInput.value);
        const usdValue = (btcValue * btcToUsdRate).toFixed(2);
        usdInput.value = usdValue;
        btcAmountText.innerText = btcValue.toFixed(8);
    }
}
</script>

<?php include 'footer.php'; ?>