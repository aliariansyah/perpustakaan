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

// Handle adding a new book
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_book"])) {
    $title = $_POST["title"];
    $writer = $_POST["writer"];
    $publisher = $_POST["publisher"];
    $year_released = $_POST["year_released"];
    $context = $_POST["context"];
    $quantity = $_POST["quantity"];
    $category = $_POST["category"];
    $isbn = $_POST["isbn"]; // Added ISBN field

    // Generate a 4-digit random number for book ID
    $book_id = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

    // Upload image
    $target_dir = "images/books/";
    $image_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
    $image_path = $target_dir . "book" . $book_id . "." . $image_extension;

    move_uploaded_file($_FILES["image"]["tmp_name"], $image_path);

    // Insert book into database
    $insert_query = "INSERT INTO books (book_id, title, writer, publisher, year_released, context, quantity, category, isbn, image_path) 
                     VALUES ('$book_id', '$title', '$writer', '$publisher', '$year_released', '$context', '$quantity', '$category', '$isbn', '$image_path')";

    if ($conn->query($insert_query) === TRUE) {
        echo "<script>alert('Book added successfully');</script>";
    } else {
        echo "Error: " . $insert_query . "<br>" . $conn->error;
    }
}

// Handle removing a book
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["remove_book_confirm"])) {
    $book_id = $_POST["book_id"];
    $image_path = $_POST["image_path"];

    // Delete book from database
    $delete_query = "DELETE FROM books WHERE book_id = $book_id";

    if ($conn->query($delete_query) === TRUE) {
        // Delete the associated image file
        if (file_exists($image_path)) {
            unlink($image_path);
        }

        echo "<script>alert('Book removed successfully');</script>";
    } else {
        echo "Error: " . $delete_query . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dashboard.css">
    <title>Add books - Admin Dashboard</title>
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

        // JavaScript function to format ISBN with hyphens
        function formatISBN(input) {
            // Remove any existing hyphens and non-numeric characters
            var cleanedInput = input.replace(/[^\d]/g, '');

            // Apply formatting (3-3-4-2-1)
            var formattedISBN = '';
            for (var i = 0; i < cleanedInput.length; i++) {
                if (i === 3 || i === 6 || i === 10 || i === 12) {
                    formattedISBN += '-';
                }
                formattedISBN += cleanedInput.charAt(i);
            }

            // Set the formatted value back to the input field
            document.getElementById('isbn').value = formattedISBN;
        }
    </script>
</head>

<body>
    <div class="dashboard-container">
        <h2>Add Books:</h2>
        <h3><a href="admin_dashboard.php">Dashboard</a></h3>
        <!-- Form to add a new book -->
        <form method="post" action="" enctype="multipart/form-data">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>

            <label for="writer">Writer:</label>
            <input type="text" id="writer" name="writer" required>

            <label for="publisher">Publisher:</label>
            <input type="text" id="publisher" name="publisher" required>

            <label for="year_released">Year Released:</label>
            <input type="text" id="year_released" name="year_released" required>

            <label for="context">Context:</label>
            <textarea id="context" name="context" required></textarea>

            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" required>

            <label for="category">Category:</label>
            <input type="text" id="category" name="category" required>

            <label for="isbn">ISBN:</label>
<input type="text" id="isbn" name="isbn" required oninput="formatISBN(this.value)" maxlength="16">

            <label for="image">Image:</label>
            <input type="file" id="image" name="image" accept="image/*" required>

            <button type="submit" name="add_book">Add Book</button>
        </form>

        <!-- Form to confirm book removal -->
        <form method="post" action="" id="removeBookForm">
            <input type="hidden" id="book_id_to_remove" name="book_id">
            <input type="hidden" id="image_path_to_remove" name="image_path">
            <button type="submit" name="remove_book_confirm" style="display: none;">Confirm Remove</button>
        </form>
    </div>
</body>

</html>
