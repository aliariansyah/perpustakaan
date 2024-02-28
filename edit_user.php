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

// Check if the user is logged in and has an admin role
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // If not logged in or not an admin, redirect to the login page
    header("Location: login.php");
    exit();
}

// Check if user_id is set in the URL
if (!isset($_GET['user_id'])) {
    // Redirect to manage_user.php if user_id is not provided
    header("Location: manage_user.php");
    exit();
}

$user_id = $_GET['user_id'];

// Retrieve user information from the database
$select_query = "SELECT * FROM users WHERE id = '$user_id'";
$result = $conn->query($select_query);

if ($result->num_rows == 1) {
    $user_data = $result->fetch_assoc();
} else {
    // Redirect to manage_user.php if user_id is not found
    header("Location: manage_user.php");
    exit();
}

// Handle form submission for updating user information
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_user"])) {
    $new_full_name = $_POST["new_full_name"];
    $new_email = $_POST["new_email"];
    $new_telephone = $_POST["new_telephone"];

    // Update user information in the database
    $update_query = "UPDATE users SET full_name = '$new_full_name', email = '$new_email', telephone = '$new_telephone' WHERE id = '$user_id'";

    if ($conn->query($update_query) === TRUE) {
        $_SESSION['message'] = 'User information updated successfully';
        header("Location: manage_user.php");
        exit();
    } else {
        $_SESSION['message'] = 'Error updating user information: ' . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dashboard.css">
    <title>Edit User - Admin Dashboard</title>
</head>

<body>
    <div class="dashboard-container">
        <h2>Edit User:</h2>
        <p><a href="logout.php">Logout</a></p>
        
        <h3>Welcome, <?php echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Admin'; ?>!</h3>

        <!-- Display message if user information updated successfully -->
        <?php if (isset($_SESSION['message'])) : ?>
            <p style="color: green;"><?php echo $_SESSION['message']; ?></p>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <!-- Edit user form -->
        <form method="post" action="">
            <label for="new_full_name">Full Name:</label>
            <input type="text" id="new_full_name" name="new_full_name" value="<?php echo htmlspecialchars($user_data['full_name']); ?>" required><br>

            <label for="new_email">Email:</label>
            <input type="email" id="new_email" name="new_email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required><br>

            <label for="new_telephone">Phone Number:</label>
            <input type="text" id="new_telephone" name="new_telephone" value="<?php echo htmlspecialchars($user_data['telephone']); ?>" required><br>

            <button type="submit" name="update_user">Update User</button>
        </form>
    </div>
</body>

</html>
