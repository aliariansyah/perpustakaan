<?php
// Check if a session is not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
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

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["generate_report"])) {
    $selected_month = $_POST["selected_month"];
    $selected_year = $_POST["selected_year"];

    // Retrieve data based on the selected month and year, and join with users and books tables
    $select_query = "SELECT bb.*, u.full_name AS user_name, b.title AS book_title
                     FROM book_borrow bb
                     JOIN users u ON bb.user_id = u.id
                     JOIN books b ON bb.book_id = b.book_id
                     WHERE MONTH(request_date) = '$selected_month' AND YEAR(request_date) = '$selected_year'";
    $result = $conn->query($select_query);

    // Check for query execution errors
    if ($result === FALSE) {
        echo "Error executing monthly report query: " . $conn->error;
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Report - Admin Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
</head>

<body>
    <div class="dashboard-container">
        <h2>Admin Dashboard</h2>
        <p><a href="admin_dashboard.php">Dashboard</a></p>
        <p><a href="logout.php">Logout</a></p>
        <h3>Welcome, <?php echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Admin'; ?>!</h3>

        <!-- Select Month and Year Section -->
        <h3>Select Month and Year for report</h3>
        <form method="post" action="">
            <?php
            // Set default values to the current month and year
            $selected_month = date('n');
            $selected_year = date('Y');
            ?>

            <label for="selected_month">Choose a month:</label>
            <select name="selected_month" id="selected_month" required>
                <!-- Display all 12 months -->
                <?php for ($month = 1; $month <= 12; $month++) : ?>
                    <option value="<?php echo $month; ?>" <?php echo ($month == $selected_month) ? 'selected' : ''; ?>><?php echo date('F', mktime(0, 0, 0, $month, 1)); ?></option>
                <?php endfor; ?>
            </select>

            <label for="selected_year">Choose a year:</label>
            <select name="selected_year" id="selected_year" required>
                <!-- Display years dynamically, adjust the range as needed -->
                <?php for ($year = date('Y'); $year >= 2020; $year--) : ?>
                    <option value="<?php echo $year; ?>" <?php echo ($year == $selected_year) ? 'selected' : ''; ?>><?php echo $year; ?></option>
                <?php endfor; ?>
            </select>

            <button type="submit" name="generate_report">Generate Report</button>
        </form>

        <!-- Display message if no data available -->
        <?php if (isset($_POST["generate_report"]) && $result->num_rows == 0) : ?>
            <p>No data available for <?php echo date('F Y', strtotime($selected_year . '-' . $selected_month . '-01')); ?></p>
        <?php endif; ?>

        <!-- Monthly Report Section -->
        <h3>Monthly Report for <?php echo date('F Y', strtotime($selected_year . '-' . $selected_month . '-01')); ?></h3>

        <?php if (isset($_POST["generate_report"]) && $result->num_rows > 0) : ?>

            <!-- Table for Book Borrowed -->
            <h4>Book Borrowed</h4>
            <table>
                <!-- Add headers for book borrowed report information -->
                <thead>
                    <tr>
                        <!-- Modify headers based on your table structure -->
                        <th>User Name</th>
                        <th>Book Id</th>
                        <th>Book Title</th>
                        <th>Request Date</th>
                        <th>Returning Date</th>
                        <!-- Add other columns as needed -->
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) : ?>
                        <tr>
                            <!-- Display book borrowed report information -->
                            <!-- Modify columns based on your table structure -->
                            <td><?php echo $row['user_name']; ?></td>
                            <td><?php echo $row['book_id']; ?></td>
                            <td><?php echo $row['book_title']; ?></td>
                            <td><?php echo $row['request_date']; ?></td>
                            <td><?php echo $row['returning_date']; ?></td>
                            <!-- Add other columns as needed -->
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Table for Users with Fine -->
            <?php
            // Additional query for users with fine
            $fine_query = "SELECT u.full_name AS user_name, bb.*, b.title AS book_title
                           FROM book_borrow bb
                           JOIN users u ON bb.user_id = u.id
                           JOIN books b ON bb.book_id = b.book_id
                           WHERE bb.fine > 0 AND MONTH(bb.returning_date) = '$selected_month' AND YEAR(bb.returning_date) = '$selected_year'";
            $fine_result = $conn->query($fine_query);
            ?>

            <?php if ($fine_result->num_rows > 0) : ?>
                <h4>Users with Fine</h4>
                <table>
                    <!-- Add headers for users with fine report information -->
                    <thead>
                        <tr>
                            <!-- Modify headers based on your table structure -->
                            <th>User Name</th>
                            <th>Book Title</th>
                            <th>Returning Date</th>
                            <th>Fine Amount</th>
                            <!-- Add other columns as needed -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($fine_row = $fine_result->fetch_assoc()) : ?>
                            <tr>
                                <!-- Display users with fine report information -->
                                <!-- Modify columns based on your table structure -->
                                <td><?php echo $fine_row['user_name']; ?></td>
                                <td><?php echo $fine_row['book_title']; ?></td>
                                <td><?php echo $fine_row['returning_date']; ?></td>
                                <td><?php echo $fine_row['fine']; ?></td>
                                <!-- Add other columns as needed -->
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>No users with fine for <?php echo date('F Y', strtotime($selected_year . '-' . $selected_month . '-01')); ?></p>
            <?php endif; ?>

            <!-- Table for Users Details -->
            <?php
            // Additional query for user details
            $user_details_query = "SELECT u.id, u.full_name, u.nis, u.gender, u.telephone, u.last_login
                                  FROM users u
                                  WHERE u.id IN (SELECT DISTINCT user_id FROM book_borrow)";
            $user_details_result = $conn->query($user_details_query);
            ?>

            <?php if ($user_details_result->num_rows > 0) : ?>
                <h4>User Details</h4>
                <table>
                    <!-- Add headers for user details report information -->
                    <thead>
                        <tr>
                            <!-- Modify headers based on your table structure -->
                            <th>User Name</th>
                            <th>NIS</th>
                            <th>Gender</th>
                            <th>Telephone</th>
                            <th>Last Login</th>
                            <!-- Add other columns as needed -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user_details_row = $user_details_result->fetch_assoc()) : ?>
                            <tr>
                                <!-- Display user details report information -->
                                <!-- Modify columns based on your table structure -->
                                <td><?php echo $user_details_row['full_name']; ?></td>
                                <td><?php echo $user_details_row['nis']; ?></td>
                                <td><?php echo $user_details_row['gender']; ?></td>
                                <td><?php echo $user_details_row['telephone']; ?></td>
                                <td><?php echo $user_details_row['last_login']; ?></td>
                                <!-- Add other columns as needed -->
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>No user details available</p>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</body>

</html>
