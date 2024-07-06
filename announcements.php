<?php
session_start();
require('header.php');

// Fetch announcements
$stmt = $conn->prepare("SELECT * FROM announcements ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$announcements = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<head>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

</head>

<div id="announcementCarousel" class="carousel slide" data-ride="carousel">
    <div class="carousel-inner">
        <?php foreach ($announcements as $index => $announcement) : ?>
            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                <div class="d-block w-100 p-4">
                    <h5><?php echo htmlspecialchars($announcement['announcement_date']); ?></h5>
                    <p><?php echo htmlspecialchars($announcement['message']); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <a class="carousel-control-prev" href="#announcementCarousel" role="button" data-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="sr-only">Previous</span>
    </a>
    <a class="carousel-control-next" href="#announcementCarousel" role="button" data-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="sr-only">Next</span>
    </a>
</div>

<?php require('footer.php'); ?>