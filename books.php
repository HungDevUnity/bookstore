<?php
session_start();
include 'ConnectDB.php';

// Lấy danh sách sách
$result = $conn->query("SELECT * FROM books");

// Xử lý thêm vào giỏ hàng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_id'])) {
    $book_id = $_POST['book_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

    // Lấy thông tin sách
    $stmt = $conn->prepare("SELECT id, title, price, stock FROM books WHERE id = ? AND stock >= ?");
    $stmt->bind_param("ii", $book_id, $quantity);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();

    if ($book) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Nếu đã có -> cộng dồn
        if (isset($_SESSION['cart'][$book_id])) {
            $_SESSION['cart'][$book_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$book_id] = [
                'title' => $book['title'],
                'price' => $book['price'],
                'quantity' => $quantity
            ];
        }

        echo "<div class='alert alert-success'>Đã thêm vào giỏ hàng!</div>";
    } else {
        echo "<div class='alert alert-danger'>Không đủ hàng!</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Danh sách sách</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        .book-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            height: 100%;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .book-card h5 {
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container mt-4">

    <h2 class="mb-3"></h2>Danh sách sách</h2>

    <!-- Nút thêm -->
    <a href="addbook.php" class="btn btn-success mb-3">+ Thêm sách</a>
    <a href="cart.php" class="btn btn-primary mb-3">🛒 Xem giỏ hàng</a>

    <div class="row">
        <?php while ($book = $result->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="book-card">

                    <h5><?php echo htmlspecialchars($book['title']); ?></h5>

                    <p><strong>Tác giả:</strong> <?php echo htmlspecialchars($book['author']); ?></p>

                    <p><strong>Giá:</strong> 
                        <?php echo number_format($book['price'], 0); ?> VNĐ
                    </p>

                    <p><strong>Tồn kho:</strong> <?php echo $book['stock']; ?></p>

                    <p><?php echo htmlspecialchars($book['description']); ?></p>

                    <!-- Thêm giỏ hàng -->
                    <?php if ($book['stock'] > 0): ?>
                        <form method="POST">
                            <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                            
                            <div class="input-group mb-2">
                                <input type="number" name="quantity" value="1" min="1" max="<?php echo $book['stock']; ?>" 
                                    class="form-control">
                                <button type="submit" class="btn btn-primary">Thêm</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <span class="text-danger">Hết hàng</span>
                    <?php endif; ?>

                    <!-- Nút sửa + xóa -->
                    <div class="mt-2">
                        <a href="editbook.php?id=<?php echo $book['id']; ?>" 
                           class="btn btn-warning btn-sm">Sửa</a>

                        <a href="deletebook.php?id=<?php echo $book['id']; ?>" 
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Bạn có chắc muốn xóa?')">
                           Xóa
                        </a>
                    </div>

                </div>
            </div>
        <?php endwhile; ?>
    </div>

</div>

</body>
</html>
