<?php
require('header.php');
$_SESSION['viewed_key'] = true;

function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>

<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h1 class="card-title mb-4" style="font-size: 20px;">Your Private Key</h1>
            <p class="card-text" style="font-size: 13px; margin-top: -20px;">Please save your key in a safe place. If you do not have this key, you will not be able to log into your account again.</p>
            <p class="random-string alert alert-info" style="font-size: 15px;">
                <?php
                if (isset($_GET['random_string'])) {
                    echo htmlspecialchars($_GET['random_string']);
                } else {
                    echo "No random string found.";
                }
                ?>
            </p>
            <button class="btn btn-warning copy-button" onclick="copyRandomString()"><b>Copy Private Key</b></button><br><br>
            <span class="copy-message alert alert-success" id="copyMessage" style="display: none;">Your Key has been copied</span>

            <div class="mt-4">
                <button class="btn btn-secondary mr-2" onclick="goBack()"><b>Back</b></button>
                <button class="btn btn-primary" onclick="goToLogin()"><b>Login</b></button>
            </div>
        </div>
    </div>
</div>

<script>
    function copyRandomString() {
        var randomString = document.querySelector('.random-string');
        var range = document.createRange();
        range.selectNode(randomString);
        window.getSelection().removeAllRanges();
        window.getSelection().addRange(range);
        document.execCommand("copy");
        window.getSelection().removeAllRanges();
        var copyMessage = document.getElementById('copyMessage');
        copyMessage.style.display = 'inline';
        setTimeout(function() {
            copyMessage.style.display = 'none';
        }, 2000);
    }

    function goBack() {
        window.location.href = "signup.php";
    }

    function goToLogin() {
        window.location.href = "index.php";
    }
</script>

<?php require('footer.php'); ?>
