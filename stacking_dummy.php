<?php
ob_start();
session_start();
require('header.php');

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the user's current balance and random string
$stmt = $conn->prepare("SELECT SUM(deposits.amount) AS wallet_balance, users.random_string 
                        FROM deposits 
                        JOIN users ON deposits.user_id = users.id 
                        WHERE deposits.user_id = ? AND deposits.status = 'accepted'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$wallet_balance = $result['wallet_balance'] ?? 0;
$stored_random_string = $result['random_string'];
$stmt->close();

// Initialize variables for success and error messages
$success_message = $error_message = "";

// Function to calculate earnings based on the day number
function calculate_daily_earning($day, $amount)
{
    $percentages = [0.0045, 0.0055, 0.0065];
    return $amount * $percentages[$day % 3];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['stake_amount'], $_POST['random_string'])) {
        $stake_amount = $_POST['stake_amount'];
        $input_random_string = $_POST['random_string'];

        if ($input_random_string !== $stored_random_string) {
            $error_message = "You have provided an incorrect random string.";
        } elseif ($stake_amount > 0 && $stake_amount <= $wallet_balance) {
            $estimated_earning = 3 * $stake_amount;
            $remaining_earning = $estimated_earning;

            // Insert staking record
            $stmt = $conn->prepare("INSERT INTO stakings (user_id, amount, estimated_earning, remaining_earning, status) VALUES (?, ?, ?, ?, 'active')");
            $stmt->bind_param("iddd", $user_id, $stake_amount, $estimated_earning, $remaining_earning);
            $stmt->execute();
            $stmt->close();

            // Deduct the staked amount from the user's balance
            $stmt = $conn->prepare("UPDATE deposits SET amount = amount - ? WHERE user_id = ? AND status = 'accepted'");
            $stmt->bind_param("di", $stake_amount, $user_id);
            $stmt->execute();
            $stmt->close();

            $success_message = "Successfully staked $" . htmlspecialchars(number_format($stake_amount, 2)) . ".";

            // Redirect to prevent form resubmission
            header("Location: stacking_dummy.php");
            exit();
        } else {
            $error_message = "Invalid staking amount.";
        }
    } elseif (isset($_POST['claim_now'])) {
        // Handle the "Claim Now" button click
        // Fetch the total earning from the staking records
        $stmt = $conn->prepare("SELECT SUM(total_earning) AS total_earning FROM stakings WHERE user_id = ? AND status = 'active'");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $total_earning = $stmt->get_result()->fetch_assoc()['total_earning'] ?? 0;
        $stmt->close();

        if ($total_earning > 0) {
            // Update the user's wallet balance
            $stmt = $conn->prepare("UPDATE deposits SET amount = amount + ? WHERE user_id = ? AND status = 'accepted'");
            $stmt->bind_param("di", $total_earning, $user_id);
            $stmt->execute();
            $stmt->close();

            // Update staking records - only reset total_earning, not remaining_earning
            $stmt = $conn->prepare("UPDATE stakings SET total_earning = 0 WHERE user_id = ? AND status = 'active'");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();

            // Recalculate the wallet balance
            $stmt = $conn->prepare("SELECT SUM(amount) AS wallet_balance FROM deposits WHERE user_id = ? AND status = 'accepted'");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $wallet_balance = $stmt->get_result()->fetch_assoc()['wallet_balance'] ?? 0;
            $stmt->close();

            $success_message = "Successfully claimed $" . htmlspecialchars(number_format($total_earning, 2)) . " to your wallet.";
        } else {
            $error_message = "No earnings available to claim.";
        }

        // Redirect to prevent form resubmission
        header("Location: stacking_dummy.php");
        exit();
    }
}

// Fetch staking records
$staking_records = [];
$stmt = $conn->prepare("SELECT * FROM stakings WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $staking_records[] = $row;
}
$stmt->close();

// Fetch daily earnings records
$daily_earnings_records = [];
$stmt = $conn->prepare("SELECT * FROM daily_earnings WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $daily_earnings_records[] = $row;
}
$stmt->close();

