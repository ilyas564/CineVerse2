// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    // Мобильное меню
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    
    if (hamburger && navMenu) {
        hamburger.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            hamburger.classList.toggle('active');
        });
    }

    document.addEventListener('click', function(e) {
        if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
            navMenu.classList.remove('active');
            hamburger.classList.remove('active');
        }
    });

    // Эффект прокрутки навбара
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // Фильтрация сеансов по датам
    const dateButtons = document.querySelectorAll('.date-btn');
    const sessionsDays = document.querySelectorAll('.sessions-day');
    const noSessionsEl = document.getElementById('noSessions');
    
    if (dateButtons.length > 0) {
        dateButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const selectedDate = this.getAttribute('data-date');
                
                dateButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                let found = false;
                sessionsDays.forEach(day => {
                    if (day.getAttribute('data-date') === selectedDate) {
                        day.style.display = 'grid';
                        found = true;
                        const cards = day.querySelectorAll('.session-card');
                        cards.forEach((card, index) => {
                            card.style.animation = 'none';
                            setTimeout(() => {
                                card.style.animation = 'fadeIn 0.5s ease ' + (index * 0.1) + 's both';
                            }, 10);
                        });
                    } else {
                        day.style.display = 'none';
                    }
                });
                
                if (noSessionsEl) {
                    noSessionsEl.style.display = found ? 'none' : 'block';
                }
            });
        });
    }

    // Плавная прокрутка по якорям
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Анимация элементов при скролле
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    document.querySelectorAll('.animate-fade-in, .feature-card, .movie-card').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
});

window.addEventListener('scroll', function() {
    const scrolled = window.pageYOffset;
    const hero = document.querySelector('.hero');
    if (hero) {
        hero.style.transform = `translateY(${scrolled * 0.5}px)`;
    }
});

// Фильтрация фильмов
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const genreFilter = document.getElementById('genreFilter');
    const movieCards = document.querySelectorAll('.movie-card');
    const noResults = document.querySelector('.no-results');

    function filterMovies() {
        if (!searchInput || !genreFilter || !movieCards.length) return;
        
        const searchTerm = searchInput.value.toLowerCase().trim();
        const selectedGenre = genreFilter.value;
        let visibleCount = 0;

        movieCards.forEach(card => {
            const title = card.getAttribute('data-title') || '';
            const genre = card.getAttribute('data-genre') || '';
            
            const matchesSearch = title.includes(searchTerm);
            const matchesGenre = !selectedGenre || genre.includes(selectedGenre);
            
            if (matchesSearch && matchesGenre) {
                card.style.display = 'block';
                visibleCount++;
                card.style.animation = 'fadeIn 0.5s ease';
            } else {
                card.style.display = 'none';
            }
        });

        if (noResults) {
            if (visibleCount === 0) {
                noResults.style.display = 'block';
            } else {
                noResults.style.display = 'none';
            }
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterMovies);
    }

    if (genreFilter) {
        genreFilter.addEventListener('change', filterMovies);
    }

    movieCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
});

// Бронирование мест
document.addEventListener('DOMContentLoaded', function() {
    const hallSeats = document.getElementById('hallSeats');
    const bookingForm = document.getElementById('bookingForm');
    const selectedSeatsInput = document.getElementById('selectedSeats');
    const totalPriceInput = document.getElementById('totalPrice');
    const seatsCountSpan = document.getElementById('seatsCount');
    const totalAmountSpan = document.getElementById('totalAmount');
    const submitBtn = document.getElementById('submitBtn');

    if (!hallSeats) return;

    const sessionId = hallSeats.getAttribute('data-session-id');
    const pricePerSeat = parseFloat(hallSeats.getAttribute('data-price'));
    const totalSeats = parseInt(hallSeats.getAttribute('data-total-seats'));
    
    let selectedSeats = [];
    let occupiedSeats = [];

    // Генерация схемы зала
    function generateHall() {
        const rows = Math.ceil(totalSeats / 15);
        let seatNumber = 1;

        for (let row = 0; row < rows; row++) {
            for (let col = 0; col < 15 && seatNumber <= totalSeats; col++) {
                const seat = document.createElement('div');
                seat.className = 'seat available';
                seat.setAttribute('data-seat', seatNumber);
                seat.textContent = seatNumber;
                
                if (Math.random() < 0.2 && seatNumber > 1) {
                    seat.classList.remove('available');
                    seat.classList.add('occupied');
                    occupiedSeats.push(seatNumber);
                }
                
                seat.addEventListener('click', function() {
                    if (this.classList.contains('occupied')) {
                        return;
                    }
                    
                    const seatNum = parseInt(this.getAttribute('data-seat'));
                    
                    if (this.classList.contains('selected')) {
                        this.classList.remove('selected');
                        this.classList.add('available');
                        selectedSeats = selectedSeats.filter(s => s !== seatNum);
                    } else {
                        this.classList.remove('available');
                        this.classList.add('selected');
                        selectedSeats.push(seatNum);
                    }
                    
                    updateSummary();
                });
                
                hallSeats.appendChild(seat);
                seatNumber++;
            }
        }
    }

    // Обновление итоговой суммы
    function updateSummary() {
        const count = selectedSeats.length;
        const total = count * pricePerSeat;
        
        if (seatsCountSpan) seatsCountSpan.textContent = count;
        if (totalAmountSpan) totalAmountSpan.textContent = total.toLocaleString('ru-RU') + ' ₽';
        if (selectedSeatsInput) selectedSeatsInput.value = selectedSeats.join(',');
        if (totalPriceInput) totalPriceInput.value = total;
        
        if (submitBtn) {
            if (count > 0) {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }
        }
    }

    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            if (selectedSeats.length === 0) {
                e.preventDefault();
                alert('Пожалуйста, выберите хотя бы одно место');
                return false;
            }

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Обработка...';
            }
        });
    }

    generateHall();
    updateSummary();

    const seats = hallSeats.querySelectorAll('.seat');
    seats.forEach((seat, index) => {
        seat.style.opacity = '0';
        setTimeout(() => {
            seat.style.transition = 'opacity 0.3s ease';
            seat.style.opacity = '1';
        }, index * 10);
    });
});

