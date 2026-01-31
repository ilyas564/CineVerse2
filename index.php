<?php
session_start();
require_once 'config.php';
$conn = getDBConnection();
$user = getCurrentUser();

$today = date('Y-m-d');
$endDate = date('Y-m-d', strtotime('+30 days'));

$query = "SELECT s.*, m.title, m.poster_url, m.duration, m.genre, c.name as cinema_name, c.address 
          FROM sessions s 
          JOIN movies m ON s.movie_id = m.id 
          JOIN cinemas c ON s.cinema_id = c.id 
          WHERE s.session_date BETWEEN ? AND ? 
          ORDER BY s.session_date, s.session_time";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $today, $endDate);
$stmt->execute();
$sessions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$sessionsByDate = [];
foreach ($sessions as $session) {
    $date = $session['session_date'];
    if (!isset($sessionsByDate[$date])) {
        $sessionsByDate[$date] = [];
    }
    $sessionsByDate[$date][] = $session;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineVerse - Главная</title>
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
                    <li><a href="index.php" class="active">Главная</a></li>
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

    <section class="schedule-section" id="schedule">
        <div class="hero-content">
            <h1 class="hero-title animate-fade-in">Добро пожаловать в мир кино</h1>
            <p class="hero-subtitle animate-slide-up">Лучшие фильмы, комфортные залы и бронирование билетов онлайн</p>
            <div class="hero-actions animate-scale">
                <a href="movies.php" class="btn btn-primary">Смотреть фильмы</a>
                <a href="#date-selector" class="btn btn-ghost">К расписанию</a>
            </div>
        </div>
        <div class="container">
            <h2 class="section-title animate-fade-in">Расписание сеансов</h2>
            
            <div class="date-selector" id="date-selector">
                <?php 
                $currentDate = strtotime($today);
                for ($i = 0; $i < 7; $i++): 
                    $date = date('Y-m-d', strtotime("+$i days", $currentDate));
                    $displayDate = date('d.m', strtotime($date));
                    $dayName = date('D', strtotime($date));
                    $dayNames = ['Mon' => 'Пн', 'Tue' => 'Вт', 'Wed' => 'Ср', 'Thu' => 'Чт', 'Fri' => 'Пт', 'Sat' => 'Сб', 'Sun' => 'Вс'];
                    $isActive = $i === 0 ? 'active' : '';
                ?>
                <button class="date-btn <?php echo $isActive; ?>" data-date="<?php echo $date; ?>">
                    <span class="day-name"><?php echo $dayNames[$dayName]; ?></span>
                    <span class="day-number"><?php echo $displayDate; ?></span>
                </button>
                <?php endfor; ?>
            </div>

            <div class="sessions-container">
                <?php if (empty($sessionsByDate)): ?>
                    <div class="no-sessions" id="noSessions">
                        <i class="fas fa-calendar-times"></i>
                        <p>На выбранную дату сеансов нет</p>
                    </div>
                <?php else: ?>
                    <div class="no-sessions" id="noSessions" style="display: <?php echo isset($sessionsByDate[$today]) ? 'none' : 'block'; ?>">
                        <i class="fas fa-calendar-times"></i>
                        <p>На выбранную дату сеансов нет</p>
                    </div>
                    <?php foreach ($sessionsByDate as $date => $dateSessions): ?>
                        <div class="sessions-day" data-date="<?php echo $date; ?>" style="display: <?php echo $date === $today ? 'grid' : 'none'; ?>">
                            <?php foreach ($dateSessions as $session): ?>
                                <div class="session-card animate-fade-in">
                                    <div class="session-poster">
                                        <img src="<?php echo htmlspecialchars($session['poster_url']); ?>" alt="<?php echo htmlspecialchars($session['title']); ?>" loading="lazy">
                                        <div class="session-overlay">
                                            <a href="book_seat.php?session_id=<?php echo $session['id']; ?>" class="btn-book">Забронировать</a>
                                        </div>
                                    </div>
                                    <div class="session-info">
                                        <h3 class="session-title"><?php echo htmlspecialchars($session['title']); ?></h3>
                                        <div class="session-details">
                                            <span class="cinema-name"><i class="fas fa-building"></i> <?php echo htmlspecialchars($session['cinema_name']); ?></span>
                                            <span class="session-time"><i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($session['session_time'])); ?></span>
                                            <span class="session-hall"><i class="fas fa-door-open"></i> Зал <?php echo $session['hall_number']; ?></span>
                                        </div>
                                        <div class="session-meta">
                                            <span class="genre"><?php echo htmlspecialchars($session['genre']); ?></span>
                                            <span class="duration"><?php echo $session['duration']; ?> мин</span>
                                        </div>
                                        <div class="session-price">
                                            <span class="price"><?php echo number_format($session['price'], 0, ',', ' '); ?> ₽</span>
                                            <span class="seats">Осталось мест: <?php echo $session['available_seats']; ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="features-section">
        <div class="container">
            <h2 class="section-title">Почему выбирают нас</h2>
            <div class="features-grid">
                <div class="feature-card animate-fade-in">
                    <div class="feature-icon">
                        <i class="fas fa-video"></i>
                    </div>
                    <h3>Современное оборудование</h3>
                    <p>Проекторы 4K и звуковые системы Dolby Atmos</p>
                </div>
                <div class="feature-card animate-fade-in">
                    <div class="feature-icon">
                        <i class="fas fa-couch"></i>
                    </div>
                    <h3>Комфортные кресла</h3>
                    <p>Ортопедические кресла с подогревом и подлокотниками</p>
                </div>
                <div class="feature-card animate-fade-in">
                    <div class="feature-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <h3>Удобное бронирование</h3>
                    <p>Онлайн-бронирование билетов за несколько кликов</p>
                </div>
                <div class="feature-card animate-fade-in">
                    <div class="feature-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>Лучшие фильмы</h3>
                    <p>Премьеры и классика мирового кинематографа</p>
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
