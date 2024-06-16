<?php
ob_start();
session_start();
require('top.inc.php');

// Ensure only admin can access this script
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
//     header("Location: index.php");
//     exit();
// }

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = $_POST['message'];

    // Insert the message into the database
    $stmt = $con->prepare("INSERT INTO admin_messages (message) VALUES (?)");
    $stmt->bind_param("s", $message);
    $stmt->execute();
    $stmt->close();

    $success_message = "Message added successfully!";
}
?>

<h1>Add Message</h1>
<?php if (isset($success_message)) : ?>
    <p style="color: green;"><?php echo $success_message; ?></p>
<?php endif; ?>
<form method="POST" action="">
    <label for="message">Dollar Rate:</label><br>
    <input type="text" id="message" name="message" required><br>
    <button type="submit">Add</button>
</form>


<?php require('footer.inc.php') ?>