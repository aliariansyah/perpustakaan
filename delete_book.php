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

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["book_id"])) {
    $book_id = $_GET["book_id"];

    // Retrieve the image path before deleting the book
    $select_image_query = "SELECT image_path FROM books WHERE book_id = $book_id";
    $result_image = $conn->query($select_image_query);

    if ($result_image->num_rows > 0) {
        $row_image = $result_image->fetch_assoc();
        $image_path = $row_image["image_path"];

        // Delete book from database
        $delete_query = "DELETE FROM books WHERE book_id = $book_id";

        if ($conn->query($delete_query) === TRUE) {
            // Delete the associated image file
            if (file_exists($image_path)) {
                unlink($image_path);
            }

            echo "<script>alert('Book removed successfully');</script>";
            header("Location: admin_dashboard.php"); // Redirect to the admin dashboard after deletion
            exit();
        } else {
            echo "Error: " . $delete_query . "<br>" . $conn->error;
        }
    } else {
        echo "Error retrieving image path.";
    }
} else {
    echo "Invalid request.";
}
?>
