<?php
session_start();
require('header.php');

// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

$user_id = $_SESSION['user_id'];

// Handle claim button click
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['claim_bonus'])) {
  $bonus_id = $_POST['bonus_id'];

  // Fetch the bonus record
  $stmt = $conn->prepare("SELECT * FROM bonus_rewards WHERE id = ? AND user_id = ? AND claimed = FALSE");
  $stmt->bind_param("ii", $bonus_id, $user_id);
  $stmt->execute();
  $bonus = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if ($bonus) {
    $bonus_amount = $bonus['bonus_amount'];

    // Insert the amount into the deposits table with status 'accepted'
    $stmt = $conn->prepare("INSERT INTO deposits (user_id, amount, status) VALUES (?, ?, 'accepted')");
    $stmt->bind_param("id", $user_id, $bonus_amount);
    $stmt->execute();
    $stmt->close();

    // Mark the bonus as claimed
    $stmt = $conn->prepare("UPDATE bonus_rewards SET claimed = TRUE WHERE id = ?");
    $stmt->bind_param("i", $bonus_id);
    $stmt->execute();
    $stmt->close();

    echo "Bonus claimed successfully.";
  } else {
    echo "Invalid bonus or already claimed.";
  }
}

// Fetch bonus rewards for the user
$stmt = $conn->prepare("SELECT * FROM bonus_rewards WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bonus_rewards = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="container mt-5">
  <h2>Bonus Rewards</h2>
  <table class="table table-striped">
    <thead>
      <tr>
        <th>Bonus Amount (USD)</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($bonus_rewards as $bonus) : ?>
        <tr>
          <td><?php echo htmlspecialchars(number_format($bonus['bonus_amount'], 2)); ?></td>
          <td><?php echo $bonus['claimed'] ? 'Claimed' : 'Unclaimed'; ?></td>
          <td>
            <?php if (!$bonus['claimed']) : ?>
              <form method="post" action="bonus_rewards.php">
                <input type="hidden" name="bonus_id" value="<?php echo $bonus['id']; ?>">
                <button type="submit" name="claim_bonus" class="btn btn-success">Claim</button>
              </form>
            <?php else : ?>
              <button class="btn btn-secondary" disabled>Claimed</button>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require('footer.php'); ?>
