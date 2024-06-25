<?php require('connection.inc.php'); ?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <!-- CSS Files -->
    <link href="css/own1.css" rel="stylesheet" />

    <!-- Custom CSS -->
    <style>
        .navbar-nav .nav-link {
            text-decoration: none; /* Remove underline */
            color: inherit; /* Inherit color */
        }

        .navbar-nav .nav-link:hover {
            color: inherit; /* Inherit color on hover */
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary shadow-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php"><b>StakingHUB</b></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fas fa-bars" style="color: white;"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="wallet_design.php">Wallet</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="staking.php">Stacking</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="team.php">Team Building</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Daily Earning</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
