<?php
/**
 * DiabetCare AI - Semptom Bildirimi API
 * Python ML modelini PHP'den çağıran API
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// OPTIONS request için
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

class SymptomAnalyzer {
    private $pythonPath;
    private $scriptPath;
    
    public function __construct() {
        // Python yolu (sisteminize göre ayarlayın)
        $this->pythonPath = 'python'; // Windows: python.exe
        $this->scriptPath = __DIR__ . '/analyze_symptoms.py';
    }
    
    /**
     * Semptom analizini başlat
     */
    public function analyzeSymptoms($data) {
        try {
            // Veriyi JSON olarak hazırla
            $inputData = json_encode($data);
            
            // Python scriptini çalıştır
            $command = sprintf(
                '%s %s %s 2>&1',
                escapeshellcmd($this->pythonPath),
                escapeshellarg($this->scriptPath),
                escapeshellarg($inputData)
            );
            
            $output = shell_exec($command);
            
            if ($output === null) {
                throw new Exception('Python script çalıştırılamadı');
            }
            
            // JSON çıktısını parse et
            $result = json_decode(trim($output), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Python scriptinden geçersiz JSON: ' . $output);
            }
            
            return $result;
            
        } catch (Exception $e) {
            // Hata durumunda basit kural tabanlı analiz yap
            return $this->fallbackAnalysis($data);
        }
    }
    
    /**
     * Python modeli çalışmazsa alternatif analiz
     */
    private function fallbackAnalysis($data) {
        $riskScore = 0;
        $riskFactors = [];
        
        // Yaş riski
        if ($data['age'] > 50) {
            $riskScore += 2;
            $riskFactors[] = 'Yaş faktörü';
        } elseif ($data['age'] > 35) {
            $riskScore += 1;
        }
        
        // BMI riski
        if ($data['bmi'] > 30) {
            $riskScore += 2;
            $riskFactors[] = 'Obezite';
        } elseif ($data['bmi'] > 25) {
            $riskScore += 1;
            $riskFactors[] = 'Fazla kilo';
        }
        
        // Kan şekeri riski
        if ($data['glucose'] > 140) {
            $riskScore += 3;
            $riskFactors[] = 'Yüksek kan şekeri';
        } elseif ($data['glucose'] > 100) {
            $riskScore += 1;
            $riskFactors[] = 'Sınırda kan şekeri';
        }
        
        // Kan basıncı riski
        if ($data['blood_pressure'] > 90) {
            $riskScore += 1;
            $riskFactors[] = 'Yüksek kan basıncı';
        }
        
        // Semptom riski
        $symptomCount = count($data['symptoms']);
        $riskScore += $symptomCount * $data['severity'];
        
        if ($symptomCount > 3) {
            $riskFactors[] = 'Çoklu semptom';
        }
        
        // Risk kategorisi belirle
        if ($riskScore >= 8) {
            $riskCategory = 'Yüksek Risk';
            $probability = min(90, 60 + $riskScore * 3);
            $recommendation = '🚨 Acil doktor konsültasyonu önerilir! Semptomlarınız ciddi risk gösteriyor.';
        } elseif ($riskScore >= 4) {
            $riskCategory = 'Orta Risk';
            $probability = min(70, 30 + $riskScore * 5);
            $recommendation = '⚠️ Doktor kontrolü ve yaşam tarzı değişiklikleri önerilir.';
        } else {
            $riskCategory = 'Düşük Risk';
            $probability = min(40, 10 + $riskScore * 5);
            $recommendation = '✅ Düzenli takip ve sağlıklı yaşam önerilir.';
        }
        
        return [
            'tahmin' => $riskScore > 5 ? 'Diyabet Riski Var' : 'Diyabet Riski Düşük',
            'olasillik' => $probability . '%',
            'risk_kategorisi' => $riskCategory,
            'risk_skoru' => $riskScore,
            'oneri' => $recommendation,
            'risk_factors' => $riskFactors,
            'method' => 'fallback' // Test için
        ];
    }
}

// Ana işlem
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Sadece POST method desteklenmektedir');
    }
    
    // JSON verisini al
    $jsonInput = file_get_contents('php://input');
    $data = json_decode($jsonInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Geçersiz JSON verisi');
    }
    
    // Gerekli alanları kontrol et
    $requiredFields = ['age', 'bmi', 'glucose', 'blood_pressure', 'symptoms', 'severity'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Eksik alan: $field");
        }
    }
    
    // Veri doğrulama
    if ($data['age'] < 18 || $data['age'] > 100) {
        throw new Exception('Yaş 18-100 arasında olmalıdır');
    }
    
    if ($data['bmi'] < 15 || $data['bmi'] > 50) {
        throw new Exception('BMI 15-50 arasında olmalıdır');
    }
    
    if ($data['glucose'] < 50 || $data['glucose'] > 400) {
        throw new Exception('Kan şekeri 50-400 mg/dL arasında olmalıdır');
    }
    
    if (!is_array($data['symptoms'])) {
        throw new Exception('Semptomlar dizi formatında olmalıdır');
    }
    
    // Analiz yap
    $analyzer = new SymptomAnalyzer();
    $result = $analyzer->analyzeSymptoms($data);
    
    // Başarılı yanıt
    echo json_encode([
        'success' => true,
        'data' => $result,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    // Hata yanıtı
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>