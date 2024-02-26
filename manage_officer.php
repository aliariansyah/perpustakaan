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

// Handle changing officer back to user role
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["make_user"])) {
    $user_id = $_POST["user_id"];

    // Update user role to user
    $update_user_role_query = "UPDATE users SET role = 'user' WHERE id = '$user_id';";

    if ($conn->query($update_user_role_query) === TRUE) {
        // Update confirmed column in officer table to 0
        $update_officer_query = "UPDATE officers SET confirmed = 0 WHERE user_id = '$user_id';";
        $conn->query($update_officer_query);

        $_SESSION['message'] = 'Officer changed back to user successfully.';
        header("Location: manage_officer.php");
        exit();
    } else {
        $_SESSION['message'] = 'Error changing officer back to user: ' . $conn->error;
        header("Location: manage_officer.php");
        exit();
    }
}

// Retrieve list of users with officer role from the database
$select_query = "SELECT * FROM users WHERE role = 'officer'";
$result = $conn->query($select_query);

// Check for errors in the query execution
if (!$result) {
    die("Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Manage Officers - Admin Dashboard</title>
</head>

<body>
    <div class="dashboard-container">
        <h2>Manage Officers:</h2>
        <p><a href="admin_dashboard.php">Back to Dashboard</a></p>

        <!-- Display message if officer changed back to user successfully -->
        <?php if (isset($_SESSION['message'])) : ?>
            <p style="color: green;"><?php echo $_SESSION['message']; ?></p>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <!-- List of users with officer role -->
        <table>
            <tr>
                <th>User ID</th>
                <th>NIS</th>
                <th>Full Name</th>
                <th>Gender</th>
                <th>Place of Birth</th>
                <th>Date of Birth</th>
                <th>Address</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
            <?php
            while ($row = $result->fetch_assoc()) :
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['nis']); ?></td>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['gender']); ?></td>
                    <td><?php echo htmlspecialchars($row['place_of_birth']); ?></td>
                    <td><?php echo htmlspecialchars($row['date_of_birth']); ?></td>
                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td>
                        <form method="post" action="">
                            <input type="hidden" name="make_user" value="true">
                            <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                            <button type="submit">Remove Officer</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <!-- Check if there are no users with officer role -->
        <?php if ($result->num_rows == 0) : ?>
            <p>No officers available.</p>
        <?php endif; ?>
    </div>
</body>

</html>
