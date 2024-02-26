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

// Check if the form is submitted and the required fields are set
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_review"]) && isset($_POST["book_id"]) && isset($_POST["rating"]) && isset($_POST["comment"])) {
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';

    // Check if the user is logged in
    if (empty($user_id)) {
        header("Location: login.php");
        exit();
    }

    $book_id = $_POST["book_id"];
    $rating = $_POST["rating"];
    $comment = $_POST["comment"];

    // Check if the user has already submitted a review for this book
    $check_review_query = "SELECT * FROM reviews WHERE user_id = '$user_id' AND book_id = '$book_id'";
    $result_check_review = $conn->query($check_review_query);

    // Check for query execution errors
    if ($result_check_review === FALSE) {
        echo "Error checking review: " . $conn->error;
        exit();
    }

    // If the user has not submitted a review, insert the new review
    if ($result_check_review->num_rows == 0) {
        $insert_review_query = "INSERT INTO reviews (user_id, book_id, rating, comment) VALUES ('$user_id', '$book_id', '$rating', '$comment')";

        if ($conn->query($insert_review_query) === TRUE) {
            // Successfully inserted the review
            echo "<script>alert('Review submitted successfully');</script>";
        } else {
            // Failed to insert the review
            echo "<script>alert('Error submitting review');</script>";
        }
    } else {
        // User has already submitted a review for this book
        echo "<script>alert('You have already submitted a review for this book');</script>";
    }

    // Redirect back to the user history page
    echo '<script>
            setTimeout(function() {
                window.location.href = "user_history.php";
            }, 1000);
          </script>';
    exit();
} else {
    // Redirect to the user history page if the form is not submitted properly
    header("Location: user_history.php");
    exit();
}
?>
