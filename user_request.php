<?php
// Start the session to access session variables
session_start();

// Check if the user is logged in and has the 'officer' role
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'officer') {
    // If not logged in or does not have the 'officer' role, redirect to the login page
    header("Location: login.php");
    exit();
}

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

// Retrieve borrow requests from the database
$select_borrow_query = "SELECT * FROM borrow_requests WHERE confirmed = 0";
$result_borrow = $conn->query($select_borrow_query);

// Check for query execution errors
if ($result_borrow === FALSE) {
    echo "Error executing borrow request query: " . $conn->error;
    exit();
}

// Retrieve return requests from the database
$select_return_query = "SELECT * FROM borrow_requests WHERE confirmed = 1";
$result_return = $conn->query($select_return_query);


// Check for query execution errors
if ($result_return === FALSE) {
    echo "Error executing return request query: " . $conn->error;
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Requests - Officer Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Add custom CSS styles here */
    </style>
</head>

<body>
    <div class="dashboard-container">
        <h2>User Requests</h2>

        <!-- Display officer-specific content and features here -->
        <h3>Welcome, <?php echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Officer'; ?>!</h3>

        <!-- Navigation links -->
        <div class="navigation-links">
            <a href="officer_dashboard.php">Back to Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>

        <!-- Display borrow requests -->
        <h3>Borrow Requests:</h3>

        <div class="borrow-requests">
            <?php if ($result_borrow->num_rows > 0) : ?>
                <table>
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>User ID</th>
                            <th>Book ID</th>
                            <th>NIS</th>
                            <th>E-mail</th>
                            <th>Phone Number</th>
                            <th>Request Date</th>
                            <th>Returning Date</th>
                            <!-- Add other columns as needed -->
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_borrow->fetch_assoc()) : ?>
                            <tr>
                                <td><?php echo $row['request_id']; ?></td>
                                <td><?php echo $row['user_id']; ?></td>
                                <td><?php echo $row['book_id']; ?></td>
                                <td><?php echo $row['nis']; ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td><?php echo $row['phone_number']; ?></td>
                                <td><?php echo $row['request_date']; ?></td>
                                <td><?php echo $row['returning_date']; ?></td>
                                <!-- Add other columns as needed -->
                                <td>
                                    <form method="post" action="process_confirm.php">
                                        <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                                        <button type="submit" name="confirm">Confirm</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>No borrow requests available.</p>
            <?php endif; ?>
        </div>

        <!-- Display return requests -->
        <h3>Return Requests:</h3>

        <div class="return-requests">
            <?php if ($result_return->num_rows > 0) : ?>
                <table>
                    <thead>
                        <!-- Include necessary columns for return requests -->
                        <tr>
                            <th>Book ID</th>
                            <th>User ID</th>
                            <th>Request Date</th>
                            <th>Returning Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_return->fetch_assoc()) : ?>
                            <tr>
                                <!-- Display return request information -->
                                <!-- You can customize the display based on your table structure -->
                                <td><?php echo $row['book_id']; ?></td>
                                <td><?php echo $row['user_id']; ?></td>
                                <td><?php echo $row['request_date']; ?></td>
                                <td><?php echo $row['returning_date']; ?></td>
                                <td>
                                    <form method="post" action="process_return.php">
                                        <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                                        <button type="submit" name="return">Return</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>No return requests available.</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
