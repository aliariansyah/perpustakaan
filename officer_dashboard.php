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

// Retrieve list of books from the database
$select_query = "SELECT * FROM books";
$result = $conn->query($select_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Officer Dashboard - Digital Library</title>
    <link rel="stylesheet" href="dashboard.css">
</head>

<body>
    <div class="dashboard-container">
        <h2>Officer Dashboard</h2>

        <!-- Display officer-specific content and features here -->
        <h3>Welcome, <?php echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'officer'; ?>!</h3>

        <!-- Navigation links -->
        <div class="navigation-links">
            <a href="user_request.php">User Requests</a></br>
            <a href="logout.php">Logout</a>
        </div>

        <!-- List of books with edit option -->
        <h3>Book List:</h3>

        <div class="book-container">
            <?php
            while ($row = $result->fetch_assoc()) :
            ?>
                <div class="book-item">
                    <img class="book-image" src="<?php echo $row['image_path']; ?>" alt="<?php echo htmlspecialchars($row["title"]); ?>">
                    <p><strong>Title: <?php echo htmlspecialchars($row['title']); ?></strong></p>
                    <p><strong>Category: <?php echo htmlspecialchars($row['category']); ?></strong></p>
                    <p><strong>Quantity: <?php echo htmlspecialchars($row['quantity']); ?></strong></p>
                    <div class="book-buttons">
                        <a href="edit_book.php?book_id=<?php echo $row['book_id']; ?>"><button type="button">Edit</button></a>
                    </div>
                </div>
            <?php endwhile; ?>

            <!-- Check if there are no books -->
            <?php if ($result->num_rows == 0) {
                echo '<p>No books available.</p>';
            }
            ?>
        </div>
    </div>
</body>

</html>