// Calculate total staking amount
$total_staking_amount = 0;
foreach ($staking_records as $record) {
    $total_staking_amount += $record['amount'];
}

// Calculate total earning amount
$total_earning_amount = 0;
foreach ($staking_records as $record) {
    $total_earning_amount += $record['total_earning'];
}

// Calculate total remaining earning amount
$total_remaining_earning = 0;
foreach ($staking_records as $record) {
    $remaining_earning = $record['remaining_earning'];
    $estimated_earning = $record['estimated_earning'];

    // Ensure remaining earnings do not exceed the estimated earnings
    if ($remaining_earning > $estimated_earning) {
        $remaining_earning = $estimated_earning;
    }

    // Ensure remaining earnings do not go below zero
    if ($remaining_earning < 0) {
        $remaining_earning = 0;
    }

    $total_remaining_earning += $remaining_earning;
}

// Disable claim button and daily earnings form if total remaining earning is zero
$claim_button_disabled = $total_remaining_earning <= 0;
$daily_earnings_disabled = $total_remaining_earning <= 0;

// Prevent insertion of new daily earnings records if remaining earnings are zero
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$daily_earnings_disabled) {
    // Example logic for inserting a new daily earnings record
    $current_day = date('j'); // Day of the month
    $daily_earning_amount = calculate_daily_earning($current_day, $total_remaining_earning);

    if ($daily_earning_amount > 0) {
        // Insert daily earnings record
        $stmt = $conn->prepare("INSERT INTO daily_earnings (user_id, amount, date) VALUES (?, ?, NOW())");
        $stmt->bind_param("id", $user_id, $daily_earning_amount);
        $stmt->execute();
        $stmt->close();
    }
}
?>

<style>
    th,
    td {
        color: white;
    }
</style>

<div class="container">

    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-12">
                        <h3 class="fs-5 mb-3">On Stacking</h3>
                        <?php
                        // Check if there are any staking records and sum their amounts, otherwise set to 0
                        $total_staking_amount = 0;
                        if (!empty($staking_records)) {
                            foreach ($staking_records as $record) {
                                $total_staking_amount += $record['amount'];
                            }
                        }
                        ?>
                        <h2 class="display-5 mb-4" style="margin-top: -15px;">
                            <?php echo '$' . htmlspecialchars(number_format($total_staking_amount, 2)); ?>
                        </h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-12">
                        <h3 class="fs-5 mb-3">Estimated Earning</h3>
                        <?php
                        $total_estimated_earning = 0;
                        foreach ($staking_records as $record) {
                            $total_estimated_earning += $record['estimated_earning'];
                        }
                        ?>
                        <h1 class="display-5 mb-4" style="margin-top: -15px;">
                            $<?php echo htmlspecialchars(number_format($total_estimated_earning, 2)); ?>
                        </h1>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-12">
                        <h3 class="fs-5 mb-3">Remaining Earning</h3>
                        <h1 class="display-5 mb-4" style="margin-top: -15px;">
                            $<?php echo htmlspecialchars(number_format($total_remaining_earning, 2)); ?>
                        </h1>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-12">
                        <h3 class="fs-5 mb-3">Daily Earning</h3>
                        <?php if (!$daily_earnings_disabled): ?>
                            <form method="post" action="stacking_dummy.php">
                                <button type="submit" name="claim_now" class="btn btn-success" <?php echo $claim_button_disabled ? 'disabled' : ''; ?>>Claim Now</button>
                            </form>
                        <?php else: ?>
                            <p>Daily earnings are not available!!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h3>Daily Earnings Records</h3>
    <table class="table table-responsive">
        <thead>
            <tr>
                <th>ID</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($daily_earnings_records as $record) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($record['id']); ?></td>
                    <td><?php echo "$" . htmlspecialchars(number_format($record['amount'], 2)); ?></td>
                    <td><?php echo htmlspecialchars($record['date']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require('footer.php'); ?>
