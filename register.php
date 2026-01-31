<?php
session_start();
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    
    // Валидация
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Заполните все обязательные поля';
    } elseif ($password !== $password_confirm) {
        $error = 'Пароли не совпадают';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен содержать минимум 6 символов';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Неверный формат email';
    } else {
        $conn = getDBConnection();
        
        // Проверка существования пользователя
        $check_query = "SELECT id FROM users WHERE email = ? OR username = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ss", $email, $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = 'Пользователь с таким email или именем уже существует';
        } else {
            // Создание пользователя
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_query = "INSERT INTO users (username, email, password, full_name, phone) VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("sssss", $username, $email, $hashed_password, $full_name, $phone);
            
            if ($insert_stmt->execute()) {
                $success = 'Регистрация успешна! Теперь вы можете войти.';
                // Автоматический вход после регистрации
                $user_id = $conn->insert_id;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['full_name'] = $full_name;
                $_SESSION['email'] = $email;
                
                $insert_stmt->close();
                $conn->close();
                
                header('Location: index.php');
                exit;
            } else {
                $error = 'Ошибка при регистрации. Попробуйте позже.';
            }
        }
        
        $check_stmt->close();
        if (isset($insert_stmt)) $insert_stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - CineVerse</title>
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
                    <li><a href="booking.php">Бронирование</a></li>
                    <li><a href="contacts.php">Контакты</a></li>
                </ul>
                <div class="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Форма регистрации -->
    <section class="auth-section">
        <div class="auth-container">
            <div class="auth-card animate-fade-in">
                <div class="auth-header">
                    <div class="auth-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h1>Регистрация</h1>
                    <p>Создайте аккаунт для бронирования билетов</p>
                </div>

                <?php if ($error): ?>
                    <div class="auth-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="auth-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="register.php" class="auth-form">
                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-user"></i> Имя пользователя *
                        </label>
                        <input type="text" id="username" name="username" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i> Email *
                        </label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="full_name">
                            <i class="fas fa-id-card"></i> Полное имя
                        </label>
                        <input type="text" id="full_name" name="full_name">
                    </div>

                    <div class="form-group">
                        <label for="phone">
                            <i class="fas fa-phone"></i> Телефон
                        </label>
                        <input type="tel" id="phone" name="phone" placeholder="+7 (999) 123-45-67">
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Пароль * (минимум 6 символов)
                        </label>
                        <input type="password" id="password" name="password" required minlength="6">
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">
                            <i class="fas fa-lock"></i> Подтвердите пароль *
                        </label>
                        <input type="password" id="password_confirm" name="password_confirm" required minlength="6">
                    </div>

                    <button type="submit" class="btn btn-primary btn-auth">
                        <i class="fas fa-user-plus"></i> Зарегистрироваться
                    </button>
                </form>

                <div class="auth-footer">
                    <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
                </div>
            </div>
        </div>
    </section>

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
