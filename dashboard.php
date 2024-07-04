<?php
require('header.php');
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch user-specific data
$stmt = $conn->prepare("SELECT * FROM rewards WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_rewards = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch the user's referral code
$stmt = $conn->prepare("SELECT referral_code FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_referral = $stmt->get_result()->fetch_assoc();
$stmt->close();

$referral_code = $user_referral['referral_code'];
$referral_link = SITE_PATH . "/signup.php?referral=" . $referral_code;

// Fetch the user's transaction status
$stmt = $conn->prepare("SELECT status FROM transactions WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$transaction_status_row = $stmt->get_result()->fetch_assoc();
$transaction_status = $transaction_status_row['status'] ?? null;
$stmt->close();

// Fetch the latest deposit status
$stmt = $conn->prepare("SELECT status FROM deposits WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$deposit_status_row = $stmt->get_result()->fetch_assoc();
$deposit_status = $deposit_status_row['status'] ?? null;
$stmt->close();

// Calculate the wallet balance (sum of accepted deposits)
$stmt = $conn->prepare("SELECT SUM(amount) AS wallet_balance FROM deposits WHERE user_id = ? AND status = 'accepted'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$wallet_balance_row = $stmt->get_result()->fetch_assoc();
$wallet_balance = $wallet_balance_row['wallet_balance'] ?? 0;
$stmt->close();

// Handle form submission for new deposits
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['amount'])) {
    $amount = $_POST['amount'];
    $screenshot = $_FILES['screenshot']['name'];
    move_uploaded_file($_FILES['screenshot']['tmp_name'], 'upload/' . $screenshot);

    $stmt = $conn->prepare("INSERT INTO deposits (user_id, amount, screenshot, status) VALUES (?, ?, ?, 'pending')");
    $stmt->bind_param("ids", $user_id, $amount, $screenshot);
    $stmt->execute();
    $stmt->close();

    // Update transaction status to pending when a new deposit is made
    $transaction_status = 'pending';
}


//Staking All Code




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

// Handle the staking form submission
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
            $staking_id = $stmt->insert_id; // Get the ID of the newly inserted staking record
            $stmt->close();

            // Deduct the staked amount from the user's balance
            $remaining_to_deduct = $stake_amount;
            while ($remaining_to_deduct > 0) {
                $stmt = $conn->prepare("SELECT id, amount FROM deposits WHERE user_id = ? AND status = 'accepted' AND amount > 0 ORDER BY id ASC LIMIT 1");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $deposit = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($deposit) {
                    $deposit_id = $deposit['id'];
                    $deposit_amount = $deposit['amount'];

                    if ($deposit_amount >= $remaining_to_deduct) {
                        $stmt = $conn->prepare("UPDATE deposits SET amount = amount - ? WHERE id = ?");
                        $stmt->bind_param("di", $remaining_to_deduct, $deposit_id);
                        $stmt->execute();
                        $stmt->close();
                        $remaining_to_deduct = 0;
                    } else {
                        $stmt = $conn->prepare("UPDATE deposits SET amount = 0 WHERE id = ?");
                        $stmt->bind_param("i", $deposit_id);
                        $stmt->execute();
                        $stmt->close();
                        $remaining_to_deduct -= $deposit_amount;
                    }
                } else {
                    break; // No more deposits to deduct from
                }
            }

            // Calculate the first day's earnings and insert the record
            $first_daily_earning = calculate_daily_earning(0, $stake_amount);
            $stmt = $conn->prepare("INSERT INTO daily_earnings (user_id, staking_id, date, amount) VALUES (?, ?, CURDATE(), ?)");
            $stmt->bind_param("iid", $user_id, $staking_id, $first_daily_earning);
            $stmt->execute();
            $stmt->close();

            // Update total earned and remaining earning in stakings table
            $remaining_earning -= $first_daily_earning;
            $stmt = $conn->prepare("UPDATE stakings SET total_earning = ?, remaining_earning = ? WHERE id = ?");
            $stmt->bind_param("dii", $first_daily_earning, $remaining_earning, $staking_id);
            $stmt->execute();
            $stmt->close();

            // Recalculate the wallet balance
            $stmt = $conn->prepare("SELECT SUM(amount) AS wallet_balance FROM deposits WHERE user_id = ? AND status = 'accepted'");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $wallet_balance = $stmt->get_result()->fetch_assoc()['wallet_balance'] ?? 0;
            $stmt->close();

            $success_message = "Successfully staked $" . htmlspecialchars(number_format($stake_amount, 2)) . " and earned $" . htmlspecialchars(number_format($first_daily_earning, 2)) . " on the first day.";

            // Redirect to prevent form resubmission
            header("Location: staking.php");
            exit(); // Ensure script termination after redirection
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

            // Reset the total earning in the staking records
            $stmt = $conn->prepare("UPDATE stakings SET total_earning = 0, remaining_earning = 0 WHERE user_id = ? AND status = 'active'");
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
        header("Location: staking.php");
        exit(); // Ensure script termination after redirection
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





// Display Refer Code Here



// Fetch user-specific data
$stmt = $conn->prepare("SELECT * FROM rewards WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_rewards = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch the user's referral code
$stmt = $conn->prepare("SELECT referral_code FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_referral = $stmt->get_result()->fetch_assoc();
$stmt->close();

$referral_code = $user_referral['referral_code'];
$referral_link = SITE_PATH . "/signup.php?referral=" . $referral_code;

// Check if rewards have been claimed
$stmt = $conn->prepare("SELECT SUM(amount) AS total_claimed FROM deposits WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_claimed = $stmt->get_result()->fetch_assoc()['total_claimed'] ?? 0;
$stmt->close();

// Calculate the claimable amount
$claimable_amount = $user_rewards['reward_points'] - $total_claimed;


// Fetch the user's transaction status
$stmt = $conn->prepare("SELECT status FROM transactions WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$transaction_status_row = $stmt->get_result()->fetch_assoc();
$transaction_status = $transaction_status_row['status'] ?? null;
$stmt->close();

$record = isset($record) ? $record : ['total_earning' => 0];
?>


<div class="container-fluid py-4">
    <div class="row">

        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-12">
                            <!-- Wallet Balance -->
                            <p class="fs-5 mb-3">Wallet Balance</p>
                            <!-- Wallet Balance Amount (in larger size) -->
                            <h1 class="display-5 mb-4" style="margin-top: -15px;">$<?php echo htmlspecialchars(number_format($wallet_balance, 2)); ?></h1>
                            <!-- Your Account Has Been Activated message -->
                            <?php if ($transaction_status === 'accepted') : ?>
                                <p>Your account has been <span style="color: green;">Activated</span></p>
                            <?php endif; ?>
                            <!-- Deposit Button -->
                            <?php if ($transaction_status === 'accepted') : ?>
                                <p><a href="deposit.php" class="btn btn-info">Deposit</a></p>
                            <?php endif; ?>
                            <!-- Transaction Status and Resend Activation Request Button -->
                            <?php if ($transaction_status === 'rejected') : ?>
                                <p>Transaction Status: <?php echo htmlspecialchars($transaction_status); ?></p>
                                <p><a href="activate.php" class="btn btn-info">Resend Activation Request</a></p>
                            <?php elseif ($transaction_status === 'pending') : ?>
                                <p>Transaction Status: <?php echo htmlspecialchars($transaction_status); ?></p>
                            <?php elseif (!$transaction_status) : ?>
                                <p><a href="activate.php" class="btn btn-info">Activate Account</a></p>
                            <?php elseif ($transaction_status === 'accepted' && $deposit_status) : ?>
                                <p>Deposit Status: <?php echo htmlspecialchars($deposit_status); ?></p>
                            <?php endif; ?>
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
                            <p class="fs-5 mb-3">Total On Stacking</p>
                            <?php
                            // Check if there are any staking records and sum their amounts, otherwise set to 0
                            $total_staking_amount = 0;
                            if (!empty($staking_records)) {
                                foreach ($staking_records as $record) {
                                    $total_staking_amount += $record['amount'];
                                }
                            }
                            ?>
                            <h1 class="display-5 mb-4" style="margin-top: -15px;">
                                <?php echo '$' . htmlspecialchars(number_format($total_staking_amount, 2)); ?>
                            </h1>
                            <!-- Remove the loop as we are only showing the total -->
                            <!-- <p><a href="#" class="btn btn-info">Stacking</a></p> -->
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
                            <p class="fs-5 mb-3">Daily Earning</p>
                            <?php
                            // Check if there are any daily earnings records and get the latest one, otherwise set to 0
                            $latest_daily_earning_record = !empty($daily_earnings_records) ? end($daily_earnings_records) : ['amount' => 0, 'date' => ''];
                            ?>
                            <h1 class="display-5 mb-4" style="margin-top: -15px;">
                                <?php echo '$' . htmlspecialchars(number_format($latest_daily_earning_record['amount'], 2)); ?>
                            </h1>
                            <p><?php echo htmlspecialchars($latest_daily_earning_record['date']); ?></p>
                            <!-- Remove the loop as we are only showing the latest record -->
                            <!-- <p><a href="#" class="btn btn-info">Daily Earning Record</a></p> -->
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
                            <p class="fs-5 mb-3">Total Daily Earning</p>
                            <?php
                            // Initialize the total earnings to 0
                            $total_daily_earnings = 0;

                            // Check if there are any daily earnings records and sum their amounts
                            if (!empty($daily_earnings_records)) {
                                foreach ($daily_earnings_records as $record) {
                                    $total_daily_earnings += $record['amount'];
                                }
                            }
                            ?>
                            <h1 class="display-5 mb-4" style="margin-top: -15px;">
                                $<?php echo htmlspecialchars(number_format($total_daily_earnings, 2)); ?>
                            </h1>
                            <p>Total Daily Earning</p>
                            <!-- Remove the loop as we are only showing the total -->
                            <!-- <p><a href="#" class="btn btn-info">Daily Earning Record</a></p> -->
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
                            <p class="fs-5 mb-3">Team Reward</p>
                            <h1 class="display-5 mb-4" style="margin-top: -15px;">$<?php echo htmlspecialchars($user_rewards['reward_points']); ?>.00</h1>
                            <!-- <p><a href="team.php" class="btn btn-info">Team Building</a></p> -->
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
                            <p class="fs-5 mb-3">Total Earning</p>
                            <h1 class="display-5 mb-4" style="margin-top: -15px;">
                                $<?php
                                    $reward_points = isset($user_rewards['reward_points']) ? $user_rewards['reward_points'] : 0;
                                    $total_earning = isset($record['total_earning']) ? $record['total_earning'] : 0;
                                    echo number_format($reward_points + $total_earning, 2);
                                    ?>
                            </h1>
                            <!-- <p><a href="team.php" class="btn btn-info">Team Building</a></p> -->
                            <p>Daily & Refer Total Earning</p>
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
                            <p class="fs-5 mb-3">Withdraw</p>
                            <h1 class="display-5 mb-4" style="margin-top: -15px;">$0.00</h1>
                            <!-- <p><a href="team.php" class="btn btn-info">Team Building</a></p> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Other content rows -->
    </div>
</div>

<?php require('footer.php'); ?>