<?php
require('header.php');
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $claim_amount = floatval($_POST['claim_amount']);

    // Fetch the current reward points
    $stmt = $conn->prepare("SELECT reward_points FROM rewards WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_rewards = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($claim_amount > 0 && $claim_amount <= $user_rewards['reward_points']) {
        // Insert the claim into the deposits table
        $status = 'Accepted'; // Assuming reward claims are automatically accepted
        $stmt = $conn->prepare("INSERT INTO deposits (user_id, amount, status) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $claim_amount, $status);

        if ($stmt->execute()) {
            $stmt->close();

            // Update the user's reward points
            $stmt = $conn->prepare("UPDATE rewards SET reward_points = reward_points - ? WHERE user_id = ?");
            $stmt->bind_param("di", $claim_amount, $user_id);
            $stmt->execute();
            $stmt->close();

            header("Location: refer_page.php?claim_success=1");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Invalid claim amount.";
    }
}
?>
