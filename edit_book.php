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

// Check if the user is logged in and has an admin or officer role
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'officer')) {
    // If not logged in, or not an admin or officer, redirect to the login page
    header("Location: login.php");
    exit();
}

// Determine the appropriate dashboard based on the role
if ($_SESSION['user_role'] === 'admin') {
    // Admin dashboard
    $dashboard_link = "admin_dashboard.php";
} elseif ($_SESSION['user_role'] === 'officer') {
    // Officer dashboard
    $dashboard_link = "officer_dashboard.php";
} else {
    // If the role is not recognized, redirect to the login page
    header("Location: login.php");
    exit();
}

// Check if book_id is set in the URL
if (!isset($_GET['book_id'])) {
    // Redirect to manage_books.php if book_id is not provided
    header("Location: manage_books.php");
    exit();
}

$book_id = $_GET['book_id'];

// Retrieve book information from the database
$select_query = "SELECT * FROM books WHERE book_id = '$book_id'";
$result = $conn->query($select_query);

if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();
    // Initialize $current_image_path with the current image path
    $current_image_path = $row['image_path'];
} else {
    // Redirect to manage_books.php if book_id is not found
    header("Location: manage_books.php");
    exit();
}

// Handle updating book information
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_book"])) {
    $title = $_POST["title"];
    $writer = $_POST["writer"];
    $publisher = $_POST["publisher"];
    $year_released = $_POST["year_released"];
    $context = $_POST["context"];
    $quantity = $_POST["quantity"];
    $category = $_POST["category"];

    // Check if a new image is uploaded
    if (!empty($_FILES["image"]["name"])) {
        // Remove the existing image file
        if (file_exists($current_image_path)) {
            unlink($current_image_path);
        }

        // Upload the new image
        $target_dir = "images/books/";
        $image_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $image_path = $target_dir . "book" . $book_id . "." . $image_extension;

        move_uploaded_file($_FILES["image"]["tmp_name"], $image_path);
    } else {
        // Keep the existing image path
        $image_path = $current_image_path;
    }

    // Update book in the database
    $update_query = "UPDATE books SET title = '$title', writer = '$writer', publisher = '$publisher', 
                     year_released = '$year_released', context = '$context', image_path = '$image_path',
                     quantity = '$quantity', category = '$category'
                     WHERE book_id = '$book_id'";

    if ($conn->query($update_query) === TRUE) {
        echo "<script>alert('Book updated successfully');</script>";
    } else {
        echo "Error: " . $update_query . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="gaya.css">
    <title>Edit Book - Admin Dashboard</title>
</head>

<body>
    <div class="dashboard-container">
        <h2>Edit Book:</h2>
        <h3><a href="<?php echo $dashboard_link; ?>">Dashboard</a></h3>

        <!-- Form to edit book information -->
        <form method="post" action="" enctype="multipart/form-data">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($row['title']); ?>" required>

            <label for="writer">Writer:</label>
            <input type="text" id="writer" name="writer" value="<?php echo htmlspecialchars($row['writer']); ?>" required>

            <label for="publisher">Publisher:</label>
            <input type="text" id="publisher" name="publisher" value="<?php echo htmlspecialchars($row['publisher']); ?>" required>

            <label for="year_released">Year Released:</label>
            <input type="text" id="year_released" name="year_released" value="<?php echo htmlspecialchars($row['year_released']); ?>" required>

            <label for="context">Context:</label>
            <textarea id="context" name="context" required><?php echo htmlspecialchars($row['context']); ?></textarea>

            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($row['quantity']); ?>" required>

            <label for="category">Category:</label>
            <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($row['category']); ?>" required>

            <label for="image">New Image:</label>
            <input type="file" id="image" name="image" accept="image/*">

            <!-- Hidden input to store the current image path -->
            <input type="hidden" name="current_image_path" value="<?php echo htmlspecialchars($row['image_path']); ?>">

            <button type="submit" name="update_book">Update Book</button>
        </form>
    </div>
</body>

</html>
