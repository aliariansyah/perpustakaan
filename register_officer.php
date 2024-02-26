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

// Start the session to access session variables
session_start();

// Retrieve user details from the session
$user_id = $_SESSION['user_id'] ?? '';
$full_name = $_SESSION['full_name'] ?? '';
$email = $_SESSION['email'] ?? '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Generate a random 4-digit request_id
    $request_id = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

    // Retrieve officer input
    $officer_name = $_POST["officer_name"] ?? '';
    $officer_position = $_POST["officer_position"] ?? '';
    $officer_email = $_POST["officer_email"] ?? '';
    $officer_password = $_POST["officer_password"] ?? '';

    // Validate email format
    if (!filter_var($officer_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format";
    } else {
        // Validate other fields as needed

        // Check if the data already exists in the database (except for password and email)
        $check_query = "SELECT * FROM officers WHERE officer_email = '$officer_email'";
        $result = $conn->query($check_query);

        if ($result && $result->num_rows > 0) {
            $error_message = "Email already in use. Please choose another.";
        } else {
            // Validate password (similar to user registration)

            // Hash the password
            $hashed_password = password_hash($officer_password, PASSWORD_DEFAULT);

            // Insert officer details into the database with hashed password
            $insert_query = "INSERT INTO officers (request_id, user_id, full_name, email, officer_name, officer_position, officer_email, officer_password)
                            VALUES ('$request_id', '$user_id', '$full_name', '$email', '$officer_name', '$officer_position', '$officer_email', '$hashed_password')";

            if ($conn->query($insert_query) === TRUE) {
                // Display success message using JavaScript
                echo '<script>alert("Registration as Officer successful");</script>';

                // Redirect to login page upon successful registration after a short delay
                echo '<script>
                        setTimeout(function() {
                            window.location.href = "login.php";
                        }, 1000);
                      </script>';
                exit();
            } else {
                $error_message = "Error: " . $conn->error;
            }
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
    <title>Officer Registration - Digital Library</title>
</head>

<body>
    <div class="registration-container">
        <h2>Officer Registration</h2>

        <!-- Display error message if registration fails -->
        <?php if (isset($error_message)) : ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <form method="post" action="">
            <!-- Add hidden input for user_id -->
            <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">

            <!-- Add hidden input for full_name -->
            <input type="hidden" name="full_name" value="<?php echo $full_name; ?>">

            <!-- Add hidden input for email -->
            <input type="hidden" name="email" value="<?php echo $email; ?>">

            <!-- Add officer-specific fields -->
            <label for="officer_name">Officer Name:</label>
            <input type="text" id="officer_name" name="officer_name" required>

            <label for="officer_position">Officer Position:</label>
            <input type="text" id="officer_position" name="officer_position" required>

            <label for="officer_email">Officer Email:</label>
            <input type="email" id="officer_email" name="officer_email" required>

            <label for="officer_password">Password:</label>
            <input type="password" id="officer_password" name="officer_password" required>

            <button type="submit">Register as Officer</button>

            <p>Already have an account? <a href="lndex.php">Login here</a></p>
        </form>
    </div>
</body>

</html>
