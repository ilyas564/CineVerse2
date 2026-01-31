<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: booking.php');
    exit;
}

// Получаем данные из формы
$session_id = isset($_POST['session_id']) ? (int)$_POST['session_id'] : 0;
$customer_name = isset($_POST['customer_name']) ? trim($_POST['customer_name']) : '';
$customer_email = isset($_POST['customer_email']) ? trim($_POST['customer_email']) : '';
$customer_phone = isset($_POST['customer_phone']) ? trim($_POST['customer_phone']) : '';
$selected_seats = isset($_POST['selected_seats']) ? $_POST['selected_seats'] : '';
$total_price = isset($_POST['total_price']) ? (float)$_POST['total_price'] : 0;

// Валидация
$errors = [];

if ($session_id <= 0) {
    $errors[] = 'Не выбран сеанс';
}

if (empty($customer_name)) {
    $errors[] = 'Не указано имя';
}

if (empty($customer_email) || !filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Неверный email';
}

if (empty($customer_phone)) {
    $errors[] = 'Не указан телефон';
}

if (empty($selected_seats)) {
    $errors[] = 'Не выбраны места';
}

if ($total_price <= 0) {
    $errors[] = 'Неверная сумма';
}

if (!empty($errors)) {
    $_SESSION['booking_errors'] = $errors;
    header('Location: book_seat.php?session_id=' . $session_id);
    exit;
}

$conn = getDBConnection();

// Проверяем доступность сеанса
$query = "SELECT * FROM sessions WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $session_id);
$stmt->execute();
$session = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$session) {
    $errors[] = 'Сеанс не найден';
    $_SESSION['booking_errors'] = $errors;
    header('Location: booking.php');
    exit;
}

// Подсчитываем количество мест
$seats_array = explode(',', $selected_seats);
$seats_count = count(array_filter($seats_array));

if ($seats_count > $session['available_seats']) {
    $errors[] = 'Недостаточно свободных мест';
    $_SESSION['booking_errors'] = $errors;
    header('Location: book_seat.php?session_id=' . $session_id);
    exit;
}

// Генерируем код бронирования
$booking_code = generateBookingCode();

// Вставляем бронирование
$query = "INSERT INTO bookings (session_id, customer_name, customer_email, customer_phone, seats_count, total_price, booking_code, status) 
          VALUES (?, ?, ?, ?, ?, ?, ?, 'confirmed')";
$stmt = $conn->prepare($query);
$stmt->bind_param("isssids", $session_id, $customer_name, $customer_email, $customer_phone, $seats_count, $total_price, $booking_code);

if ($stmt->execute()) {
    // Обновляем количество доступных мест
    $new_available = $session['available_seats'] - $seats_count;
    $update_query = "UPDATE sessions SET available_seats = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ii", $new_available, $session_id);
    $update_stmt->execute();
    $update_stmt->close();
    
    $stmt->close();
    $conn->close();
    
    // Перенаправляем на страницу успеха
    header('Location: booking_success.php?code=' . $booking_code);
    exit;
} else {
    $errors[] = 'Ошибка при создании бронирования: ' . $conn->error;
    $_SESSION['booking_errors'] = $errors;
    $stmt->close();
    $conn->close();
    header('Location: book_seat.php?session_id=' . $session_id);
    exit;
}
?>
