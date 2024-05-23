<?php
require('top.php');
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
$user_rewards_result = $stmt->get_result();
$user_rewards = $user_rewards_result ? $user_rewards_result->fetch_assoc() : [];
$stmt->close();

// Fetch the user's referral code
$stmt = $conn->prepare("SELECT referral_code FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_referral_result = $stmt->get_result();
$user_referral = $user_referral_result ? $user_referral_result->fetch_assoc() : [];
$stmt->close();

$referral_code = isset($user_referral['referral_code']) ? $user_referral['referral_code'] : '';
$referral_link = SITE_PATH . "/signup.php?referral=" . $referral_code;

// Fetch the user's transaction status
$stmt = $conn->prepare("SELECT status FROM transactions WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$transaction_result = $stmt->get_result();
$transaction_status = $transaction_result ? $transaction_result->fetch_assoc()['status'] : null;
$stmt->close();

// Fetch the latest deposit status
$deposit_status = null;
$stmt = $conn->prepare("SELECT status FROM deposits WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$deposit_result = $stmt->get_result();
if ($deposit_result) {
  $deposit_data = $deposit_result->fetch_assoc();
  if ($deposit_data) {
    $deposit_status = $deposit_data['status'];
  }
}
$stmt->close();

// Calculate the wallet balance (sum of accepted deposits)
$stmt = $conn->prepare("SELECT SUM(amount) AS wallet_balance FROM deposits WHERE user_id = ? AND status = 'accepted'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$wallet_balance_result = $stmt->get_result();
$wallet_balance = $wallet_balance_result ? $wallet_balance_result->fetch_assoc()['wallet_balance'] : 0;
$stmt->close();

// Update the deposit status if there are no pending or rejected deposits and a new deposit is made
if (isset($_POST['amount'])) {
  $amount = $_POST['amount'];
  $screenshot = $_FILES['screenshot']['name'];
  move_uploaded_file($_FILES['screenshot']['tmp_name'], 'upload/' . $screenshot);

  $deposit_status = 'pending';

  $stmt = $conn->prepare("INSERT INTO deposits (user_id, amount, screenshot, status) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("idss", $user_id, $amount, $screenshot, $deposit_status);
  $stmt->execute();
  $stmt->close();
}

?>

<head>
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  <link href="assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="assets/css/nucleo-svg.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link href="assets/css/nucleo-svg.css" rel="stylesheet" />
  <link id="pagestyle" href="assets/css/soft-ui-dashboard.css?v=1.0.7" rel="stylesheet" />
  <link href="css/own1.css" rel="stylesheet" />
  <title>Dashboard</title>
</head>

<body>
  <div class="g-sidenav-show bg-gray-100">
    <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3" id="sidenav-main">
      <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="dashboard.php">
          <img src="assets/img/logo-ct-dark.png" class="navbar-brand-img h-100" alt="main_logo">
          <span class="ms-1 font-weight-bold">Stacking HUB</span>
        </a>
      </div>
      <hr class="horizontal dark mt-0">
      <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link active" href="dashboard.php">
              <span class="nav-link-text ms-1">Dashboard</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="dashboard.php">
              <span class="nav-link-text ms-1">Team Building</span>
            </a>
          </li>
          <!-- Other nav items -->
        </ul>
      </div>
    </aside>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
      <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">
        <div class="container-fluid py-1 px-3">
          <nav aria-label="breadcrumb">
            <h6 class="font-weight-bolder mb-0">Dashboard</h6>
            <h6 class="font-weight-bolder mb-0">Welcome, <?php echo htmlspecialchars($user_name); ?>!</h6>
          </nav>
          <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
            <div class="ms-md-auto pe-md-3 d-flex align-items-center"></div>
            <ul class="navbar-nav justify-content-end">
              <li class="nav-item d-flex align-items-center">
                <a href="logout.php" class="nav-link text-body font-weight-bold px-0">
                  <i class="fa fa-user me-sm-1"></i>
                  <span class="d-sm-inline d-none">Logout</span>
                </a>
              </li>
              <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                  <div class="sidenav-toggler-inner">
                    <i class="sidenav-toggler-line"></i>
                    <i class="sidenav-toggler-line"></i>
                    <i class="sidenav-toggler-line"></i>
                  </div>
                </a>
              </li>
              <li class="nav-item px-3 d-flex align-items-center">
                <a href="javascript:;" class="nav-link text-body p-0">
                  <i class="fa fa-cog fixed-plugin-button-nav cursor-pointer"></i>
                </a>
              </li>
              <li class="nav-item dropdown pe-2 d-flex align-items-center">
                <a href="javascript:;" class="nav-link text-body p-0" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fa fa-bell cursor-pointer"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end px-2 py-3 me-sm-n4" aria-labelledby="dropdownMenuButton">
                  <!-- Dropdown items -->
                </ul>
              </li>
            </ul>
          </div>
        </div>
      </nav>
      <!-- End Navbar -->
      <div class="container-fluid py-4">
        <div class="row">
          <div class="col-12 mb-4">
            <div class="card shadow">
              <div class="card-body p-3">
                <div class="row">
                  <div class="col-12">
                    <div class="card mb-4 shadow">
                      <div class="row g-0">
                        <div class="col-md-6 mb-3 mb-md-0">
                          <div class="card h-100">
                            <div class="card-body">
                              <h5 class="card-title">Wallet Balance</h5>
                              <p class="card-text fs-1">$<?php echo htmlspecialchars(number_format($wallet_balance, 2)); ?></p>
                              <?php if ($transaction_status === 'accepted') : ?>
                                <a href="deposit.php" class="btn btn-info">Deposit</a>
                              <?php endif; ?>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="card h-100">
                            <div class="card-body">
                              <h5 class="card-title">Total Referral Reward</h5>
                              <p class="card-text">Referral Count: <?php echo htmlspecialchars($user_rewards['referral_count'] ?? 'N/A'); ?></p>
                              <a href="team.php" class="btn btn-info">Team Building</a>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <p>Your Reward Points: <?php echo htmlspecialchars($user_rewards['reward_points'] ?? 'N/A'); ?></p>
                    <p>Referral Count: <?php echo htmlspecialchars($user_rewards['referral_count'] ?? 'N/A'); ?></p>
                    <p>Level One Count: <?php echo htmlspecialchars($user_rewards['level_one_count'] ?? 'N/A'); ?></p>
                    <p>Level Two Count: <?php echo htmlspecialchars($user_rewards['level_two_count'] ?? 'N/A'); ?></p>
                    <p>Level Three Count: <?php echo htmlspecialchars($user_rewards['level_three_count'] ?? 'N/A'); ?></p>
                    <p><?php echo $referral_link ?></p>
                    <?php if ($transaction_status === 'pending') : ?>
                      <p>Account Activation Status: <?php echo htmlspecialchars($transaction_status); ?></p>
                    <?php endif; ?>
                    <?php if ($transaction_status !== 'accepted' && $transaction_status !== 'pending') : ?>
                      <p><a href="activate.php" class="btn btn-info">Activate Account</a></p>
                    <?php elseif ($transaction_status === 'accepted') : ?>
                      <p>Account Activation Status: <?php echo htmlspecialchars($transaction_status); ?></p>
                      <?php if ($deposit_status !== null) : ?>
                        <p>Deposit Status: <?php echo htmlspecialchars($deposit_status); ?></p>
                      <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($transaction_status === 'rejected') : ?>
                      <p>Account Activation Status: <?php echo htmlspecialchars($transaction_status); ?></p>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Other content rows -->
        </div>
      </div>
    </main>
  </div>

  <?php require('footer.php'); ?>
