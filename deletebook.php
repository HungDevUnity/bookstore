<?php
include 'ConnectDB.php';

$id = $_GET['id'];

$stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: books.php");
    exit;
} else {
    echo "Lỗi xóa!";
}
?>
