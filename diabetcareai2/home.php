<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DiabetCare AI - Hasta Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
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

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 280px;
            background: rgba(26, 26, 46, 0.95);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            padding: 2rem 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .logo {
            text-align: center;
            font-size: 1.8rem;
            font-weight: 700;
            color: #00d4ff;
            margin-bottom: 3rem;
            padding: 0 1rem;
        }

        .sidebar a {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
            margin: 0.2rem 0;
        }

        .sidebar a:hover {
            color: #00d4ff;
            background: rgba(0, 212, 255, 0.1);
            border-left-color: #00d4ff;
        }

        .sidebar a.active {
            color: #00d4ff;
            background: rgba(0, 212, 255, 0.2);
            border-left-color: #00d4ff;
        }

        .sidebar a i {
            margin-right: 0.8rem;
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
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

        /* Dashboard Cards */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .dashboard-card {
            background: rgba(26, 26, 46, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border-color: rgba(0, 212, 255, 0.3);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
        }

        .card-icon {
            font-size: 1.5rem;
            padding: 0.5rem;
            border-radius: 10px;
        }

        .card-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .card-subtitle {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }

        .card-trend {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        /* Status Colors */
        .status-normal { color: #4ecdc4; }
        .status-warning { color: #f9ca24; }
        .status-danger { color: #ff6b6b; }
        .bg-normal { background: rgba(78, 205, 196, 0.2); color: #4ecdc4; }
        .bg-warning { background: rgba(249, 202, 36, 0.2); color: #f9ca24; }
        .bg-danger { background: rgba(255, 107, 107, 0.2); color: #ff6b6b; }

        /* Charts Section */
        .charts-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .chart-container {
            background: rgba(26, 26, 46, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 1.5rem;
        }

        .chart-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #fff;
        }

        /* AI Suggestions */
        .ai-suggestions {
            background: linear-gradient(135deg, rgba(0, 212, 255, 0.1) 0%, rgba(255, 107, 107, 0.1) 100%);
            border: 1px solid rgba(0, 212, 255, 0.3);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .ai-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #00d4ff;
        }

        .suggestion-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 0.8rem;
            border-left: 4px solid #00d4ff;
        }

        .suggestion-item:last-child {
            margin-bottom: 0;
        }

        /* Recent Activities */
        .recent-activities {
            background: rgba(26, 26, 46, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 1.5rem;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 600;
            margin-bottom: 0.2rem;
        }

        .activity-time {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }

        /* Quick Actions */
        .quick-actions {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .action-btn {
            background: linear-gradient(135deg, #00d4ff 0%, #0099cc 100%);
            color: white;
            border: none;
            border-radius: 15px;
            padding: 1rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 212, 255, 0.3);
        }

        .action-btn.secondary {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
        }

        .action-btn.secondary:hover {
            box-shadow: 0 10px 25px rgba(255, 107, 107, 0.3);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .charts-section {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }
            
            .welcome-section h1 {
                font-size: 2rem;
            }
            
            .quick-actions {
                flex-direction: column;
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
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <i class="fas fa-heartbeat"></i>
            DiabetCare <span style="color: #00d4ff;">AI</span>
        </div>
        
        <a href="#" class="active">
            <i class="fas fa-home"></i>
            Ana Sayfa
        </a>
        <a href="diyabet_tahmin.html">
            <i class="fas fa-tint"></i>
            Diyabet Tahmini
        </a>
        <a href="#">
            <i class="fas fa-utensils"></i>
            Beslenme Kaydı
        </a>
        <a href="#">
            <i class="fas fa-running"></i>
            Aktivite Kaydı
        </a>
        <a href="#">
            <i class="fas fa-brain"></i>
            AI Öneriler
        </a>
        <a href="#">
            <i class="fas fa-chart-line"></i>
            Raporlarım
        </a>
        <a href="#">
            <i class="fas fa-graduation-cap"></i>
            Eğitim Modülü
        </a>
        <a href="#">
            <i class="fas fa-user-md"></i>
            Doktor Mesajları
        </a>
        <a href="#">
            <i class="fas fa-user-cog"></i>
            Profil Ayarları
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header fade-in">
            <div class="welcome-section">
                <h1>Hoş Geldiniz, Ahmet!</h1>
                <p>Diyabet yönetim asistanınız bugün de sizinle</p>
            </div>
            <div class="datetime">
                <div id="current-date"></div>
                <div id="current-time"></div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions fade-in">
            <button class="action-btn">
                <i class="fas fa-plus"></i>
                Kan Şekeri Ekle
            </button>
            <button class="action-btn secondary">
                <i class="fas fa-utensils"></i>
                Öğün Kaydet
            </button>
            <button class="action-btn">
                <i class="fas fa-running"></i>
                Aktivite Kaydet
            </button>
        </div>

        <!-- Dashboard Cards -->
        <div class="dashboard-grid fade-in">
            <div class="dashboard-card">
                <div class="card-header">
                    <span class="card-title">Son Kan Şekeri</span>
                    <div class="card-icon bg-normal">
                        <i class="fas fa-tint"></i>
                    </div>
                </div>
                <div class="card-value status-normal">128 mg/dL</div>
                <div class="card-subtitle">2 saat önce ölçüldü</div>
                <div class="card-trend">
                    <i class="fas fa-arrow-up status-normal"></i>
                    <span>Normal aralıkta</span>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <span class="card-title">Haftalık Ortalama</span>
                    <div class="card-icon bg-warning">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                </div>
                <div class="card-value status-warning">145 mg/dL</div>
                <div class="card-subtitle">Son 7 günün ortalaması</div>
                <div class="card-trend">
                    <i class="fas fa-arrow-down status-warning"></i>
                    <span>Geçen haftaya göre %3 düşük</span>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <span class="card-title">HbA1c Tahmini</span>
                    <div class="card-icon bg-normal">
                        <i class="fas fa-microscope"></i>
                    </div>
                </div>
                <div class="card-value status-normal">6.8%</div>
                <div class="card-subtitle">AI destekli tahmin</div>
                <div class="card-trend">
                    <i class="fas fa-check status-normal"></i>
                    <span>Hedef aralığında</span>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <span class="card-title">Risk Durumu</span>
                    <div class="card-icon bg-normal">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                </div>
                <div class="card-value status-normal">Düşük</div>
                <div class="card-subtitle">Hipoglisemi riski</div>
                <div class="card-trend">
                    <i class="fas fa-thumbs-up status-normal"></i>
                    <span>Güvenli seviyede</span>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <span class="card-title">Bugünkü Kalori</span>
                    <div class="card-icon bg-warning">
                        <i class="fas fa-fire"></i>
                    </div>
                </div>
                <div class="card-value status-warning">1,450</div>
                <div class="card-subtitle">/ 1,800 kcal hedef</div>
                <div class="card-trend">
                    <i class="fas fa-arrow-right status-warning"></i>
                    <span>350 kcal kaldı</span>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <span class="card-title">Günlük Adım</span>
                    <div class="card-icon bg-danger">
                        <i class="fas fa-walking"></i>
                    </div>
                </div>
                <div class="card-value status-danger">4,250</div>
                <div class="card-subtitle">/ 8,000 adım hedef</div>
                <div class="card-trend">
                    <i class="fas fa-arrow-down status-danger"></i>
                    <span>3,750 adım kaldı</span>
                </div>
            </div>
        </div>

        <!-- AI Suggestions -->
        <div class="ai-suggestions fade-in">
            <div class="ai-title">
                <i class="fas fa-brain"></i>
                AI Önerileriniz
            </div>
            <div class="suggestion-item">
                <strong>Beslenme Önerisi:</strong> Öğle yemeğinde kompleks karbonhidrat tüketmeyi tercih edin. Kahverengi pirinç veya tam buğday makarna kan şekerinizi daha stabil tutacaktır.
            </div>
            <div class="suggestion-item">
                <strong>Aktivite Önerisi:</strong> Akşam yemeğinden sonra 15 dakika yürüyüş yapın. Bu, kan şekerinizi %12 oranında düşürmeye yardımcı olacaktır.
            </div>
            <div class="suggestion-item">
                <strong>Ölçüm Hatırlatması:</strong> Sabah ölçümünüzü yapmayı unutmayın! Düzenli ölçüm, AI tahminlerimizi daha doğru hale getirir.
            </div>
        </div>

        <!-- Charts and Recent Activities -->
        <div class="charts-section fade-in">
            <div class="chart-container">
                <div class="chart-title">Son 7 Günlük Kan Şekeri Trendi</div>
                <canvas id="glucoseChart" height="300"></canvas>
            </div>
            
            <div class="recent-activities">
                <div class="chart-title">Son Aktiviteler</div>
                
                <div class="activity-item">
                    <div class="activity-icon bg-normal">
                        <i class="fas fa-tint"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Kan şekeri ölçüldü</div>
                        <div class="activity-time">2 saat önce</div>
                    </div>
                </div>

                <div class="activity-item">
                    <div class="activity-icon bg-warning">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Öğle yemeği kaydedildi</div>
                        <div class="activity-time">4 saat önce</div>
                    </div>
                </div>

                <div class="activity-item">
                    <div class="activity-icon bg-normal">
                        <i class="fas fa-pills"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">İlaç alındı</div>
                        <div class="activity-time">6 saat önce</div>
                    </div>
                </div>

                <div class="activity-item">
                    <div class="activity-icon bg-danger">
                        <i class="fas fa-running"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">30 dk yürüyüş</div>
                        <div class="activity-time">Dün</div>
                    </div>
                </div>

                <div class="activity-item">
                    <div class="activity-icon bg-normal">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">Doktor mesajı var</div>
                        <div class="activity-time">2 gün önce</div>
                    </div>
                </div>
            </div>
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
                element.style.animationDelay = `${index * 0.1}s`;
            });
        });

        // Kan şekeri grafiği
        const ctx = document.getElementById('glucoseChart').getContext('2d');
        
        // Demo veri
        const glucoseData = {
            labels: ['Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'],
            datasets: [{
                label: 'Kan Şekeri (mg/dL)',
                data: [132, 145, 128, 156, 142, 138, 128],
                borderColor: '#00d4ff',
                backgroundColor: 'rgba(0, 212, 255, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                pointBackgroundColor: '#00d4ff',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 6,
                fill: true
            }]
        };

        const glucoseChart = new Chart(ctx, {
            type: 'line',
            data: glucoseData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)'
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)'
                        },
                        min: 80,
                        max: 200
                    }
                }
            }
        });

        // Quick action button events
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Burada ilgili sayfalara yönlendirme yapılabilir
                const text = this.textContent.trim();
                console.log(`${text} butonuna tıklandı`);
                // Örnek: window.location.href = 'ilgili-sayfa.php';
            });
        });
    </script>
</body>
</html>