<?php
session_start();
include 'db_connection.php';

if (isset($_SESSION['user_id']) && isset($_GET['book_id'])) {
    $user_id = $_SESSION['user_id'];
    $book_id = $_GET['book_id'];

    $query = "INSERT INTO user_library (user_id, book_id) VALUES ($user_id, $book_id)";
    mysqli_query($conn, $query);
    header("Location: MyLibrary.php");
} else {
    echo "Please log in first.";
}
?>
