<?php
include 'ConnectDB.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO books (title, author, price, stock, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdiss", $title, $author, $price, $stock, $description);

    if ($stmt->execute()) {
        header("Location: books.php");
        exit;
    } else {
        echo "Lỗi thêm sách!";
    }
}
?>

<h2>Thêm sách</h2>
<form method="POST">
    <input type="text" name="title" placeholder="Tên sách" required><br><br>
    <input type="text" name="author" placeholder="Tác giả"><br><br>
    <input type="number" name="price" placeholder="Giá" required><br><br>
    <input type="number" name="stock" placeholder="Số lượng"><br><br>
    <textarea name="description" placeholder="Mô tả"></textarea><br><br>
    <button type="submit">Thêm</button>
</form>
