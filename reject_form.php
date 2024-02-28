<!-- reject_form.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reject Request - Officer Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        /* Add custom CSS styles here */
    </style>
</head>

<body>
    <div class="dashboard-container">
        <h2>Reject Request</h2>

        <!-- Display officer-specific content and features here -->
        <h3>Welcome, <?php echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Officer'; ?>!</h3>

        <!-- Navigation links -->
        <div class="navigation-links">
            <a href="officer_dashboard.php">Back to Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>

        <!-- Display reject form -->
        <form method="post" action="process_reject.php">
            <input type="hidden" name="request_id" value="<?php echo $_GET['request_id']; ?>">

            <label for="reasons">Reasons for Rejection:</label>
            <textarea id="reasons" name="reasons" rows="4" required></textarea>

            <button type="submit" name="reject">Reject Request</button>
        </form>
    </div>
</body>

</html>
