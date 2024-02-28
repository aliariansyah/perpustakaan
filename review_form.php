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

// Initialize the notification message
$notification = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve user input
    $book_id = $_POST['book_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    // Insert review data into the reviews table
    $insert_review_query = "INSERT INTO reviews (book_id, user_id, rating, comment, timestamp) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($insert_review_query);

    if ($stmt) {
        session_start();
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';
        $stmt->bind_param("iiis", $book_id, $user_id, $rating, $comment);
        $stmt->execute();
        $stmt->close();

        // Set the notification message
        echo '<script>alert("Rating submited");</script>';
        echo '<script>
        setTimeout(function() {
            window.location.href = "user_dashboard.php";
        }, 100);
      </script>';
    } else {
        echo "Error preparing statement: " . $conn->error;
        exit();
    }
}

// Retrieve book information based on the book_id from the URL parameter
if (isset($_GET['book_id'])) {
    $book_id = $_GET['book_id'];

    $select_book_query = "SELECT * FROM books WHERE book_id = ?";
    $stmt = $conn->prepare($select_book_query);

    if ($stmt) {
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $book = $result->fetch_assoc();
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
        exit();
    }
} else {
    // Redirect to user history page if book_id is not provided
    header("Location: user_history.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Review</title>
    <link rel="stylesheet" href="gaya.css">
    <style>
        /* Add custom CSS styles here */
    </style>
</head>

<body>
    <div class="form-container">
        <h2>Submit Review for <?php echo isset($book['title']) ? $book['title'] : 'Book'; ?></h2>

        <!-- Display notification message -->
        <?php if (!empty($notification)) : ?>
            <div class="notification"><?php echo $notification; ?></div>
        <?php endif; ?>

        <!-- Display book information -->
        <p>Title: <?php echo isset($book['title']) ? $book['title'] : ''; ?></p>
        <!-- Add other book information as needed -->

        <!-- Review form -->
        <form method="post" action="">
            <input type="hidden" name="book_id" value="<?php echo isset($book['book_id']) ? $book['book_id'] : ''; ?>">

            <label for="rating">Rating:</label>
            <input type="number" placeholder="Give rating 1-5" name="rating" min="1" max="5" required>

            <label for="comment">Comment:</label>
            <textarea name="comment" rows="4" required></textarea>

            <button type="submit" name="submit_review">Submit Review</button>
        </form>
    </div>
</body>

</html>
