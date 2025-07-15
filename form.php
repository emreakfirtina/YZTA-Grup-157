<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DiabetCare AI - Diyabet Tahmin Formu</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0a0a0a;
            color: #ffffff;
            min-height: 100vh;
            line-height: 1.6;
        }

        .navbar {
            background: rgba(10, 10, 10, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: #00d4ff;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #b0b0b0;
        }

        .main-container {
            margin-top: 80px;
            padding: 2rem;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        .form-header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .form-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #ffffff 0%, #00d4ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .form-header p {
            color: #b0b0b0;
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 3rem;
            margin-bottom: 2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group label {
            font-weight: 600;
            color: #ffffff;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group label i {
            color: #00d4ff;
            font-size: 1.1rem;
        }

        .form-group input,
        .form-group select {
            padding: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #00d4ff;
            box-shadow: 0 0 0 3px rgba(0, 212, 255, 0.1);
            background: rgba(255, 255, 255, 0.12);
        }

        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: #00d4ff;
        }

        .checkbox-group label {
            margin: 0;
            font-weight: 500;
        }

        .submit-btn {
            background: linear-gradient(135deg, #00d4ff 0%, #0099cc 100%);
            color: white;
            border: none;
            padding: 1.2rem 3rem;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 2rem;
            position: relative;
            overflow: hidden;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 212, 255, 0.3);
        }

        .submit-btn i {
            margin-right: 0.5rem;
        }

        .result-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 2rem;
            margin-top: 2rem;
            display: none;
        }

        .result-container.show {
            display: block;
            animation: fadeInUp 0.6s ease-out;
        }

        .result-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .result-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #00d4ff;
            margin-bottom: 1rem;
        }

        .risk-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .risk-badge {
            padding: 0.8rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            text-align: center;
        }

        .risk-low {
            background: linear-gradient(135deg, #4ecdc4 0%, #44a08d 100%);
            color: white;
        }

        .risk-medium {
            background: linear-gradient(135deg, #f9ca24 0%, #f0932b 100%);
            color: white;
        }

        .risk-high {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
        }

        .probability-display {
            text-align: center;
            font-size: 1.2rem;
            color: #b0b0b0;
            margin-bottom: 2rem;
        }

        .probability-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: #00d4ff;
            display: block;
            margin: 0.5rem 0;
        }

        .recommendations {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 16px;
            padding: 2rem;
            margin-top: 2rem;
        }

        .recommendations h3 {
            color: #00d4ff;
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        .recommendations ul {
            list-style: none;
        }

        .recommendations li {
            padding: 0.5rem 0;
            color: #b0b0b0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .recommendations li i {
            color: #00d4ff;
            font-size: 0.9rem;
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

        .floating-elements {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        .floating-element {
            position: absolute;
            color: rgba(0, 212, 255, 0.05);
            font-size: 1.5rem;
            animation: float 8s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .floating-element:nth-child(1) { top: 20%; left: 10%; animation-delay: 0s; }
        .floating-element:nth-child(2) { top: 60%; left: 85%; animation-delay: 2s; }
        .floating-element:nth-child(3) { top: 40%; left: 5%; animation-delay: 4s; }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-header h1 {
                font-size: 2rem;
            }
            
            .main-container {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="floating-elements">
        <i class="floating-element fas fa-heartbeat"></i>
        <i class="floating-element fas fa-chart-line"></i>
        <i class="floating-element fas fa-user-md"></i>
    </div>

    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <i class="fas fa-heartbeat"></i>
                DiabetCare AI
            </div>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span>Hasta Paneli</span>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <div class="form-header">
            <h1><i class="fas fa-chart-line"></i> Diyabet Tahmin Formu</h1>
            <p>Sağlık verilerinizi girerek diyabet risk değerlendirmesi yapabilirsiniz. Tüm bilgiler güvenli bir şekilde saklanır ve sadece sağlık takibiniz için kullanılır.</p>
        </div>

        <form class="form-container" id="diabetForm">
            <div class="form-grid">
                <div class="form-group">
                    <label for="hamilelik_sayisi">
                        <i class="fas fa-baby"></i>
                        Hamilelik Sayısı
                    </label>
                    <input type="number" id="hamilelik_sayisi" name="hamilelik_sayisi" min="0" max="20" value="0" required>
                </div>

                <div class="form-group">
                    <label for="glukoz">
                        <i class="fas fa-tint"></i>
                        Glukoz (mg/dL)
                    </label>
                    <input type="number" id="glukoz" name="glukoz" step="0.01" min="0" max="500" placeholder="Örn: 120" required>
                </div>

                <div class="form-group">
                    <label for="kan_basinci_sistolik">
                        <i class="fas fa-heartbeat"></i>
                        Kan Basıncı Sistolik (mmHg)
                    </label>
                    <input type="number" id="kan_basinci_sistolik" name="kan_basinci_sistolik" min="0" max="300" placeholder="Örn: 120">
                </div>

                <div class="form-group">
                    <label for="kan_basinci_diyastolik">
                        <i class="fas fa-heartbeat"></i>
                        Kan Basıncı Diyastolik (mmHg)
                    </label>
                    <input type="number" id="kan_basinci_diyastolik" name="kan_basinci_diyastolik" min="0" max="200" placeholder="Örn: 80">
                </div>

                <div class="form-group">
                    <label for="cilt_kalinligi">
                        <i class="fas fa-ruler"></i>
                        Cilt Kalınlığı (mm)
                    </label>
                    <input type="number" id="cilt_kalinligi" name="cilt_kalinligi" step="0.01" min="0" max="100" placeholder="Örn: 25.5">
                </div>

                <div class="form-group">
                    <label for="insulin">
                        <i class="fas fa-syringe"></i>
                        İnsülin (mU/L)
                    </label>
                    <input type="number" id="insulin" name="insulin" step="0.01" min="0" max="1000" placeholder="Örn: 15.2">
                </div>

                <div class="form-group">
                    <label for="bmi">
                        <i class="fas fa-weight"></i>
                        BMI (Vücut Kitle İndeksi)
                    </label>
                    <input type="number" id="bmi" name="bmi" step="0.01" min="0" max="80" placeholder="Örn: 24.5" required>
                </div>

                <div class="form-group">
                    <label for="yas">
                        <i class="fas fa-birthday-cake"></i>
                        Yaş
                    </label>
                    <input type="number" id="yas" name="yas" min="1" max="120" placeholder="Örn: 35" required>
                </div>
            </div>

            <div class="form-group">
                <label>
                    <i class="fas fa-dna"></i>
                    Diyabet Soy Geçmişi
                </label>
                <div class="checkbox-group">
                    <input type="checkbox" id="diyabet_soy_gecmisi" name="diyabet_soy_gecmisi" value="1">
                    <label for="diyabet_soy_gecmisi">Ailemde diyabet hastalığı geçmişi var</label>
                </div>
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-brain"></i>
                AI ile Diyabet Risk Analizi Yap
            </button>
        </form>

        <div class="result-container" id="resultContainer">
            <div class="result-header">
                <h2>Diyabet Risk Analizi Sonucu</h2>
                <div class="risk-indicator">
                    <div class="risk-badge" id="riskBadge">Düşük Risk</div>
                </div>
                <div class="probability-display">
                    Diyabet Olasılığı: <span class="probability-value" id="probabilityValue">0.00%</span>
                </div>
            </div>

            <div class="recommendations" id="recommendations">
                <h3><i class="fas fa-lightbulb"></i> AI Önerileri</h3>
                <ul id="recommendationsList">
                    <li><i class="fas fa-check-circle"></i> Düzenli egzersiz yapın</li>
                    <li><i class="fas fa-check-circle"></i> Dengeli beslenmeye dikkat edin</li>
                    <li><i class="fas fa-check-circle"></i> Düzenli doktor kontrolü yaptırın</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('diabetForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Form verilerini topla
            const formData = new FormData(this);
            const data = {};
            
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }
            
            // Diyabet soy geçmişi checkbox kontrolü
            data.diyabet_soy_gecmisi = document.getElementById('diyabet_soy_gecmisi').checked ? 1 : 0;
            
            // Simülasyon için basit bir risk hesaplama
            let riskSkoru = calculateRisk(data);
            
            // Sonucu göster
            displayResult(riskSkoru);
            
            // Bu kısımda gerçek uygulamada AJAX ile PHP'ye göndereceğiz
            console.log('Form verileri:', data);
            console.log('Risk skoru:', riskSkoru);
        });

        function calculateRisk(data) {
            let risk = 0;
            
            // Basit risk hesaplama algoritması (gerçek AI modeliyle değiştirilecek)
            if (data.glukoz > 125) risk += 0.3;
            if (data.bmi > 30) risk += 0.2;
            if (data.yas > 45) risk += 0.2;
            if (data.diyabet_soy_gecmisi == 1) risk += 0.2;
            if (data.kan_basinci_sistolik > 140) risk += 0.1;
            
            return Math.min(risk, 1); // 0-1 arası sınırla
        }

        function displayResult(riskSkoru) {
            const resultContainer = document.getElementById('resultContainer');
            const riskBadge = document.getElementById('riskBadge');
            const probabilityValue = document.getElementById('probabilityValue');
            const recommendationsList = document.getElementById('recommendationsList');
            
            // Risk seviyesini belirle
            let riskSeviye, riskClass, oneriler;
            
            if (riskSkoru < 0.3) {
                riskSeviye = 'Düşük Risk';
                riskClass = 'risk-low';
                oneriler = [
                    'Mevcut sağlıklı yaşam tarzınızı sürdürün',
                    'Yılda bir kez rutin kontrol yaptırın',
                    'Dengeli beslenmeye devam edin',
                    'Düzenli fiziksel aktivite yapın'
                ];
            } else if (riskSkoru < 0.7) {
                riskSeviye = 'Orta Risk';
                riskClass = 'risk-medium';
                oneriler = [
                    'Doktorunuzla görüşmeyi planlayın',
                    '6 ayda bir kontrol yaptırın',
                    'Kilo kontrolüne dikkat edin',
                    'Şekerli yiyecekleri sınırlayın',
                    'Düzenli egzersiz programı başlatın'
                ];
            } else {
                riskSeviye = 'Yüksek Risk';
                riskClass = 'risk-high';
                oneriler = [
                    'Acil olarak doktorunuza başvurun',
                    'Detaylı kan tahlili yaptırın',
                    'Beslenme uzmanından destek alın',
                    'Günlük kan şekeri takibi yapın',
                    'Stres yönetimi tekniklerini öğrenin'
                ];
            }
            
            // Sonuçları güncelle
            riskBadge.textContent = riskSeviye;
            riskBadge.className = `risk-badge ${riskClass}`;
            probabilityValue.textContent = `${(riskSkoru * 100).toFixed(2)}%`;
            
            // Önerileri güncelle
            recommendationsList.innerHTML = '';
            oneriler.forEach(oneri => {
                const li = document.createElement('li');
                li.innerHTML = `<i class="fas fa-check-circle"></i> ${oneri}`;
                recommendationsList.appendChild(li);
            });
            
            // Sonuç containerını göster
            resultContainer.classList.add('show');
            resultContainer.scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html>