<?php
session_start();
require_once 'config.php';
$conn = getDBConnection();
$user = getCurrentUser();

// Получаем ошибки из сессии
$errors = isset($_SESSION['booking_errors']) ? $_SESSION['booking_errors'] : [];
unset($_SESSION['booking_errors']);

$session_id = isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0;
$movie_id = isset($_GET['movie_id']) ? (int)$_GET['movie_id'] : 0;

$session = null;
$movie = null;
$cinema = null;

if ($session_id > 0) {
    $query = "SELECT s.*, m.*, c.name as cinema_name, c.address, c.phone 
              FROM sessions s 
              JOIN movies m ON s.movie_id = m.id 
              JOIN cinemas c ON s.cinema_id = c.id 
              WHERE s.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $session = $result->fetch_assoc();
    $stmt->close();
} elseif ($movie_id > 0) {
    $query = "SELECT * FROM movies WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $movie = $result->fetch_assoc();
    $stmt->close();
    
    // Получаем сеансы для этого фильма
    $query = "SELECT s.*, c.name as cinema_name 
              FROM sessions s 
              JOIN cinemas c ON s.cinema_id = c.id 
              WHERE s.movie_id = ? AND s.session_date >= CURDATE() 
              ORDER BY s.session_date, s.session_time 
              LIMIT 10";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $movie_id);
    $stmt->execute();
    $available_sessions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Получаем все кинотеатры для выбора
$cinemas_query = "SELECT * FROM cinemas";
$cinemas_result = $conn->query($cinemas_query);
$all_cinemas = $cinemas_result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Выбор места - CineVerse</title>
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

    <!-- Hero Section -->
    <section class="page-hero">
        <div class="page-hero-overlay"></div>
        <div class="page-hero-content">
            <h1 class="page-hero-title animate-fade-in">Выбор места</h1>
            <p class="page-hero-subtitle animate-slide-up">Выберите удобное место в зале</p>
        </div>
    </section>

    <!-- Форма бронирования -->
    <section class="booking-section">
        <div class="container">
            <?php if ($session): ?>
                <!-- Бронирование конкретного сеанса -->
                <div class="booking-wrapper">
                    <div class="booking-movie-info">
                        <img src="<?php echo htmlspecialchars($session['poster_url']); ?>" alt="<?php echo htmlspecialchars($session['title']); ?>" class="booking-poster">
                        <div class="booking-details">
                            <h2><?php echo htmlspecialchars($session['title']); ?></h2>
                            <div class="booking-meta">
                                <p><i class="fas fa-building"></i> <?php echo htmlspecialchars($session['cinema_name']); ?></p>
                                <p><i class="fas fa-calendar"></i> <?php echo date('d.m.Y', strtotime($session['session_date'])); ?></p>
                                <p><i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($session['session_time'])); ?></p>
                                <p><i class="fas fa-door-open"></i> Зал <?php echo $session['hall_number']; ?></p>
                                <p><i class="fas fa-ruble-sign"></i> <?php echo number_format($session['price'], 0, ',', ' '); ?> ₽ за билет</p>
                            </div>
                        </div>
                    </div>

                    <!-- Схема зала -->
                    <div class="hall-container">
                        <div class="hall-screen">
                            <i class="fas fa-film"></i>
                            <span>Экран</span>
                        </div>
                        <div class="hall-seats" id="hallSeats" data-session-id="<?php echo $session['id']; ?>" data-price="<?php echo $session['price']; ?>" data-total-seats="<?php echo $session['total_seats']; ?>">
                            <!-- Места будут сгенерированы через JavaScript -->
                        </div>
                        <div class="hall-legend">
                            <div class="legend-item">
                                <span class="seat-legend available"></span>
                                <span>Доступно</span>
                            </div>
                            <div class="legend-item">
                                <span class="seat-legend selected"></span>
                                <span>Выбрано</span>
                            </div>
                            <div class="legend-item">
                                <span class="seat-legend occupied"></span>
                                <span>Занято</span>
                            </div>
                        </div>
                    </div>

                    <!-- Сообщения об ошибках -->
                    <?php if (!empty($errors)): ?>
                        <div class="error-messages" style="background: #5a1a1a; padding: 1.5rem; border-radius: 15px; margin-bottom: 2rem; border-left: 4px solid var(--primary-color);">
                            <h3 style="color: var(--primary-color); margin-bottom: 1rem;"><i class="fas fa-exclamation-triangle"></i> Ошибки:</h3>
                            <ul style="list-style: none; color: #ffcccc;">
                                <?php foreach ($errors as $error): ?>
                                    <li style="margin-bottom: 0.5rem;">• <?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Форма данных -->
                    <form id="bookingForm" class="booking-form" method="POST" action="process_booking.php">
                        <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                        <input type="hidden" name="selected_seats" id="selectedSeats" value="">
                        <input type="hidden" name="total_price" id="totalPrice" value="0">
                        
                        <?php if ($user): ?>
                            <!-- Авторизованный пользователь - данные заполняются автоматически -->
                            <div class="user-info-notice">
                                <i class="fas fa-user-check"></i>
                                <span>Вы авторизованы как <strong><?php echo htmlspecialchars($user['username']); ?></strong>. Ваши данные будут использованы для бронирования.</span>
                            </div>
                            <input type="hidden" name="customer_name" value="<?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?>">
                            <input type="hidden" name="customer_email" value="<?php echo htmlspecialchars($user['email']); ?>">
                            <input type="hidden" name="customer_phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            
                            <div class="form-group">
                                <label>Ваше имя</label>
                                <input type="text" value="<?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?>" disabled style="background: var(--bg-hover); opacity: 0.7;">
                            </div>
                            
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="background: var(--bg-hover); opacity: 0.7;">
                            </div>
                            
                            <?php if (!empty($user['phone'])): ?>
                            <div class="form-group">
                                <label>Телефон</label>
                                <input type="tel" value="<?php echo htmlspecialchars($user['phone']); ?>" disabled style="background: var(--bg-hover); opacity: 0.7;">
                            </div>
                            <?php else: ?>
                            <div class="form-group">
                                <label for="customer_phone">Телефон *</label>
                                <input type="tel" id="customer_phone" name="customer_phone" required>
                            </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- Неавторизованный пользователь - нужно заполнить форму -->
                            <div class="user-info-notice guest">
                                <i class="fas fa-info-circle"></i>
                                <span>Для быстрого бронирования <a href="login.php?redirect=<?php echo urlencode('book_seat.php?session_id=' . $session['id']); ?>">войдите в систему</a></span>
                            </div>
                            
                            <div class="form-group">
                                <label for="customer_name">Ваше имя *</label>
                                <input type="text" id="customer_name" name="customer_name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="customer_email">Email *</label>
                                <input type="email" id="customer_email" name="customer_email" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="customer_phone">Телефон *</label>
                                <input type="tel" id="customer_phone" name="customer_phone" required>
                            </div>
                        <?php endif; ?>
                        
                        <div class="booking-summary">
                            <div class="summary-item">
                                <span>Выбрано мест:</span>
                                <span id="seatsCount">0</span>
                            </div>
                            <div class="summary-item">
                                <span>Цена за билет:</span>
                                <span><?php echo number_format($session['price'], 0, ',', ' '); ?> ₽</span>
                            </div>
                            <div class="summary-item total">
                                <span>Итого:</span>
                                <span id="totalAmount">0 ₽</span>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-booking" id="submitBtn" disabled>
                            <i class="fas fa-ticket-alt"></i> Забронировать билеты
                        </button>
                    </form>
                </div>
            <?php elseif ($movie && isset($available_sessions)): ?>
                <!-- Выбор сеанса для фильма -->
                <div class="movie-sessions">
                    <div class="movie-header">
                        <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                        <div>
                            <h2><?php echo htmlspecialchars($movie['title']); ?></h2>
                            <p><?php echo htmlspecialchars($movie['description']); ?></p>
                        </div>
                    </div>
                    <h3>Доступные сеансы:</h3>
                    <div class="sessions-list">
                        <?php foreach ($available_sessions as $sess): ?>
                            <a href="book_seat.php?session_id=<?php echo $sess['id']; ?>" class="session-item">
                                <div class="session-date-time">
                                    <span class="session-date"><?php echo date('d.m', strtotime($sess['session_date'])); ?></span>
                                    <span class="session-time"><?php echo date('H:i', strtotime($sess['session_time'])); ?></span>
                                </div>
                                <div class="session-cinema"><?php echo htmlspecialchars($sess['cinema_name']); ?></div>
                                <div class="session-price"><?php echo number_format($sess['price'], 0, ',', ' '); ?> ₽</div>
                                <div class="session-seats">Осталось: <?php echo $sess['available_seats']; ?> мест</div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- Выбор фильма и сеанса -->
                <div class="booking-selector">
                    <p>Выберите фильм из <a href="movies.php">каталога</a> или <a href="index.php">расписания</a> для бронирования</p>
                    <div style="margin-top: 2rem; text-align: center;">
                        <a href="index.php" class="btn btn-primary">Посмотреть расписание</a>
                        <a href="movies.php" class="btn btn-primary" style="margin-left: 1rem;">Каталог фильмов</a>
                    </div>
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
