<?php
session_start();
require_once 'config.php';
$user = getCurrentUser();

$booking_code = isset($_GET['code']) ? $_GET['code'] : '';

if (empty($booking_code)) {
    header('Location: index.php');
    exit;
}

$conn = getDBConnection();

$query = "SELECT b.*, s.session_date, s.session_time, s.hall_number, s.price, 
          m.title as movie_title, m.poster_url,
          c.name as cinema_name, c.address
          FROM bookings b
          JOIN sessions s ON b.session_id = s.id
          JOIN movies m ON s.movie_id = m.id
          JOIN cinemas c ON s.cinema_id = c.id
          WHERE b.booking_code = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $booking_code);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$booking) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Бронирование успешно - CineVerse</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .success-section {
            padding: 5rem 0;
            min-height: 70vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .success-card {
            background: var(--bg-card);
            padding: 3rem;
            border-radius: 20px;
            max-width: 600px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        }
        .success-icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 2rem;
            background: linear-gradient(135deg, #4CAF50, #45a049);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            animation: scaleIn 0.5s ease;
        }
        .booking-code {
            font-size: 2rem;
            font-weight: bold;
            color: var(--accent-color);
            margin: 1rem 0;
            letter-spacing: 3px;
        }
        .booking-details {
            text-align: left;
            margin: 2rem 0;
            padding: 1.5rem;
            background: var(--bg-dark);
            border-radius: 15px;
        }
        .booking-details p {
            margin: 0.8rem 0;
            display: flex;
            justify-content: space-between;
        }
        .booking-details strong {
            color: var(--text-primary);
        }
    </style>
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
                                <li><a href="booking.php"><i class="fas fa-ticket-alt"></i> Мои бронирования</a></li>
                                <?php endif; ?>
                                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Выйти</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li><a href="login.php" class="btn-nav" style="padding: 5px 20px;">Вход</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <section class="success-section">
        <div class="container">
            <div class="success-card animate-fade-in">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h1>Бронирование успешно!</h1>
                <p style="color: var(--text-secondary); margin: 1rem 0;">Ваш код бронирования:</p>
                <div class="booking-code"><?php echo htmlspecialchars($booking_code); ?></div>
                
                <div class="booking-details">
                    <p>
                        <span><strong>Фильм:</strong></span>
                        <span><?php echo htmlspecialchars($booking['movie_title']); ?></span>
                    </p>
                    <p>
                        <span><strong>Кинотеатр:</strong></span>
                        <span><?php echo htmlspecialchars($booking['cinema_name']); ?></span>
                    </p>
                    <p>
                        <span><strong>Дата и время:</strong></span>
                        <span><?php echo date('d.m.Y H:i', strtotime($booking['session_date'] . ' ' . $booking['session_time'])); ?></span>
                    </p>
                    <p>
                        <span><strong>Зал:</strong></span>
                        <span><?php echo $booking['hall_number']; ?></span>
                    </p>
                    <p>
                        <span><strong>Количество мест:</strong></span>
                        <span><?php echo $booking['seats_count']; ?></span>
                    </p>
                    <p>
                        <span><strong>Итого:</strong></span>
                        <span style="color: var(--accent-color); font-weight: bold;">
                            <?php echo number_format($booking['total_price'], 0, ',', ' '); ?> ₽
                        </span>
                    </p>
                </div>

                <p style="color: var(--text-secondary); margin-top: 2rem;">
                    Билеты будут отправлены на ваш email: <strong><?php echo htmlspecialchars($booking['customer_email']); ?></strong>
                </p>

                <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: center;">
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home"></i> На главную
                    </a>
                    <a href="booking.php" class="btn" style="background: var(--bg-hover);">
                        <i class="fas fa-ticket-alt"></i> Еще билеты
                    </a>
                </div>
            </div>
        </div>
    </section>

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
