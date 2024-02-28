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

// Handle book removal
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["remove_book_confirm"])) {
    $book_id = $_POST["book_id"];
    $image_path = $_POST["image_path"];

    // Delete book from the database
    $delete_query = "DELETE FROM books WHERE book_id = '$book_id'";

    if ($conn->query($delete_query) === TRUE) {
        // Delete the associated image file
        if (file_exists($image_path)) {
            unlink($image_path);
        }

        $_SESSION['message'] = 'Book removed successfully';
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $_SESSION['message'] = 'Error removing book: ' . $conn->error;
        header("Location: admin_dashboard.php");
        exit();
    }
}

// Retrieve list of books from the database
$select_query = "SELECT * FROM books";
$result = $conn->query($select_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dashboard.css">
    <title>Admin Dashboard</title>
</head>

<body>
    <div class="dashboard-container">
        <h2>Admin Dashboard</h2>
        <p><a href="logout.php">Logout</a></p>
        <h3>Welcome, <?php echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Admin'; ?>!</h3>

        <!-- Manage Section -->
        <h3>Manage:</h3>
        <ul>
            <li><a href="manage_books.php">Add Books</a></li>
            <li><a href="manage_officer.php">Manage Officers</a></li>
            <li><a href="manage_user.php">Manage Users</a></li>
           
            <li><a href="monthly_report.php">Monthly Report</a></li>

        </ul>

        <!-- Display message if book added or removed successfully -->
        <?php if (isset($_SESSION['message'])) : ?>
            <p style="color: green;"><?php echo $_SESSION['message']; ?></p>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <!-- List of books with remove and edit options -->
        <h3>Book List:</h3>

        <div class="book-container">
            <?php
            $counter = 0;
            while ($row = $result->fetch_assoc()) :
            ?>
                <div class="book-item">
                    <img class="book-image" src="<?php echo $row['image_path']; ?>" alt="<?php echo htmlspecialchars($row["title"]); ?>">
                    <p><strong>Title: <?php echo htmlspecialchars($row['title']); ?></strong></p>
                    <p><strong>Category: <?php echo htmlspecialchars($row['category']); ?></strong></p>
                    <p><strong>Quantity: <?php echo htmlspecialchars($row['quantity']); ?></strong></p>
                    <div class="book-buttons">
                        <form method="post" action="">
                            <input type="hidden" name="remove_book_confirm" value="true">
                            <input type="hidden" name="book_id" value="<?php echo $row['book_id']; ?>">
                            <input type="hidden" name="image_path" value="<?php echo $row['image_path']; ?>">
                            <button type="button" onclick="confirmDelete(<?php echo $row['book_id']; ?>, '<?php echo $row['image_path']; ?>')">Remove</button>
                            <a href="edit_book.php?book_id=<?php echo $row['book_id']; ?>"><button type="button">Edit</button></a>
                        </form>
                    </div>
                </div>
            <?php
                $counter++;
                if ($counter % 4 == 0) {
                    echo '<div style="width: 100%;"></div>'; // Start a new row after every 4 books
                }
            endwhile;

            // Check if there are no books
            if ($counter == 0) {
                echo '<p>No books available.</p>';
            }
            ?>
        </div>

        <hr>

        <!-- JavaScript for confirmation prompt -->
        <script>
            function confirmDelete(bookId, imagePath) {
                var confirmDelete = confirm('Are you sure you want to delete this book?');

                if (confirmDelete) {
                    // If confirmed, set values and submit the form
                    document.getElementById('book_id_to_remove').value = bookId;
                    document.getElementById('image_path_to_remove').value = imagePath;
                    document.getElementById('removeBookForm').submit();
                } else {
                    // If not confirmed, do nothing
                }
            }
        </script>

        <!-- Add the following form to handle the confirmed book removal -->
        <form id="removeBookForm" method="post" action="">
            <input type="hidden" name="remove_book_confirm" value="true">
            <input type="hidden" name="book_id" id="book_id_to_remove" value="">
            <input type="hidden" name="image_path" id="image_path_to_remove" value="">
        </form>

        <!-- End of Manage Section -->

    </div>
</body>

</html>