// Форма обратной связи
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');

    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(contactForm);
            const data = {
                name: formData.get('name'),
                email: formData.get('email'),
                subject: formData.get('subject'),
                message: formData.get('message')
            };

            if (!data.name || !data.email || !data.message) {
                alert('Пожалуйста, заполните все обязательные поля');
                return;
            }

            const submitBtn = contactForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Отправка...';

            setTimeout(() => {
                alert('Спасибо за ваше сообщение! Мы свяжемся с вами в ближайшее время.');
                contactForm.reset();
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }, 1500);
        });
    }

    const cinemaCards = document.querySelectorAll('.cinema-card');
    cinemaCards.forEach((card) => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.02)';
            this.style.boxShadow = '0 10px 30px rgba(229, 9, 20, 0.3)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
            this.style.boxShadow = 'none';
        });
    });

    const socialLinks = document.querySelectorAll('.social-link');
    socialLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.2) rotate(5deg)';
        });

        link.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1) rotate(0)';
        });
    });
});

// Валидация форм авторизации
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirm');
    const registerForm = document.querySelector('form[action="register.php"]');

    if (passwordConfirmInput && registerForm) {
        function validatePasswords() {
            if (passwordInput.value && passwordConfirmInput.value) {
                if (passwordInput.value !== passwordConfirmInput.value) {
                    passwordConfirmInput.setCustomValidity('Пароли не совпадают');
                    passwordConfirmInput.style.borderColor = 'var(--primary-color)';
                } else {
                    passwordConfirmInput.setCustomValidity('');
                    passwordConfirmInput.style.borderColor = '';
                }
            }
        }

        passwordInput.addEventListener('input', validatePasswords);
        passwordConfirmInput.addEventListener('input', validatePasswords);

        registerForm.addEventListener('submit', function(e) {
            if (passwordInput.value !== passwordConfirmInput.value) {
                e.preventDefault();
                alert('Пароли не совпадают!');
                return false;
            }
        });
    }

    const formGroups = document.querySelectorAll('.auth-form .form-group');
    formGroups.forEach((group, index) => {
        group.style.opacity = '0';
        group.style.transform = 'translateY(20px)';
        setTimeout(() => {
            group.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            group.style.opacity = '1';
            group.style.transform = 'translateY(0)';
        }, index * 100);
    });
});

// Кнопка "Наверх" и чат поддержки
document.addEventListener('DOMContentLoaded', function() {
    const scrollToTopBtn = document.getElementById('scrollToTop');
    const openChatBtn = document.getElementById('openChat');
    const closeChatBtn = document.getElementById('closeChat');
    const chatWidget = document.getElementById('chatWidget');
    const chatInput = document.getElementById('chatInput');
    const sendMessageBtn = document.getElementById('sendMessage');
    const chatMessages = document.getElementById('chatMessages');

    if (scrollToTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollToTopBtn.classList.add('show');
            } else {
                scrollToTopBtn.classList.remove('show');
            }
        });

        scrollToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    if (openChatBtn) {
        openChatBtn.classList.add('show');
        openChatBtn.addEventListener('click', function() {
            chatWidget.classList.add('active');
            chatInput.focus();
        });
    }

    if (closeChatBtn) {
        closeChatBtn.addEventListener('click', function() {
            chatWidget.classList.remove('active');
        });
    }

    // Добавление сообщения в чат
    function addMessage(text, isUser) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'chat-message ' + (isUser ? 'user' : 'bot');
        messageDiv.innerHTML = '<p>' + text + '</p>';
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Отправка сообщения в чат
    function sendMessage() {
        const message = chatInput.value.trim();
        if (message) {
            addMessage(message, true);
            chatInput.value = '';

            setTimeout(() => {
                const responses = [
                    'Спасибо за ваше сообщение! Мы ответим в ближайшее время.',
                    'Понял, передам ваш вопрос специалисту.',
                    'Хорошо, уточню детали и вернусь к вам.',
                    'Принято, обрабатываю ваш запрос.'
                ];
                const randomResponse = responses[Math.floor(Math.random() * responses.length)];
                addMessage(randomResponse, false);
            }, 1000);
        }
    }

    if (sendMessageBtn && chatInput) {
        sendMessageBtn.addEventListener('click', sendMessage);
        chatInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    }
});
