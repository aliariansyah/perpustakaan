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

// Handle search query
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// Retrieve list of books from the database based on search term
$select_query = "SELECT * FROM books WHERE title LIKE '%$search_term%' OR writer LIKE '%$search_term%' OR category LIKE '%$search_term%'";
$result = $conn->query($select_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Digital Library</title>
    <link rel="stylesheet" href="dashboard.css">

</head>

<body>
    <div class="dashboard-container">
        <h2>User Dashboard</h2>
        <div class="dropdown">
        </div>
        <h3>Welcome, <?php echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'User'; ?>!</h3>
        <button class="dropbtn" onclick="toggleDropdown()">Settings</button>
            <div class="dropdown-content" id="dropdownContent">
                <a href="user_profile.php">User Profile</a><br>
                <a href="user_history.php">Borrowing History</a><br> <!-- Added link to borrowing history -->
                <a href="#" onclick="confirmLogout()">Logout</a>

<script>
  function confirmLogout() {
    // Display a confirmation dialogue
    const userConfirmed = confirm('Are you sure you want to logout?');

    // If the user confirms, redirect to the logout.php page
    if (userConfirmed) {
      window.location.href = 'index.php';
    }
  }
</script>
                
            </div>

        <!-- Search bar -->
        <form method="get" action="">
            <label for="search">Search:</label>
            <input type="text" id="search" name="search" value="<?php echo $search_term; ?>">
            <button type="submit">Search</button>
        </form>

        
        <!-- List of books -->
        <h3>Book List:</h3>

        <div class="book-container">
            <?php
            while ($row = $result->fetch_assoc()) :
            ?>
                <div class="book-item">
                    <img class="book-image" src="<?php echo $row['image_path']; ?>" alt="<?php echo htmlspecialchars($row["title"]); ?>">
                    <p><strong>Title: <?php echo htmlspecialchars($row['title']); ?></strong></p>
                    <p><strong>Category: <?php echo htmlspecialchars($row['category']); ?></strong></p>
                    <div class="book-buttons">
                        <a href="book_detail.php?book_id=<?php echo $row['book_id']; ?>"><button type="button">Details</button></a>
                    </div>
                </div>
            <?php
            endwhile;

            // Check if there are no books
            if ($result->num_rows == 0) {
                echo '<p>No books available.</p>';
            }
            ?>
            
        </div>
    </div>

    <script>
        function toggleDropdown() {
            var dropdownContent = document.getElementById("dropdownContent");
            dropdownContent.style.display = (dropdownContent.style.display === "block") ? "none" : "block";
        }
    </script>
</body>

</html>
