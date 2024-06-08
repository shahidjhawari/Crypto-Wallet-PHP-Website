<?php
require('top.php');
session_start();

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Function to sanitize user input
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $claim_amount = test_input($_POST["claim_amount"]);

    // Check if claim amount is valid
    $stmt = $conn->prepare("SELECT * FROM rewards WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_rewards = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $conn->prepare("SELECT SUM(amount) AS total_claimed FROM deposits WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $total_claimed = $stmt->get_result()->fetch_assoc()['total_claimed'] ?? 0;
    $stmt->close();

    $claimable_amount = $user_rewards['reward_points'] - $total_claimed;

    if ($claim_amount <= $claimable_amount) {
        // Insert the claim into the deposits table
        $status = 'Accepted'; // Assuming reward claims are automatically accepted
        $stmt = $conn->prepare("INSERT INTO deposits (user_id, amount, status) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $claim_amount, $status);

        if ($stmt->execute()) {
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
