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

// Set the default time zone to your desired time zone
date_default_timezone_set('Asia/Jakarta'); // Adjust this based on your time zone

// Initialize variables for error handling
$error_message = "";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve user input
    $full_name = $_POST['full_name'];
    $email = $_POST['email']; // Corrected input field name to 'email'
    $password = $_POST["password"];

    // Validate full name (assuming it should not be empty)
    if (empty($full_name)) {
        $error_message = "Full name is required.";
    }

    // Validate email (you can customize this validation)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    }

    // Validate password (you can customize this validation)
    if (empty($password)) {
        $error_message = "Password is required.";
    }

    // If there are no validation errors, proceed with login
    if (empty($error_message)) {
        $email = mysqli_real_escape_string($conn, $email); // Escape email to prevent SQL injection

        $query = "SELECT * FROM users WHERE email = '$email' AND full_name = '$full_name'";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Verify the entered password against the hashed password in the database
            if (password_verify($password, $row["password"])) {
                // Successful login

                // Insert login data into login_data table
                $insert_login_query = "INSERT INTO login_data (user_id, login_datetime) VALUES (?, ?)";
                $stmt = $conn->prepare($insert_login_query);

                if ($stmt) {
                    $user_id = $row['id'];
                    $current_datetime = date("Y-m-d H:i:s");

                    $stmt->bind_param("is", $user_id, $current_datetime);
                    $stmt->execute();
                    $stmt->close();
                }

                // Update last_login in the database
                $update_query = "UPDATE users SET last_login = '$current_datetime' WHERE id = '{$row['id']}'";
                $conn->query($update_query);

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
            $error_message = "User not found. Please check your full name, email, or password.";
        }
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
        <?php if (!empty($error_message)) : ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <form method="post" action="">

            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" required>
            
            <!-- Corrected the input field name to 'email' -->
            <label for="email">E-mail</label>
            <input type="text" id="email" name="email" required>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>

            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </form>
    </div>
</body>

</html>
