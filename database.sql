-- База данных для сети кинотеатров
-- Создание базы данных
CREATE DATABASE IF NOT EXISTS cineverse CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cineverse;

-- Таблица 1: Кинотеатры
CREATE TABLE IF NOT EXISTS cinemas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(50),
    email VARCHAR(100),
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица 2: Фильмы
CREATE TABLE IF NOT EXISTS movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    duration INT NOT NULL COMMENT 'Длительность в минутах',
    genre VARCHAR(100),
    rating DECIMAL(3,1) DEFAULT 0.0,
    poster_url TEXT,
    trailer_url VARCHAR(500),
    release_date DATE,
    age_rating VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица 3: Сеансы
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT NOT NULL,
    cinema_id INT NOT NULL,
    session_date DATE NOT NULL,
    session_time TIME NOT NULL,
    hall_number INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    available_seats INT DEFAULT 150,
    total_seats INT DEFAULT 150,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    FOREIGN KEY (cinema_id) REFERENCES cinemas(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_date_time (session_date, session_time),
    INDEX idx_cinema (cinema_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Таблица 4: Бронирования
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(50) NOT NULL,
    seats_count INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    booking_code VARCHAR(20) UNIQUE NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
    INDEX idx_booking_code (booking_code),
    INDEX idx_customer_email (customer_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Вставка тестовых данных

-- Кинотеатры
INSERT INTO cinemas (name, address, phone, email, image_url) VALUES
('Кинотеатр "Премьера"', 'ул. Ленина, 15, г. Москва', '+7 (495) 123-45-67', 'premiera@cinema.ru', 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?w=800'),
('Кинотеатр "Звезда"', 'пр. Мира, 42, г. Москва', '+7 (495) 234-56-78', 'zvezda@cinema.ru', 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?w=800'),
('Кинотеатр "Максимум"', 'ул. Тверская, 8, г. Москва', '+7 (495) 345-67-89', 'maximum@cinema.ru', 'https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?w=800');

-- Фильмы
INSERT INTO movies (title, description, duration, genre, rating, poster_url, release_date, age_rating) VALUES
('Интерстеллар', 'Эпическая научно-фантастическая драма о путешествии через червоточину в поисках нового дома для человечества.', 169, 'Фантастика, Драма', 8.6, 'https://avatars.mds.yandex.net/get-kinopoisk-image/1600647/430042eb-ee69-4818-aed0-a312400a26bf/600x900', '2024-01-15', '12+'),
('Дюна', 'Эпическая сага о пустынной планете Аракис и борьбе за контроль над ценнейшим ресурсом вселенной.', 155, 'Фантастика, Приключения', 8.0, 'https://upload.wikimedia.org/wikipedia/ru/thumb/f/f1/Дюна_официальный_постер.jpg/500px-Дюна_официальный_постер.jpg', '2024-02-01', '12+'),
('Топ Ган: Мэверик', 'Продолжение культового фильма о лучших пилотах военно-морского флота.', 130, 'Экшн, Драма', 8.2, 'https://avatars.mds.yandex.net/get-kinopoisk-image/6201401/57f8f038-f628-44d3-922d-fb28f719ff77/600x900', '2024-01-20', '12+'),
('Аватар: Путь воды', 'Продолжение истории о планете Пандора и её обитателях.', 192, 'Фантастика, Приключения', 7.8, 'https://avatars.mds.yandex.net/get-kinopoisk-image/10768063/035e9573-0377-43b2-b129-c3362bdf36ef/600x900', '2024-02-10', '12+'),
('Оппенгеймер', 'Биографический фильм о создателе атомной бомбы.', 180, 'Драма, Биография', 8.5, 'https://avatars.mds.yandex.net/get-kinopoisk-image/4486454/c5292109-642c-4ab0-894a-cc304e1bcec4/600x900', '2024-01-25', '16+'),
('Барби', 'Комедийный фильм о знаменитой кукле.', 114, 'Комедия, Фэнтези', 7.0, 'https://avatars.mds.yandex.net/get-kinopoisk-image/10592371/f0410960-9002-46a8-bbf3-6acc6a69c411/600x900', '2024-02-05', '12+');

-- Таблица 5: Пользователи
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(255),
    phone VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Тестовый пользователь (пароль: password123)
INSERT INTO users (username, email, password, full_name, phone) VALUES
('admin', 'admin@cineverse.ru', '$2y$10$50Awc6Wg/xEiQSpgQXMdzuJKlbxn9yqKZ09djDwXjerShffvziOqy', 'Администратор', '+7 (495) 123-45-67');

-- Сеансы (на ближайшие 7 дней)
INSERT INTO sessions (movie_id, cinema_id, session_date, session_time, hall_number, price, available_seats, total_seats) VALUES
(1, 1, CURDATE(), '10:00:00', 1, 450.00, 150, 150),
(1, 1, CURDATE(), '14:30:00', 1, 550.00, 120, 150),
(1, 1, CURDATE(), '19:00:00', 1, 650.00, 100, 150),
(2, 1, CURDATE(), '11:00:00', 2, 500.00, 150, 150),
(2, 1, CURDATE(), '16:00:00', 2, 600.00, 130, 150),
(3, 2, CURDATE(), '12:00:00', 1, 480.00, 150, 150),
(3, 2, CURDATE(), '17:30:00', 1, 580.00, 140, 150),
(4, 2, CURDATE(), '13:00:00', 2, 520.00, 150, 150),
(4, 2, CURDATE(), '18:00:00', 2, 620.00, 110, 150),
(5, 3, CURDATE(), '14:00:00', 1, 490.00, 150, 150),
(5, 3, CURDATE(), '19:30:00', 1, 590.00, 125, 150),
(6, 3, CURDATE(), '15:00:00', 2, 470.00, 150, 150),
(6, 3, CURDATE(), '20:00:00', 2, 570.00, 135, 150);
