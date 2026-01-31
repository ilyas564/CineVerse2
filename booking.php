<?php
session_start();
require_once 'config.php';
$conn = getDBConnection();
$user = getCurrentUser();

// Получаем бронирования пользователя
$user_bookings = [];
if ($user) {
    $bookings_query = "SELECT b.*, s.session_date, s.session_time, s.hall_number, s.price,
                       m.title as movie_title, m.poster_url,
                       c.name as cinema_name, c.address
                       FROM bookings b
                       JOIN sessions s ON b.session_id = s.id
                       JOIN movies m ON s.movie_id = m.id
                       JOIN cinemas c ON s.cinema_id = c.id
                       WHERE b.customer_email = ? AND b.status != 'cancelled'
                       ORDER BY s.session_date DESC, s.session_time DESC
                       LIMIT 50";
    $bookings_stmt = $conn->prepare($bookings_query);
    $bookings_stmt->bind_param("s", $user['email']);
    $bookings_stmt->execute();
    $bookings_result = $bookings_stmt->get_result();
    $user_bookings = $bookings_result->fetch_all(MYSQLI_ASSOC);
    $bookings_stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои бронирования - CineVerse</title>
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
                                <li><a href="add_session.php"><i class="fas fa-calendar-plus"></i> Добавить сеанс</a></li>
                                <?php else: ?>
                                <li><a href="booking.php" class="active"><i class="fas fa-ticket-alt"></i> Мои бронирования</a></li>
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

    <!-- Hero Section -->
    <section class="page-hero">
        <div class="page-hero-overlay"></div>
        <div class="page-hero-content">
            <h1 class="page-hero-title animate-fade-in">Мои бронирования</h1>
            <p class="page-hero-subtitle animate-slide-up">Все ваши забронированные билеты</p>
        </div>
    </section>

    <!-- Мои бронирования -->
    <section class="my-bookings-section">
        <div class="container">
            <?php if ($user): ?>
                <?php if (!empty($user_bookings)): ?>
                    <div class="bookings-list">
                        <?php foreach ($user_bookings as $booking): ?>
                            <div class="booking-item-card animate-fade-in">
                                <div class="booking-item-poster">
                                    <img src="<?php echo htmlspecialchars($booking['poster_url']); ?>" alt="<?php echo htmlspecialchars($booking['movie_title']); ?>" loading="lazy">
                                </div>
                                <div class="booking-item-info">
                                    <h3><?php echo htmlspecialchars($booking['movie_title']); ?></h3>
                                    <div class="booking-item-details">
                                        <div class="detail-row">
                                            <span class="detail-label"><i class="fas fa-building"></i> Кинотеатр:</span>
                                            <span class="detail-value"><?php echo htmlspecialchars($booking['cinema_name']); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label"><i class="fas fa-calendar"></i> Дата:</span>
                                            <span class="detail-value"><?php echo date('d.m.Y', strtotime($booking['session_date'])); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label"><i class="fas fa-clock"></i> Время:</span>
                                            <span class="detail-value"><?php echo date('H:i', strtotime($booking['session_time'])); ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label"><i class="fas fa-door-open"></i> Зал:</span>
                                            <span class="detail-value"><?php echo $booking['hall_number']; ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label"><i class="fas fa-chair"></i> Мест:</span>
                                            <span class="detail-value"><?php echo $booking['seats_count']; ?></span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label"><i class="fas fa-ruble-sign"></i> Сумма:</span>
                                            <span class="detail-value price-value"><?php echo number_format($booking['total_price'], 0, ',', ' '); ?> ₽</span>
                                        </div>
                                    </div>
                                    <div class="booking-item-footer">
                                        <div class="booking-code-display">
                                            <i class="fas fa-ticket-alt"></i>
                                            <span>Код бронирования: <strong><?php echo htmlspecialchars($booking['booking_code']); ?></strong></span>
                                        </div>
                                        <div class="booking-status status-<?php echo $booking['status']; ?>">
                                            <?php 
                                            $status_text = [
                                                'pending' => 'Ожидает подтверждения',
                                                'confirmed' => 'Подтверждено',
                                                'cancelled' => 'Отменено'
                                            ];
                                            echo $status_text[$booking['status']] ?? $booking['status'];
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-bookings">
                        <i class="fas fa-ticket-alt"></i>
                        <p>У вас пока нет бронирований</p>
                        <p class="no-bookings-hint">Выберите фильм из <a href="movies.php">каталога</a> или <a href="index.php">расписания</a> для бронирования</p>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-bookings">
                    <i class="fas fa-lock"></i>
                    <p>Для просмотра бронирований необходимо войти в систему</p>
                    <p class="no-bookings-hint">
                        <a href="login.php" class="btn btn-primary" style="margin-top: 1rem; display: inline-block;">Войти</a>
                    </p>
                </div>
            <?php endif; ?>
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
