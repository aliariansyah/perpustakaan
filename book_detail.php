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
} else {
    // Redirect to manage_books.php if book_id is not found
    header("Location: manage_books.php");
    exit();
}

// Start the session to access session variables
session_start();

// Determine the destination dashboard based on the user's role
$dashboard_link = ($_SESSION['user_role'] === 'admin') ? 'admin_dashboard.php' : 'user_dashboard.php';

// Check if the user is logged in and has a user role
$user_logged_in = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user';

// Retrieve user reviews for the current book from the database
$select_reviews_query = "SELECT r.*, u.full_name
                         FROM reviews r
                         JOIN users u ON r.user_id = u.id
                         WHERE r.book_id = '$book_id'";
$result_reviews = $conn->query($select_reviews_query);

// Check for query execution errors
if ($result_reviews === FALSE) {
    echo "Error executing reviews query: " . $conn->error;
    exit();
}

// Check if the quantity is greater than or equal to 1
$quantity = isset($row['quantity']) ? intval($row['quantity']) : 0;
$can_borrow = $quantity >= 1;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dashboard.css">
    <title>Book Detail - Dashboard</title>
    <style>
                       body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .dashboard-container {
            max-width: 800px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h2, h3 {
            text-align: center;
            color: #333;
        }

        p {
            text-align: right;
            margin: 10px 0 0 0;
        }

        a {
            text-decoration: none;
            color: #3498db;
        }

        .book-detail-container {
            display: flex;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .book-info {
            width: 60%;
            padding: 0 20px 0 0;
        }

        .book-info strong {
            display: block;
            margin-top: 10px;
            color: #555;
        }

        .book-image {
            width: 40%;
            max-width: 250px;
            overflow: hidden;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        img {
            width: 100%;
            height: auto;
            border-radius: 4px;
        }

        button {
            margin-top: 10px;
            background-color: #4caf50;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #2980b9;
        }

        .synopsis {
            margin-top: 20px;
        }

    </style>
</head>

<body>
    <div class="dashboard-container">
        <h2>Book Detail:</h2>
        <p><a href="logout.php">Logout</a></p>
        <h3><a href="<?php echo $dashboard_link; ?>">Back to Dashboard</a></h3>

        <!-- Display book information -->
        <div class="book-detail-container">
            <div class="book-info">
                <strong>Title:</strong> <?php echo isset($row['title']) ? htmlspecialchars($row['title']) : ''; ?><br>
                <strong>Writer:</strong> <?php echo isset($row['writer']) ? htmlspecialchars($row['writer']) : ''; ?><br>
                <strong>Publisher:</strong> <?php echo isset($row['publisher']) ? htmlspecialchars($row['publisher']) : ''; ?><br>
                <strong>Year Released:</strong> <?php echo isset($row['year_released']) ? htmlspecialchars($row['year_released']) : ''; ?><br>
                <strong>Category:</strong> <?php echo isset($row['category']) ? htmlspecialchars($row['category']) : ''; ?><br>
                <strong>Quantity:</strong> <?php echo isset($row['quantity']) ? htmlspecialchars($row['quantity']) : ''; ?><br>
                <strong class="synopsis">Synopsis:</strong> <?php echo isset($row['context']) ? nl2br(htmlspecialchars($row['context'])) : ''; ?><br>

                <?php if ($user_logged_in && $can_borrow) : ?>
                    <form method="post" action="borrow_page.php">
                        <input type="hidden" name="book_id" value="<?php echo $book_id; ?>">
                        <button type="submit" name="borrow_book">Borrow</button>
                    </form>
                <?php elseif (!$can_borrow) : ?>
                    <p>This book is currently not available for borrowing.</p>
                <?php endif; ?>
            </div>
            <div class="book-image">
                <img src="<?php echo isset($row['image_path']) ? htmlspecialchars($row['image_path']) : ''; ?>" alt="<?php echo isset($row['title']) ? htmlspecialchars($row['title']) : ''; ?>">
            </div>
        </div>

        <!-- Display user reviews -->
        <h3>User Reviews:</h3>
        <div class="user-reviews">
            <?php if ($result_reviews->num_rows > 0) : ?>
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Rating</th>
                            <th>Review</th>
                            <th>Time</th>
                            <!-- Add other columns as needed -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($review_row = $result_reviews->fetch_assoc()) : ?>
                            <tr>
                                <td><?php echo $review_row['full_name']; ?></td>
                                <td><?php echo $review_row['rating']; ?></td>
                                <td><?php echo $review_row['comment']; ?></td>
                                <td><?php echo $review_row['timestamp']; ?></td>
                                <!-- Add other columns as needed -->
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p>No reviews available.</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
