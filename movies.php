<?php
session_start();
require_once 'config.php';
$conn = getDBConnection();
$user = getCurrentUser();

// Получаем все фильмы
$query = "SELECT * FROM movies ORDER BY release_date DESC, rating DESC";
$result = $conn->query($query);
$movies = $result->fetch_all(MYSQLI_ASSOC);

$message = isset($_SESSION['movies_message']) ? $_SESSION['movies_message'] : null;
unset($_SESSION['movies_message']);

$conn->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог фильмов - CineVerse</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Навигация -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-wrapper">
                <div class="logo">
                    <i class="fas fa-film"></i>
                    <span>CineVerse</span>
                </div>
                <ul class="nav-menu">
                    <li><a href="index.php">Главная</a></li>
                    <li><a href="movies.php" class="active">Фильмы</a></li>
                    <li><a href="contacts.php">Контакты</a></li>
                    <?php if ($user): ?>
                        <li class="nav-user">
                            <a href="#" class="user-menu-toggle">
                                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($user['username']); ?>
                            </a>
                            <ul class="user-menu">
                                <?php if (isAdmin()): ?>
                                <li><a href="add_movie.php"><i class="fas fa-plus-circle"></i> Добавить фильм</a></li>
                                <li><a href="add_session.php"><i class="fas fa-calendar-plus"></i> Добавить сеанс</a></li>
                                <?php else: ?>
                                <li><a href="booking.php"><i class="fas fa-ticket-alt"></i> Мои бронирования</a></li>
                                <?php endif; ?>
                                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Выйти</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li><a href="login.php" class="btn-nav" style="padding: 5px 20px;">Вход</a></li>
                    <?php endif; ?>
                </ul>
                <div class="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section для страницы фильмов -->
    <section class="page-hero">
        <div class="page-hero-overlay"></div>
        <div class="page-hero-content">
            <h1 class="page-hero-title animate-fade-in">Каталог фильмов</h1>
            <p class="page-hero-subtitle animate-slide-up">Откройте для себя лучшие фильмы</p>
        </div>
    </section>

    <!-- Каталог фильмов -->
    <section class="movies-section">
        <div class="container">
            <?php if ($message): ?>
            <div class="auth-<?php echo $message['type'] === 'success' ? 'success' : 'error'; ?>" style="margin-bottom: 1.5rem;">
                <i class="fas fa-<?php echo $message['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <span><?php echo htmlspecialchars($message['text']); ?></span>
            </div>
            <?php endif; ?>

            <div class="movies-filter">
                <input type="text" id="searchInput" placeholder="Поиск фильмов..." class="search-input">
                <select id="genreFilter" class="filter-select">
                    <option value="">Все жанры</option>
                    <option value="Фантастика">Фантастика</option>
                    <option value="Драма">Драма</option>
                    <option value="Экшн">Экшн</option>
                    <option value="Комедия">Комедия</option>
                    <option value="Приключения">Приключения</option>
                </select>
            </div>

            <div class="movies-grid">
                <?php foreach ($movies as $index => $movie): ?>
                    <div class="movie-card animate-fade-in" data-genre="<?php echo htmlspecialchars($movie['genre']); ?>" data-title="<?php echo strtolower(htmlspecialchars($movie['title'])); ?>" style="animation-delay: <?php echo $index * 0.1; ?>s">
                        <div class="movie-poster-wrapper">
                            <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="movie-poster" loading="lazy">
                            <div class="movie-overlay">
                                <div class="movie-rating">
                                    <i class="fas fa-star"></i>
                                    <span><?php echo number_format($movie['rating'], 1); ?></span>
                                </div>
                                <a href="book_seat.php?movie_id=<?php echo $movie['id']; ?>" class="btn-watch-movie">
                                    <i class="fas fa-ticket-alt"></i> Забронировать
                                </a>
                                <?php if ($movie['trailer_url']): ?>
                                <a href="<?php echo htmlspecialchars($movie['trailer_url']); ?>" class="btn-trailer" target="_blank">
                                    <i class="fas fa-play"></i> Трейлер
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="movie-info">
                            <h3 class="movie-title"><?php echo htmlspecialchars($movie['title']); ?></h3>
                            <div class="movie-meta">
                                <span class="movie-genre"><?php echo htmlspecialchars($movie['genre']); ?></span>
                                <span class="movie-duration"><i class="fas fa-clock"></i> <?php echo $movie['duration']; ?> мин</span>
                                <span class="movie-age"><?php echo htmlspecialchars($movie['age_rating']); ?></span>
                            </div>
                            <p class="movie-description"><?php echo htmlspecialchars(mb_substr($movie['description'], 0, 120)) . '...'; ?></p>
                            <div class="movie-release">
                                <i class="fas fa-calendar"></i> Премьера: <?php echo date('d.m.Y', strtotime($movie['release_date'])); ?>
                            </div>
                            <?php if (isAdmin()): ?>
                            <form method="post" action="delete_movie.php" class="movie-delete-form" onsubmit="return confirm('Удалить фильм «<?php echo htmlspecialchars(addslashes($movie['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>»? Все связанные сеансы также будут удалены.');">
                                <input type="hidden" name="movie_id" value="<?php echo (int)$movie['id']; ?>">
                                <button type="submit" class="btn-delete-movie" title="Удалить фильм">
                                    <i class="fas fa-trash-alt"></i> Удалить
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="no-results" style="display: none;">
                <i class="fas fa-search"></i>
                <p>Фильмы не найдены</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>CineVerse</h3>
                    <p>Ваш билет в мир кино</p>
                </div>
                <div class="footer-section">
                    <h4>Навигация</h4>
                    <ul>
                        <li><a href="index.php">Главная</a></li>
                        <li><a href="movies.php">Фильмы</a></li>
                        <li><a href="contacts.php">Контакты</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Контакты</h4>
                    <p><i class="fas fa-phone"></i> +7 (495) 123-45-67</p>
                    <p><i class="fas fa-envelope"></i> info@cinema.ru</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 CineVerse. Все права защищены.</p>
            </div>
        </div>
    </footer>

    <div class="floating-buttons">
        <button class="floating-btn floating-btn-up" id="scrollToTop" title="Наверх">
            <i class="fas fa-arrow-up"></i>
        </button>
        <button class="floating-btn floating-btn-chat" id="openChat" title="Чат поддержки">
            <i class="fas fa-comments"></i>
        </button>
    </div>

    <div class="chat-widget" id="chatWidget">
        <div class="chat-header">
            <h3>Чат поддержки</h3>
            <button class="chat-close" id="closeChat">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="chat-messages" id="chatMessages">
            <div class="chat-message bot">
                <p>Здравствуйте! Чем могу помочь?</p>
            </div>
        </div>
        <div class="chat-input-area">
            <input type="text" id="chatInput" placeholder="Введите сообщение..." maxlength="500">
            <button id="sendMessage">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>

    <script src="js/main.js"></script>
</body>
</html>
