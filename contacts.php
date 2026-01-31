<?php
session_start();
require_once 'config.php';
$conn = getDBConnection();
$user = getCurrentUser();

// Получаем все кинотеатры
$query = "SELECT * FROM cinemas ORDER BY name";
$result = $conn->query($query);
$cinemas = $result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Контакты - CineVerse</title>
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
                    <li><a href="contacts.php" class="active">Контакты</a></li>
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
            <h1 class="page-hero-title animate-fade-in">Контакты</h1>
            <p class="page-hero-subtitle animate-slide-up">Свяжитесь с нами</p>
        </div>
    </section>

    <!-- Контакты -->
    <section class="contacts-section">
        <div class="container">
            <div class="contacts-grid">
                <!-- Информация о компании -->
                <div class="contact-info-card animate-fade-in">
                    <h2>О нас</h2>
                    <p>CineVerse - это современная сеть кинотеатров, предлагающая лучшие фильмы в комфортных условиях. Мы стремимся создать незабываемые впечатления для каждого зрителя.</p>
                    
                    <div class="contact-details">
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <h4>Телефон</h4>
                                <p>+7 (495) 123-45-67</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <h4>Email</h4>
                                <p>info@cinema.ru</p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <h4>Режим работы</h4>
                                <p>Ежедневно: 9:00 - 24:00</p>
                            </div>
                        </div>
                    </div>

                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-vk"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-telegram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <!-- Кинотеатры -->
                <div class="cinemas-list">
                    <h2>Наши кинотеатры</h2>
                    <?php foreach ($cinemas as $index => $cinema): ?>
                        <div class="cinema-card animate-fade-in" style="animation-delay: <?php echo $index * 0.1; ?>s">
                            <div class="cinema-image">
                                <img src="<?php echo htmlspecialchars($cinema['image_url']); ?>" alt="<?php echo htmlspecialchars($cinema['name']); ?>" loading="lazy">
                            </div>
                            <div class="cinema-info">
                                <h3><?php echo htmlspecialchars($cinema['name']); ?></h3>
                                <p class="cinema-address">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($cinema['address']); ?>
                                </p>
                                <p class="cinema-phone">
                                    <i class="fas fa-phone"></i>
                                    <?php echo htmlspecialchars($cinema['phone']); ?>
                                </p>
                                <p class="cinema-email">
                                    <i class="fas fa-envelope"></i>
                                    <?php echo htmlspecialchars($cinema['email']); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Форма обратной связи -->
                <div class="contact-form-card animate-fade-in">
                    <h2>Напишите нам</h2>
                    <form id="contactForm" class="contact-form">
                        <div class="form-group">
                            <label for="contact_name">Ваше имя *</label>
                            <input type="text" id="contact_name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="contact_email">Email *</label>
                            <input type="email" id="contact_email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="contact_subject">Тема</label>
                            <input type="text" id="contact_subject" name="subject">
                        </div>
                        <div class="form-group">
                            <label for="contact_message">Сообщение *</label>
                            <textarea id="contact_message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Отправить
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Карта (заглушка) -->
    <section class="map-section">
        <div class="map-container">
            <div class="map-placeholder">
                <i class="fas fa-map-marked-alt"></i>
                <p>Интерактивная карта с расположением кинотеатров</p>
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
