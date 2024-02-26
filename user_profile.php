<?php
session_start();

// Include the database connection code here
$servername = "localhost";
$username = "root";
$password = "";
$database = "perpustakaan_db";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in and has a user role
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'user') {
    // If not logged in or not a user, redirect to the login page
    header("Location: login.php");
    exit();
}

// Handle changing password
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["change_password"])) {
    $old_password = $_POST["old_password"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    // Validate old password (you may need to fetch the hashed password from the database)
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';

    if ($user_id) {
        $select_query = "SELECT password FROM users WHERE id = '$user_id'"; // Update 'user_id' to the correct column name
        $result = $conn->query($select_query);

        if ($result !== false && $result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $hashed_password = $row['password'];

            if (password_verify($old_password, $hashed_password)) {
                // Validate new password
                $password_pattern = "/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{12,}$/";

                if (preg_match($password_pattern, $new_password) && $new_password === $confirm_password) {
                    // Update the password in the database
                    $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_query = "UPDATE users SET password = '$hashed_new_password' WHERE id = '$user_id'"; // Update 'user_id' to the correct column name

                    if ($conn->query($update_query) === TRUE) {
                        echo "<script>alert('Password changed successfully');</script>";
                    } else {
                        echo "Error: " . $update_query . "<br>" . $conn->error;
                    }
                } else {
                    echo "<script>alert('New password does not meet the requirements or does not match the confirm password');</script>";
                }
            } else {
                echo "<script>alert('Old password is incorrect');</script>";
            }
        } else {
            echo "Error: " . $select_query . "<br>" . $conn->error;
        }
    } else {
        echo "<script>alert('User ID not set');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>User Profile - User Dashboard</title>
</head>

<body>
    <div class="dashboard-container">
        <h2>User Profile:</h2>
        <h3><a href="user_dashboard.php">Back to Dashboard</a></h3>

        <!-- Change Password Form -->
        <form method="post" action="">
            <h3>Change Password:</h3>
            <label for="old_password">Old Password:</label>
            <input type="password" id="old_password" name="old_password" required>

            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" required>

            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit" name="change_password">Change Password</button>
        </form>
    </div>
</body>

</html>
