<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DiabetCare AI - Hasta Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a2e 50%, #16213e 100%);
            color: #ffffff;
            overflow-x: hidden;
            line-height: 1.6;
            min-height: 100vh;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 50%, rgba(0, 212, 255, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(255, 107, 107, 0.1) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }

        /* Main Content */
        .main-content {
            padding: 2rem;
            min-height: 100vh;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logout-btn {
            position: fixed;
            top: 2rem;
            right: 2rem;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
            border: none;
            border-radius: 15px;
            padding: 0.8rem 1.2rem;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            z-index: 1000;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 107, 107, 0.3);
        }

        .logout-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 107, 107, 0.3);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
        }

        .welcome-section h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #ffffff 0%, #00d4ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .welcome-section p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1rem;
        }

        .datetime {
            text-align: right;
            color: rgba(255, 255, 255, 0.6);
        }

        /* Module Cards Grid */
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .module-card {
            background: rgba(26, 26, 46, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 2.5rem;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            position: relative;
            overflow: hidden;
        }

        .module-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, transparent 0%, rgba(0, 212, 255, 0.1) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .module-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
            border-color: rgba(0, 212, 255, 0.5);
        }

        .module-card:hover::before {
            opacity: 1;
        }

        .module-card:hover .module-icon {
            transform: scale(1.1);
            color: #00d4ff;
        }

        .module-icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            color: #00d4ff;
            transition: all 0.3s ease;
            display: block;
        }

        .module-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #fff;
            position: relative;
            z-index: 1;
        }

        .module-description {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.1rem;
            line-height: 1.6;
            position: relative;
            z-index: 1;
        }

        .module-features {
            margin-top: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.95rem;
        }

        .feature-item i {
            color: #4ecdc4;
            font-size: 0.8rem;
        }

        /* Special styling for different modules */
        .module-card.prediction {
            background: rgba(78, 205, 196, 0.1);
            border-color: rgba(78, 205, 196, 0.3);
        }

        .module-card.nutrition {
            background: rgba(249, 202, 36, 0.1);
            border-color: rgba(249, 202, 36, 0.3);
        }

        .module-card.activity {
            background: rgba(255, 107, 107, 0.1);
            border-color: rgba(255, 107, 107, 0.3);
        }

        .module-card.chatbot {
            background: rgba(138, 43, 226, 0.1);
            border-color: rgba(138, 43, 226, 0.3);
        }

        .module-card.symptoms {
            background: rgba(255, 165, 0, 0.1);
            border-color: rgba(255, 165, 0, 0.3);
        }

        /* Welcome Message Section */
        .welcome-message {
            background: linear-gradient(135deg, rgba(0, 212, 255, 0.1) 0%, rgba(255, 107, 107, 0.1) 100%);
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 3rem;
            text-align: center;
        }

        .welcome-message h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #00d4ff;
        }

        .welcome-message p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
            line-height: 1.6;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }
            
            .modules-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .header {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }
            
            .welcome-section h1 {
                font-size: 2rem;
            }

            .module-card {
                padding: 2rem;
            }

            .module-icon {
                font-size: 3rem;
            }

            .module-title {
                font-size: 1.5rem;
            }

            .logout-btn {
                top: 1rem;
                right: 1rem;
                padding: 0.6rem 1rem;
                font-size: 0.8rem;
            }
        }

        /* Animation */
        .fade-in {
            opacity: 0;
            animation: fadeInUp 0.8s ease-out forwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <!-- Main Content -->
    <div class="main-content">
        <!-- Logout Button -->
        <button class="logout-btn" onclick="window.location.href='index.html'">
            <i class="fas fa-sign-out-alt"></i>
            Çıkış Yap
        </button>

        <!-- Header -->
        <div class="header fade-in">
            <div class="welcome-section">
                <h1>Hoş Geldiniz!</h1>
                <p>Diyabet yönetim asistanınız bugün de sizinle</p>
            </div>
            <div class="datetime">
                <div id="current-date"></div>
                <div id="current-time"></div>
            </div>
        </div>

        <!-- Welcome Message -->
        <div class="welcome-message fade-in">
            <h2>DiabetCare AI ile Sağlığınızı Yönetin</h2>
            <p>Yapay zeka destekli modüllerimiz ile diyabet yönetiminizi kolaylaştırın. Hangi özelliği kullanmak istediğinizi seçin.</p>
        </div>

        <!-- Modules Grid -->
        <div class="modules-grid fade-in">
            <a href="diyabet_tahmin.html" class="module-card prediction">
                <i class="fas fa-tint module-icon"></i>
                <h3 class="module-title">Diyabet Risk Tahmini</h3>
                <p class="module-description">Yapay zeka ile diyabet risk analizinizi yapın ve kişiselleştirilmiş öneriler alın.</p>
                <div class="module-features">
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>AI destekli risk analizi</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Kişiselleştirilmiş öneriler</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Detaylı risk raporu</span>
                    </div>
                </div>
            </a>

            <a href="beslenme_modul.html" class="module-card nutrition">
                <i class="fas fa-utensils module-icon"></i>
                <h3 class="module-title">Beslenme Analizi</h3>
                <p class="module-description">Tükettiğiniz gıdaların kan şekeriniz üzerindeki etkisini analiz edin.</p>
                <div class="module-features">
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Gıda etkisi analizi</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Kalori ve karbonhidrat takibi</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Beslenme önerileri</span>
                    </div>
                </div>
            </a>

            <a href="aktivite_modul.html" class="module-card activity">
                <i class="fas fa-running module-icon"></i>
                <h3 class="module-title">Aktivite Önerileri</h3>
                <p class="module-description">Size özel fiziksel aktivite önerileri alın ve kan şekerinizi kontrol altında tutun.</p>
                <div class="module-features">
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Kişisel aktivite planı</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Kan şekeri etkisi analizi</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>İlerleme takibi</span>
                    </div>
                </div>
            </a>

            <a href="chatbot.html" class="module-card chatbot">
                <i class="fas fa-brain module-icon"></i>
                <h3 class="module-title">AI Chatbot</h3>
                <p class="module-description">Diyabet yönetimi hakkında sorularınızı sorun ve anında cevap alın.</p>
                <div class="module-features">
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>7/24 AI desteği</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Uzman bilgi bankası</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Hızlı cevaplar</span>
                    </div>
                </div>
            </a>

            <a href="semptom_modul.html" class="module-card symptoms">
                <i class="fas fa-chart-line module-icon"></i>
                <h3 class="module-title">Semptom Analizi</h3>
                <p class="module-description">Semptomlarınızı takip edin ve AI destekli diyabet analizi yapın.</p>
                <div class="module-features">
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Semptom takibi</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>AI destekli analiz</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Trend raporları</span>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <script>
        // Tarih ve saat güncelleme
        function updateDateTime() {
            const now = new Date();
            const dateOptions = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            };
            const timeOptions = { 
                hour: '2-digit', 
                minute: '2-digit', 
                second: '2-digit' 
            };
            
            document.getElementById('current-date').textContent = 
                now.toLocaleDateString('tr-TR', dateOptions);
            document.getElementById('current-time').textContent = 
                now.toLocaleTimeString('tr-TR', timeOptions);
        }

        // Sayfa yüklendiğinde ve her saniye güncelle
        updateDateTime();
        setInterval(updateDateTime, 1000);

        // Animasyon gecikmelerini ayarla
        document.addEventListener('DOMContentLoaded', () => {
            const elements = document.querySelectorAll('.fade-in');
            elements.forEach((element, index) => {
                element.style.animationDelay = `${index * 0.2}s`;
            });
        });

        // Module card hover effects
        document.querySelectorAll('.module-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
</body>
</html>