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

// Check if user data is set in the session
if (!isset($_SESSION['user_id'], $_SESSION['full_name'], $_SESSION['email'])) {
    echo "Error: User data is not set";
    exit();
}

// Retrieve user data from the session
$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];
$email = $_SESSION['email'];

// Check if the apply_officer form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_application"])) {
    // Retrieve additional data from the form
    // Validate and sanitize the data before using it in the query
    $additional_data = $_POST["additional_data"];

    // Insert officer request into the database
    $insert_query = "INSERT INTO officer_requests (user_id, full_name, email, additional_data) 
                     VALUES ('$user_id', '$full_name', '$email', '$additional_data')";

    if ($conn->query($insert_query) === TRUE) {
        $_SESSION['message'] = 'Officer application submitted successfully';
    } else {
        $_SESSION['message'] = 'Error submitting officer application: ' . $conn->error;
    }

    // Redirect back to the user dashboard
    header("Location: user_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="gaya.css">
    <title>Apply for Officer</title>
</head>

<body>
    <div class="dashboard-container">
        <h2>Apply for Officer</h2>
        <p><a href="logout.php">Logout</a></p>
        <h3>Welcome, <?php echo htmlspecialchars($full_name); ?>!</h3>

        <!-- Officer Application Form -->
        <form method="post" action="apply_officer.php">
            <!-- Additional data fields can be added here -->
            <!-- Example: <label for="additional_data">Additional Data:</label>
                       <input type="text" id="additional_data" name="additional_data" required>
            -->

            <button type="submit" name="submit_application">Submit Application</button>
        </form>

        <!-- Display message if officer application submitted -->
        <?php if (isset($_SESSION['message'])) : ?>
            <p style="color: green;"><?php echo $_SESSION['message']; ?></p>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
    </div>
</body>

</html>
