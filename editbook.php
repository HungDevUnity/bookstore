<?php
include 'ConnectDB.php';

$id = $_GET['id'];

// Lấy dữ liệu cũ
$stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();

// Cập nhật
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("UPDATE books SET title=?, author=?, price=?, stock=?, description=? WHERE id=?");
    $stmt->bind_param("ssdssi", $title, $author, $price, $stock, $description, $id);

    if ($stmt->execute()) {
        header("Location: books.php");
        exit;
    } else {
        echo "Lỗi cập nhật!";
    }
}
?>

<h2>Sửa sách</h2>
<form method="POST">
    <input type="text" name="title" value="<?= $book['title'] ?>" required><br><br>
    <input type="text" name="author" value="<?= $book['author'] ?>"><br><br>
    <input type="number" name="price" value="<?= $book['price'] ?>" required><br><br>
    <input type="number" name="stock" value="<?= $book['stock'] ?>"><br><br>
    <textarea name="description"><?= $book['description'] ?></textarea><br><br>
    <button type="submit">Cập nhật</button>
</form>
