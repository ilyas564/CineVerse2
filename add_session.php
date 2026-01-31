<?php
session_start();
require_once 'config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: index.php');
    exit;
}

$user = getCurrentUser();
$conn = getDBConnection();

$movies = $conn->query("SELECT id, title FROM movies ORDER BY title")->fetch_all(MYSQLI_ASSOC);
$cinemas = $conn->query("SELECT id, name FROM cinemas ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$success = isset($_SESSION['add_session_success']);
unset($_SESSION['add_session_success']);
$errors = isset($_SESSION['add_session_errors']) ? $_SESSION['add_session_errors'] : [];
unset($_SESSION['add_session_errors']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movie_id = (int)($_POST['movie_id'] ?? 0);
    $cinema_id = (int)($_POST['cinema_id'] ?? 0);
    $session_date = trim($_POST['session_date'] ?? '');
    $session_time = trim($_POST['session_time'] ?? '');
    $hall_number = (int)($_POST['hall_number'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $total_seats = (int)($_POST['total_seats'] ?? 150);

    $errors = [];
    if ($movie_id <= 0) $errors[] = 'Выберите фильм';
    if ($cinema_id <= 0) $errors[] = 'Выберите кинотеатр';
    if ($session_date === '') $errors[] = 'Укажите дату сеанса';
    if ($session_time === '') $errors[] = 'Укажите время сеанса';
    if ($hall_number <= 0) $errors[] = 'Укажите номер зала';
    if ($price <= 0) $errors[] = 'Укажите цену билета';
    if ($total_seats <= 0) $total_seats = 150;

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO sessions (movie_id, cinema_id, session_date, session_time, hall_number, price, available_seats, total_seats) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissidii", $movie_id, $cinema_id, $session_date, $session_time, $hall_number, $price, $total_seats, $total_seats);
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            $_SESSION['add_session_success'] = true;
            header('Location: add_session.php');
            exit;
        }
        $stmt->close();
        $errors[] = 'Ошибка при сохранении';
    }
    $_SESSION['add_session_errors'] = $errors;
    header('Location: add_session.php');
    exit;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить сеанс - CineVerse</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-wrapper">
                <div class="logo">
                    <i class="fas fa-film"></i>
                    <span>CineVerse</span>
                </div>
                <ul class="nav-menu">
                    <li><a href="index.php">Главная</a></li>
                    <li><a href="movies.php">Фильмы</a></li>
                    <li><a href="contacts.php">Контакты</a></li>
                    <?php if ($user): ?>
                        <li class="nav-user">
                            <a href="#" class="user-menu-toggle">
                                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($user['username']); ?>
                            </a>
                            <ul class="user-menu">
                                <?php if (isAdmin()): ?>
                                <li><a href="add_movie.php"><i class="fas fa-plus-circle"></i> Добавить фильм</a></li>
                                <li><a href="add_session.php" class="active"><i class="fas fa-calendar-plus"></i> Добавить сеанс</a></li>
                                <?php else: ?>
                                <li><a href="booking.php"><i class="fas fa-ticket-alt"></i> Мои бронирования</a></li>
                                <?php endif; ?>
                                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Выйти</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
                <div class="hamburger"><span></span><span></span><span></span></div>
            </div>
        </div>
    </nav>

    <section class="auth-section" style="padding: 4rem 0;">
        <div class="container">
            <div class="auth-card" style="max-width: 600px; margin: 0 auto;">
                <h1 class="page-hero-title" style="font-size: 1.8rem; margin-bottom: 1.5rem;">Добавить сеанс</h1>

                <?php if ($success): ?>
                <div class="auth-success">
                    <i class="fas fa-check-circle"></i>
                    <span>Сеанс успешно добавлен.</span>
                </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                <div class="auth-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars(implode(' ', $errors)); ?></span>
                </div>
                <?php endif; ?>

                <form method="post" class="auth-form">
                    <div class="form-group">
                        <label for="movie_id">Фильм *</label>
                        <select id="movie_id" name="movie_id" required>
                            <option value="">— Выберите фильм —</option>
                            <?php foreach ($movies as $m): ?>
                            <option value="<?php echo (int)$m['id']; ?>" <?php echo (isset($_POST['movie_id']) && (int)$_POST['movie_id'] === (int)$m['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($m['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cinema_id">Кинотеатр *</label>
                        <select id="cinema_id" name="cinema_id" required>
                            <option value="">— Выберите кинотеатр —</option>
                            <?php foreach ($cinemas as $c): ?>
                            <option value="<?php echo (int)$c['id']; ?>" <?php echo (isset($_POST['cinema_id']) && (int)$_POST['cinema_id'] === (int)$c['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="session_date">Дата сеанса *</label>
                        <input type="date" id="session_date" name="session_date" required value="<?php echo htmlspecialchars($_POST['session_date'] ?? date('Y-m-d')); ?>">
                    </div>
                    <div class="form-group">
                        <label for="session_time">Время начала *</label>
                        <input type="time" id="session_time" name="session_time" required value="<?php echo htmlspecialchars($_POST['session_time'] ?? '12:00'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="hall_number">Номер зала *</label>
                        <input type="number" id="hall_number" name="hall_number" min="1" required value="<?php echo (int)($_POST['hall_number'] ?? 1); ?>">
                    </div>
                    <div class="form-group">
                        <label for="price">Цена билета (₽) *</label>
                        <input type="number" id="price" name="price" min="0" step="0.01" required value="<?php echo htmlspecialchars($_POST['price'] ?? '500'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="total_seats">Количество мест в зале</label>
                        <input type="number" id="total_seats" name="total_seats" min="1" value="<?php echo (int)($_POST['total_seats'] ?? 150); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary btn-auth">Добавить сеанс</button>
                </form>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2026 CineVerse. Все права защищены.</p>
            </div>
        </div>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>
