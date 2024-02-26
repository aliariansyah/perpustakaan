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

// Handle officer request confirmation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["confirm_request"])) {
    $request_id = $_POST["request_id"];
    $user_id = $_POST["user_id"];

    // Update officer request as confirmed
    $update_request_query = "UPDATE officers SET confirmed = 1 WHERE user_id = '$user_id';";

    if ($conn->query($update_request_query) === TRUE) {
        // Update user role to officer
        $update_user_role_query = "UPDATE users SET role = 'officer' WHERE id = '$user_id';";

        if ($conn->query($update_user_role_query) === TRUE) {
            $_SESSION['message'] = 'Officer request confirmed successfully.';
        } else {
            $_SESSION['message'] = 'Error updating user role: ' . $conn->error;
        }

        header("Location: officer_request.php");
        exit();
    } else {
        $_SESSION['message'] = 'Error confirming officer request: ' . $conn->error;
        header("Location: officer_request.php");
        exit();
    }
}

// Retrieve list of unconfirmed officer requests from the database
$select_query = "SELECT * FROM officers WHERE confirmed = 0";
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
    <title>Officer Requests - Admin Dashboard</title>
</head>

<body>
    <div class="dashboard-container">
        <h2>Officer Requests:</h2>
        <p><a href="admin_dashboard.php">Back to Dashboard</a></p>

        <!-- Display message if officer request confirmed successfully -->
        <?php if (isset($_SESSION['message'])) : ?>
            <p style="color: green;"><?php echo $_SESSION['message']; ?></p>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <!-- List of unconfirmed officer requests with confirm option -->
        <table>
            <tr>
                <th>User ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
            <?php
            while ($row = $result->fetch_assoc()) :
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['officer_email']); ?></td>
                    <td>
                        <form method="post" action="">
                            <input type="hidden" name="confirm_request" value="true">
                            <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                            <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                            <button type="submit">Confirm</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <!-- Check if there are no unconfirmed officer requests -->
        <?php if ($result->num_rows == 0) : ?>
            <p>No officer requests available.</p>
        <?php endif; ?>
    </div>
</body>

</html>
