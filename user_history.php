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
    <link rel="stylesheet" href="gaya.css">
    <style>
        /* Add custom CSS styles here */
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
                            <th>Action</th> <!-- Add a new column for the review action -->
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
                                <!-- Add a new column for the review action -->
                                <td>
                                    <!-- Add a button to open the review form -->
                                    <button onclick="openReviewForm('<?php echo $row['book_id']; ?>')">Review</button>
                                </td>
                                
                                <!-- Add other columns as needed -->
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>No already borrowed history available.</p>
            <?php endif; ?>
        </div>

        <!-- Add a hidden review form for each book -->
        <?php $result_already_borrowed->data_seek(0); ?>
        <?php while ($row = $result_already_borrowed->fetch_assoc()) : ?>
            <div id="reviewForm_<?php echo $row['book_id']; ?>" style="display:none;">
                <form method="post" action="submit_review.php">
                    <input type="hidden" name="book_id" value="<?php echo $row['book_id']; ?>">
                    <label for="rating">Rating:</label>
                    <input type="number" name="rating" min="1" max="5" required>
                    <label for="comment">Comment:</label>
                    <textarea name="comment" rows="4" required></textarea>
                    <button type="submit" name="submit_review">Submit Review</button>
                </form>
            </div>
        <?php endwhile; ?>

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
