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

// Handle user deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["remove_user_confirm"])) {
    $user_id = $_POST["user_id"];

    // Delete user from the database
    $delete_query = "DELETE FROM users WHERE id = '$user_id'";

    if ($conn->query($delete_query) === TRUE) {
        $_SESSION['message'] = 'User removed successfully';
        header("Location: manage_user.php");
        exit();
    } else {
        $_SESSION['message'] = 'Error removing user: ' . $conn->error;
        header("Location: manage_user.php");
        exit();
    }
}

// Retrieve list of users with role 'user' from the database
$select_query = "SELECT * FROM users WHERE role = 'user'";
$result = $conn->query($select_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dashboard.css">
    <title>Manage Users - Admin Dashboard</title>
    <style>
        /* CSS for user items in row */
        .user-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
        }

        .user-item {
            width: 300px;
            margin: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <h2>Manage Users:</h2>
        <p><a href="logout.php">Logout</a></p>
        <p><a href="admin_dashboard.php">Dashboard</a></p>
        <h3>Welcome, <?php echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Admin'; ?>!</h3>
        

        <!-- Display message if user added or removed successfully -->
        <?php if (isset($_SESSION['message'])) : ?>
            <p style="color: green;"><?php echo $_SESSION['message']; ?></p>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <!-- Display list of users -->
        <h3>User List:</h3>
        <a href="add_user.php"><button type="button">Add User</button></a>

        <div class="user-container">
            <?php
            while ($row = $result->fetch_assoc()) :
            ?>
                <div class="user-item">
                    <p><strong>ID:</strong> <?php echo $row['id']; ?></p>
                    <p><strong>Full Name:</strong> <?php echo htmlspecialchars($row['full_name']); ?></p>
                    <p><strong>NIS:</strong> <?php echo htmlspecialchars($row['nis']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?></p>
                    <p><strong>Phone Number:</strong> <?php echo htmlspecialchars($row['telephone']); ?></p>
                    <form method="post" action="">
                        <input type="hidden" name="remove_user_confirm" value="true">
                        <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                        <button type="button" onclick="confirmDelete(<?php echo $row['id']; ?>)">Remove</button>
                        <a href="edit_user.php?user_id=<?php echo $row['id']; ?>"><button type="button">Edit</button></a>
                        <a href="info_user.php?user_id=<?php echo $row['id']; ?>"><button type="button">Few Info</button></a>
                    </form>
                   
                </div>
                
            <?php endwhile;

            // Check if there are no users
            if ($result->num_rows == 0) {
                echo '<p>No users available.</p>';
            }
            ?>
        </div>
    </div>

    <!-- JavaScript for confirmation prompt -->
    <script>
        function confirmDelete(userId) {
            var confirmDelete = confirm('Are you sure you want to delete this user?');

            if (confirmDelete) {
                // If confirmed, set values and submit the form
                document.getElementById('user_id_to_remove').value = userId;
                document.getElementById('removeUserForm').submit();
            } else {
                // If not confirmed, do nothing
            }
        }
    </script>

    <!-- Add the following form to handle the confirmed user removal -->
    <form id="removeUserForm" method="post" action="">
        <input type="hidden" name="remove_user_confirm" value="true">
        <input type="hidden" name="user_id" id="user_id_to_remove" value="">
    </form>
</body>

</html>