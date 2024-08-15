<?php
$conn = mysqli_connect("localhost", "root", "", "coin");
define('SERVER_PATH', $_SERVER['DOCUMENT_ROOT'] . 'coin');
define('SITE_PATH', 'http://localhost/coin/');

// Online Code Here

// $conn = mysqli_connect("sql206.infinityfree.com", "if0_36853949", "ZkQAKes45YF34", "if0_36853949_coin");
// define('SERVER_PATH', $_SERVER['DOCUMENT_ROOT'] . '/');
// define('SITE_PATH', 'http://stackinghub.rf.gd/');



//Online Original Code Here

// $conn = mysqli_connect("sql213.infinityfree.com", "if0_37082220", "bX5rPVvrqf0SDv", "if0_37082220_coin");
// define('SERVER_PATH', $_SERVER['DOCUMENT_ROOT'] . '/');
// define('SITE_PATH', 'https://stackinghub.website');

define('PRODUCT_IMAGE_SERVER_PATH', SERVER_PATH . 'media/product/');
define('PRODUCT_IMAGE_SITE_PATH', SITE_PATH . 'media/product/');
?>
