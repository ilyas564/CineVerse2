<?php
session_start();
require_once 'config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: index.php');
    exit;
}

$movie_id = isset($_POST['movie_id']) ? (int)$_POST['movie_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

if ($movie_id <= 0) {
    $_SESSION['movies_message'] = ['type' => 'error', 'text' => 'Фильм не указан.'];
    header('Location: movies.php');
    exit;
}

$conn = getDBConnection();
$stmt = $conn->prepare("DELETE FROM movies WHERE id = ?");
$stmt->bind_param("i", $movie_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    $_SESSION['movies_message'] = ['type' => 'success', 'text' => 'Фильм удалён.'];
} else {
    $_SESSION['movies_message'] = ['type' => 'error', 'text' => 'Не удалось удалить фильм.'];
}

$stmt->close();
$conn->close();
header('Location: movies.php');
exit;
