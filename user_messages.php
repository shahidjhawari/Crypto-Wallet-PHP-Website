<?php
ob_start();
session_start();
require('header.php');

// Fetch all messages from the database
$stmt = $conn->prepare("SELECT * FROM admin_messages ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>


<h1>Messages from Admin</h1>
<ul>
    <?php while ($row = $result->fetch_assoc()) : ?>
        <li>
            <p><?php echo htmlspecialchars($row['message']); ?></p>
            <p><small>Posted on: <?php echo htmlspecialchars($row['created_at']); ?></small></p>
        </li>
    <?php endwhile; ?>
</ul>

<?php require('footer.php'); ?>