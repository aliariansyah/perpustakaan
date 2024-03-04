<?php
session_start();

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

// Get user ID from session
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';
if (empty($user_id)) {
    header("Location: login.php");
    exit();
}

// History in progress (borrowed)
$select_in_progress_query = "SELECT bb.*, b.title
                            FROM book_borrow bb
                            JOIN books b ON bb.book_id = b.book_id
                            WHERE bb.user_id = '$user_id' AND bb.stat = 'borrowed'
                            ORDER BY bb.request_date DESC";
$result_in_progress = $conn->query($select_in_progress_query);

// Check for query execution errors
if ($result_in_progress === FALSE) {
    echo "Error executing in-progress history query: " . $conn->error;
    exit();
}

// History already borrowed (returned)
$select_already_borrowed_query = "SELECT bb.*, b.title
                                FROM book_borrow bb
                                JOIN books b ON bb.book_id = b.book_id
                                WHERE bb.user_id = '$user_id' AND bb.stat = 'returned'
                                ORDER BY bb.returning_date DESC";
$result_already_borrowed = $conn->query($select_already_borrowed_query);

// Check for query execution errors
if ($result_already_borrowed === FALSE) {
    echo "Error executing already borrowed history query: " . $conn->error;
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User History - User Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        body {
    font-family: 'Arial', sans-serif;
    margin: 0;
    padding: 0;
}

.dashboard-container {
    max-width: 90%;
    margin: 50px auto;
    padding: 20px;
    border: 3px solid #ccc;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    background-color: #fff;
}

h2, h3 {
    color: #333;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table, th, td {
    border: 2px solid #ddd;
    border-color: black;
}

th, td {
    padding: 8px;
    text-align: left;
}

th {
    background-color: #f2f2f2;
}

    </style>
</head>

<body>
    <div class="dashboard-container">
        <h2>User History</h2>

        <!-- Display user-specific content and features here -->
        <h3>Welcome, <?php echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'User'; ?>!</h3>

        <!-- Navigation links -->
        <div class="navigation-links">
            <a href="user_dashboard.php">Back to Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>

        <!-- Display pending requests -->
<h3>Pending Requests:</h3>

<div class="pending-requests">
    <?php
    $select_pending_query = "SELECT br.*, b.title
                            FROM borrow_requests br
                            JOIN books b ON br.book_id = b.book_id
                            WHERE br.user_id = '$user_id' AND br.confirmed = 0
                            ORDER BY br.request_date DESC";

    $result_pending = $conn->query($select_pending_query);

    // Check for query execution errors
    if ($result_pending === FALSE) {
        echo "Error executing pending requests query: " . $conn->error;
        exit();
    }
    ?>

    <?php if ($result_pending->num_rows > 0) : ?>
        <table>
            <thead>
                <!-- Add headers for pending requests information -->
                <tr>
                    <!-- Modify headers based on your table structure -->
                    <th>Title</th>
                    <th>Request Date</th>
                    <th>Returning Date</th>
                    <!-- Add other columns as needed -->
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_pending->fetch_assoc()) : ?>
                    <tr>
                        <!-- Display pending requests information -->
                        <!-- Modify columns based on your table structure -->
                        <td><?php echo $row['title']; ?></td>
                        <td><?php echo $row['request_date']; ?></td>
                        <td><?php echo $row['returning_date']; ?></td>
                        <!-- Add other columns as needed -->
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p>No pending requests available.</p>
    <?php endif; ?>
</div>

<!-- Display rejected requests -->
<h3>Rejected Requests:</h3>

