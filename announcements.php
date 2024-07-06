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


<div class="col-12 mb-4">
    <div class="card">
        <div class="card-body p-3">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 style="font-size: 20px;">Announcements</h1>
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
                        <a class="carousel-control-prev" href="#announcementCarousel" role="button" data-slide="prev" style="width: 5%;">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Previous</span>
                        </a>
                        <a class="carousel-control-next" href="#announcementCarousel" role="button" data-slide="next" style="width: 5%;">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Next</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php require('footer.php'); ?>