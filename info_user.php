<?php
// Include the necessary database connection code here
$servername = "localhost";
$username = "root";
$password = "";
$database = "perpustakaan_db";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user ID from URL parameter
$user_id = $_GET['user_id'];

// Retrieve login history and count for the user from login_data table
$login_history_query = "SELECT DATE(login_datetime) AS login_date, 
                               COUNT(*) AS login_count, 
                               GROUP_CONCAT(TIME(login_datetime) ORDER BY login_datetime) AS login_times
                        FROM login_data
                        WHERE user_id = $user_id
                        GROUP BY login_date
                        ORDER BY login_date DESC";
$login_history_result = $conn->query($login_history_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login History</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <h2>User Login History</h2>
    <p><a href="logout.php">Logout</a></p>
        <p><a href="admin_dashboard.php">Dashboard</a></p>
    <?php if ($login_history_result->num_rows > 0) : ?>
        <table>
            <thead>
                <tr>
                    <th>Login Date</th>
                    <th>Login Count</th>
                    <th>Login Times</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($history_row = $login_history_result->fetch_assoc()) : ?>
                    <tr>
                        <td rowspan="<?php echo $history_row['login_count']; ?>"><?php echo $history_row['login_date']; ?></td>
                        <td rowspan="<?php echo $history_row['login_count']; ?>"><?php echo $history_row['login_count']; ?></td>
                        <?php
                        $login_times = explode(',', $history_row['login_times']);
                        foreach ($login_times as $index => $login_time) :
                            ?>
                            <?php if ($index > 0) : ?>
                                <tr>
                            <?php endif; ?>
                            <td><?php echo $login_time; ?></td>
                            <?php if ($index > 0) : ?>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p>No login history available for this user.</p>
    <?php endif; ?>
</body>

</html>