<div class="rejected-requests">
    <?php
    $select_rejected_query = "SELECT rr.*, b.title
                            FROM reject_requests rr
                            JOIN books b ON rr.book_id = b.book_id
                            WHERE rr.user_id = '$user_id'
                            ORDER BY rr.request_date DESC";

    $result_rejected = $conn->query($select_rejected_query);

    // Check for query execution errors
    if ($result_rejected === FALSE) {
        echo "Error executing rejected requests query: " . $conn->error;
        exit();
    }
    ?>

    <?php if ($result_rejected->num_rows > 0) : ?>
        <table>
            <thead>
                <!-- Add headers for rejected requests information -->
                <tr>
                    <!-- Modify headers based on your table structure -->
                    <th>Title</th>
                    <th>Request Date</th>
                    <th>Reasons</th>
                    <!-- Add other columns as needed -->
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_rejected->fetch_assoc()) : ?>
                    <tr>
                        <!-- Display rejected requests information -->
                        <!-- Modify columns based on your table structure -->
                        <td><?php echo $row['title']; ?></td>
                        <td><?php echo $row['request_date']; ?></td>
                        <td><?php echo $row['reasons']; ?></td>
                        <!-- Add other columns as needed -->
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p>No rejected requests available.</p>
    <?php endif; ?>
</div>


        <!-- Display in-progress history -->
        <h3>In Progress History:</h3>

        <div class="in-progress-history">
            <?php if ($result_in_progress->num_rows > 0) : ?>
                <table>
                    <thead>
                        <!-- Add headers for in-progress history information -->
                        <tr>
                            <!-- Modify headers based on your table structure -->
                            <th>Title</th>
                            <th>Request Date</th>
                            <th>Returning Date</th>
                            <!-- Add other columns as needed -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_in_progress->fetch_assoc()) : ?>
                            <tr>
                                <!-- Display in-progress history information -->
                                <!-- Modify columns based on your table structure -->
                                <td><?php echo $row['title']; ?></td>
                                <td><?php echo $row['request_date']; ?></td>
                                <td><?php echo $row['returning_date']; ?></td>
                                <!-- Add other columns as needed -->
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>No in-progress history available.</p>
            <?php endif; ?>
        </div>

  <!-- Display already borrowed history -->
<!-- Display already borrowed history -->
<h3>Already Borrowed History:</h3>

<div class="already-borrowed-history">
    <?php if ($result_already_borrowed->num_rows > 0) : ?>
        <table>
            <thead>
                <!-- Add headers for already borrowed history information -->
                <tr>
                    <!-- Modify headers based on your table structure -->
                    <th>Title</th>
                    <th>Request Date</th>
                    <th>Returning Date</th>
                    <th>Actual Return Date</th>
                    <th>status</th>
                    <th>fine</th> <!-- Add column for actual return date -->
                    <!-- Add other columns as needed -->
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_already_borrowed->fetch_assoc()) : ?>
                    <tr>
                        <!-- Display already borrowed history information -->
                        <!-- Modify columns based on your table structure -->
                        <td><?php echo $row['title']; ?></td>
                        <td><?php echo $row['request_date']; ?></td>
                        <td><?php echo $row['returning_date']; ?></td>
                        <td><?php echo $row['actual_return_date']; ?></td>
                        <td><?php echo $row['stat']; ?></td>
                        <td><?php echo $row['fine']; ?></td> <!-- Display actual return date -->
                        <!-- Add other columns as needed -->

                        <!-- Add a new column for the review action -->
                        <td>
                            <?php
                                $bookId = $row['book_id'];
                                $userId = $_SESSION['user_id'];

                                // Check if the user has already submitted a review for this book
                                $checkReviewQuery = "SELECT * FROM reviews WHERE user_id = '$userId' AND book_id = '$bookId'";
                                $resultCheckReview = $conn->query($checkReviewQuery);

                                if ($resultCheckReview->num_rows === 0) {
                                    // User has not submitted a review, display the review button
                                    echo '<form action="review_form.php" method="GET">';
                                    echo '<input type="hidden" name="book_id" value="' . $bookId . '">';
                                    echo '<button type="submit">Review</button>';
                                    echo '</form>';
                                } else {
                                    // User has already submitted a review
                                    echo 'review submitted.';
                                }
                            ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p>No already borrowed history available.</p>
    <?php endif; ?>
</div>


        <!-- JavaScript function to open the review form -->
        <script>
            function openReviewForm(bookId) {
                var reviewForm = document.getElementById('reviewForm_' + bookId);
                if (reviewForm) {
                    reviewForm.style.display = 'block';
                }
            }
        </script>
    </div>
</body>

</html>
