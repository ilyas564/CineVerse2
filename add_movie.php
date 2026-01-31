<?php
session_start();
require_once 'config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: index.php');
    exit;
}

$user = getCurrentUser();
$success = isset($_SESSION['add_movie_success']);
unset($_SESSION['add_movie_success']);
$errors = isset($_SESSION['add_movie_errors']) ? $_SESSION['add_movie_errors'] : [];
unset($_SESSION['add_movie_errors']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $duration = (int)($_POST['duration'] ?? 0);
    $genre = trim($_POST['genre'] ?? '');
    $rating = isset($_POST['rating']) && $_POST['rating'] !== '' ? (float)$_POST['rating'] : 0.0;
    $poster_url = trim($_POST['poster_url'] ?? '');
    $trailer_url = trim($_POST['trailer_url'] ?? '');
    $release_date = trim($_POST['release_date'] ?? '') ?: null;
    $age_rating = trim($_POST['age_rating'] ?? '') ?: null;

    $errors = [];
    if ($title === '') $errors[] = 'Укажите название фильма';
    if ($duration <= 0) $errors[] = 'Укажите длительность в минутах';

    if (empty($errors)) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("INSERT INTO movies (title, description, duration, genre, rating, poster_url, trailer_url, release_date, age_rating) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssissssds", $title, $description, $duration, $genre, $rating, $poster_url, $trailer_url, $release_date, $age_rating);
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            $_SESSION['add_movie_success'] = true;
            header('Location: add_movie.php');
            exit;
        }
        $stmt->close();
        $conn->close();
        $errors[] = 'Ошибка при сохранении';
    }
    $_SESSION['add_movie_errors'] = $errors;
    header('Location: add_movie.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить фильм - CineVerse</title>
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
                                <li><a href="add_movie.php" class="active"><i class="fas fa-plus-circle"></i> Добавить фильм</a></li>
                                <li><a href="add_session.php"><i class="fas fa-calendar-plus"></i> Добавить сеанс</a></li>
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
                <h1 class="page-hero-title" style="font-size: 1.8rem; margin-bottom: 1.5rem;">Добавить фильм</h1>

                <?php if ($success): ?>
                <div class="auth-success">
                    <i class="fas fa-check-circle"></i>
                    <span>Фильм успешно добавлен.</span>
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
                        <label for="title">Название *</label>
                        <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="description">Описание</label>
                        <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="duration">Длительность (мин) *</label>
                        <input type="number" id="duration" name="duration" min="1" required value="<?php echo (int)($_POST['duration'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="genre">Жанр</label>
                        <input type="text" id="genre" name="genre" value="<?php echo htmlspecialchars($_POST['genre'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="rating">Рейтинг (0–10)</label>
                        <input type="number" id="rating" name="rating" min="0" max="10" step="0.1" value="<?php echo htmlspecialchars($_POST['rating'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="poster_url">URL постера</label>
                        <input type="url" id="poster_url" name="poster_url" value="<?php echo htmlspecialchars($_POST['poster_url'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="trailer_url">URL трейлера</label>
                        <input type="url" id="trailer_url" name="trailer_url" value="<?php echo htmlspecialchars($_POST['trailer_url'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="release_date">Дата выхода</label>
                        <input type="date" id="release_date" name="release_date" value="<?php echo htmlspecialchars($_POST['release_date'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="age_rating">Возрастное ограничение</label>
                        <input type="text" id="age_rating" name="age_rating" placeholder="например 12+" value="<?php echo htmlspecialchars($_POST['age_rating'] ?? ''); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary btn-auth">Добавить фильм</button>
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
