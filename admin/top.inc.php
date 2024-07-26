<?php
require('connection.inc.php');
require('functions.inc.php');
if (isset($_SESSION['ADMIN_LOGIN']) && $_SESSION['ADMIN_LOGIN'] != '') {
} else {
    header('location:login.php');
    die();
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Dashboard Page</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800" rel="stylesheet" type="text/css">
</head>

<body>
    <div class="d-flex">
        <aside id="left-panel" class="bg-light border-right">
            <nav class="navbar navbar-expand-lg navbar-light bg-light flex-column align-items-start">
                <a class="navbar-brand" href="#">StakingHUB</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav flex-column w-100">
                        <li class="nav-item">
                            <a class="nav-link" href="activate.php">Activation Request</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_deposits.php">Deposit Request</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_staking.php">Stacking Request</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin.php">Daily Earning</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_staking_accepted.php">Stacking Request Accepted</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_staking_rejected.php">Stacking Request Rejected</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">User</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="trans_rejected.php">Rejected Activation</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="trans_accepted.php">Accepted Activation</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="deposit_rejected.php">Rejected Deposit</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="deposit_accepted.php">Accepted Deposit</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_refer_btn.php">Refer</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="process_referral_bonus.php">Refer2</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_add_message.php">Dollar Rate</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_manage_payments.php">Withdraw Request</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="add_announcement.php">Add Announcement</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_announcements.php">Edit & Delete Announcement</a>
                        </li>
                    </ul>
                </div>
            </nav>
        </aside>



        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>