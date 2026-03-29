<?php
include 'ConnectDB.php';

// Xử lý cập nhật số lượng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $book_id => $quantity) {
        $quantity = (int)$quantity;
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$book_id]);
        } else {
            $stmt = $conn->prepare("SELECT stock FROM books WHERE id = ?");
            $stmt->bind_param("i", $book_id);
            $stmt->execute();
            $stock = $stmt->get_result()->fetch_assoc()['stock'];
            if ($quantity <= $stock) {
                $_SESSION['cart'][$book_id]['quantity'] = $quantity;
            } else {
                echo "<div class='alert alert-danger'>Số lượng vượt quá tồn kho cho sách ID $book_id!</div>";
            }
        }
    }
}

// Xử lý xóa sách
if (isset($_GET['remove'])) {
    $book_id = $_GET['remove'];
    unset($_SESSION['cart'][$book_id]);
    header("Location: cart.php");
    exit;
}

// Xử lý thanh toán
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {
    $customer_name = $_POST['customer_name'];
    $customer_email = $_POST['customer_email'];

    if (empty($customer_name) || empty($customer_email)) {
        echo "<div class='alert alert-danger'>Vui lòng nhập đầy đủ thông tin!</div>";
    } elseif (empty($_SESSION['cart'])) {
        echo "<div class='alert alert-danger'>Giỏ hàng trống!</div>";
    } else {
        // Tạo đơn hàng
        $stmt = $conn->prepare("INSERT INTO orders (customer_name, customer_email) VALUES (?, ?)");
        $stmt->bind_param("ss", $customer_name, $customer_email);
        $stmt->execute();
        $order_id = $conn->insert_id;

        // Thêm chi tiết đơn hàng
        foreach ($_SESSION['cart'] as $book_id => $item) {
            $quantity = $item['quantity'];
            $price = $item['price'];

            // Kiểm tra tồn kho
            $stmt = $conn->prepare("SELECT stock FROM books WHERE id = ?");
            $stmt->bind_param("i", $book_id);
            $stmt->execute();
            $stock = $stmt->get_result()->fetch_assoc()['stock'];

            if ($quantity <= $stock) {
                $stmt = $conn->prepare("INSERT INTO order_details (order_id, book_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiid", $order_id, $book_id, $quantity, $price);
                $stmt->execute();

                // Cập nhật tồn kho
                $stmt = $conn->prepare("UPDATE books SET stock = stock - ? WHERE id = ?");
                $stmt->bind_param("ii", $quantity, $book_id);
                $stmt->execute();
            } else {
                echo "<div class='alert alert-danger'>Sách {$item['title']} không đủ hàng!</div>";
            }
        }

        // Xóa giỏ hàng sau khi thanh toán
        $_SESSION['cart'] = [];
        echo "<div class='alert alert-success'>Đặt hàng thành công! <a href='order.php'>Xem đơn hàng</a></div>";
    }
}
?>

<!DOCTYPE html>
<html>  
<head>
    <meta charset="UTF-8">
    <title>Giỏ hàng</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        .cart-table th, .cart-table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>`
    
<h2>Giỏ hàng</h2>
<?php if (empty($_SESSION['cart'])): ?>
    <p>Giỏ hàng của bạn đang trống.</p>
<?php else: ?>
    <form method="POST">
        <table class="table cart-table">
            <thead>
                <tr>
                    <th>Sách</th>
                    <th>Giá</th>
                    <th>Số lượng</th>
                    <th>Tổng</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total = 0;
                foreach ($_SESSION['cart'] as $book_id => $item):
                    $subtotal = $item['price'] * $item['quantity'];
                    $total += $subtotal;
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['title']); ?></td>
                        <td><?php echo number_format($item['price'], 0); ?> VNĐ</td>
                        <td>
                            <input type="number" name="quantity[<?php echo $book_id; ?>]" value="<?php echo $item['quantity']; ?>" min="1" class="form-control" style="width: 80px;">
                        </td>
                        <td><?php echo number_format($subtotal, 0); ?> VNĐ</td>
                        <td>
                            <a href="?remove=<?php echo $book_id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa sách này?')">Xóa</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3" class="text-end"><strong>Tổng cộng:</strong></td>
                    <td><?php echo number_format($total, 0); ?> VNĐ</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        <button type="submit" name="update_cart" class="btn btn-primary">Cập nhật giỏ hàng</button>
    </form>

    <h3 class="mt-4">Thông tin thanh toán</h3>
    <form method="POST" class="mt-3">
        <div class="mb-3">
            <label class="form-label">Họ tên</label>
            <input type="text" name="customer_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="customer_email" class="form-control" required>
        </div>
        <button type="submit" name="checkout" class="btn btn-success">Thanh toán</button>
    </form>
<?php endif; ?>
</body>
</html>

