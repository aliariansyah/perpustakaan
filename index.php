<?php


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

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve user input
    $full_name = $_POST['full_name'];
    $nis = $_POST["nis"];
    $email = $_POST["email"];
    $password = $_POST["password"];

    // Query to check if the entered full name, NIS, or email exists in the database
    $query = "SELECT * FROM users WHERE (full_name = '$full_name' OR nis = '$nis' OR email = '$email')";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Verify the entered password against the hashed password in the database
        if (password_verify($password, $row["password"])) {
            // Successful login
            // Set user information in the session
            session_start();
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_role'] = $row['role'];
            $_SESSION['full_name'] = $row['full_name'];

            // Redirect based on user role
            if ($row['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } elseif ($row['role'] == 'officer') {
                header("Location: officer_dashboard.php");
            } elseif ($row['role'] == 'user') {
                header("Location: user_dashboard.php");
            } else {
                // Redirect to a default page if role is not recognized
                header("Location: default_dashboard.php");
            }
            exit();
        } else {
            $error_message = "Incorrect password. Please try again.";
        }
    } else {
        $error_message = "User not found. Please check your full name, NIS, or email.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>User Login - Digital Library</title>
</head>

<body>
    <div class="login-container">
        <h2>User Login</h2>

        <!-- Display error message if login fails -->
        <?php if (isset($error_message)) : ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <form method="post" action="">

            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" required>

            <label for="nis">NIS:</label>
            <input type="text" id="nis" name="nis" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>

            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </form>
    </div>
</body>

</html>
