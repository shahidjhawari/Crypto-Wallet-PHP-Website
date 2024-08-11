<?php
require('connection.inc.php');

function decryptPassword($encrypted_password, $encryption_key) {
    return openssl_decrypt($encrypted_password, 'AES-128-ECB', $encryption_key);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = test_input($_POST["username"]);
    $random_string = test_input($_POST["random_string"]);

    // Debugging - Print inputs
    echo "Username: " . $username . "<br>";
    echo "Random String: " . $random_string . "<br>";

    // Fetch the user's details from the database
    $stmt = $conn->prepare("SELECT password, random_string FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($encrypted_password, $stored_random_string);
        $stmt->fetch();
        $stmt->close();

        // Debugging - Print retrieved values
        echo "Encrypted Password: " . $encrypted_password . "<br>";
        echo "Stored Random String: " . $stored_random_string . "<br>";

        // Check if the random string matches
        if ($random_string === $stored_random_string) {
            $encryption_key = 'your-encryption-key'; // Use the same key used during encryption
            $decrypted_password = decryptPassword($encrypted_password, $encryption_key);

            // Debugging - Print decrypted password
            echo "Decrypted Password: " . $decrypted_password . "<br>";

            echo "Your password is: " . htmlspecialchars($decrypted_password);
        } else {
            echo "Invalid random string.";
        }
    } else {
        echo "Username not found.";
    }
}

function test_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}
?>
