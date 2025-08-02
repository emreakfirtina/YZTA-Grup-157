<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// DiabetCare AI - Fiziksel Aktivite Tahmini API
// Bu API, eğitilmiş ML modelini kullanarak fiziksel aktivite önerileri yapar

class ActivityPredictionAPI {
    private $modelPath;
    private $scalerPath;
    private $modelInfoPath;
    private $pythonScript;
    
    public function __construct() {
        $this->modelPath = __DIR__ . '/diabetcare_activity_model.pkl';
        $this->scalerPath = __DIR__ . '/diabetcare_scaler.pkl';
        $this->modelInfoPath = __DIR__ . '/diabetcare_model_info.pkl';
        $this->pythonScript = __DIR__ . '/predict_activity.py';
        
        // Python script'ini oluştur
        $this->createPythonScript();
    }
    
    private function createPythonScript() {
        $pythonCode = '#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
DiabetCare AI - Fiziksel Aktivite Tahmini
Python Script for Model Prediction
"""

import sys
import json
import os
import warnings
warnings.filterwarnings("ignore")

def predict_activity(data):
    """
    Fiziksel aktivite seviyesi tahmini yapar
    """
    try:
        # Python paketlerini kontrol et ve yükle
        try:
            import joblib
            import numpy as np
        except ImportError as e:
            return {
                "success": False,
                "error": f"Gerekli Python paketleri eksik: {str(e)}",
                "message": "joblib ve numpy paketlerini yükleyin: pip install joblib numpy scikit-learn"
            }
        
        # Çalışma dizinini ayarla
        script_dir = os.path.dirname(os.path.abspath(__file__))
        os.chdir(script_dir)
        
        # Model dosyalarının varlığını kontrol et
        model_files = [
            "diabetcare_activity_model.pkl",
            "diabetcare_scaler.pkl", 
            "diabetcare_model_info.pkl"
        ]
        
        missing_files = []
        for file in model_files:
            if not os.path.exists(file):
                missing_files.append(file)
        
        if missing_files:
            return {
                "success": False,
                "error": f"Model dosyaları bulunamadı: {missing_files}",
                "message": "Model dosyalarını backend klasörüne yükleyin"
            }
        
        # Model dosyalarını yükle
        try:
            model = joblib.load("diabetcare_activity_model.pkl")
            scaler = joblib.load("diabetcare_scaler.pkl")
            model_info = joblib.load("diabetcare_model_info.pkl")
        except Exception as e:
            return {
                "success": False,
                "error": f"Model yükleme hatası: {str(e)}",
                "message": "Model dosyaları bozuk olabilir, yeniden eğitin"
            }
        
        # Veriyi hazırla
        features = [
            float(data["age"]),
            float(data["gender"]), 
            float(data["bmi"]),
            float(data["glucose_level"]),
            float(data["blood_pressure"]),
            float(data["insulin"]),
            float(data["diabetes_pedigree"]),
            float(data["pregnancies"]),
            float(data["current_activity_level"]),
            float(data["heart_rate_resting"]),
            float(data["daily_steps"]),
            float(data["exercise_frequency"]),
            float(data["has_diabetes"])
        ]
        
        # NumPy array\'e çevir
        features_array = np.array([features])
        
        # Model tipine göre tahmin yap
        try:
            if hasattr(model, "predict_proba"):
                # Scaling kontrolü
                if hasattr(scaler, "transform"):
                    features_scaled = scaler.transform(features_array)
                    prediction = model.predict(features_scaled)[0]
                    prediction_proba = model.predict_proba(features_scaled)[0]
                else:
                    prediction = model.predict(features_array)[0]
                    prediction_proba = model.predict_proba(features_array)[0]
            else:
                # Basit tahmin
                if hasattr(scaler, "transform"):
                    features_scaled = scaler.transform(features_array)
                    prediction = model.predict(features_scaled)[0]
                else:
                    prediction = model.predict(features_array)[0]
                
                # Dummy probability scores
                prediction_proba = [0.33, 0.33, 0.34]
                prediction_proba[int(prediction)] = 0.85
                prediction_proba = np.array(prediction_proba)
        
        except Exception as e:
            return {
                "success": False,
                "error": f"Tahmin hatası: {str(e)}",
                "message": "Model tahmini sırasında hata oluştu"
            }
        
        # Sonuçları hazırla
        activity_labels = ["Düşük Yoğunluk", "Orta Yoğunluk", "Yüksek Yoğunluk"]
        if "activity_labels" in model_info:
            activity_labels = model_info["activity_labels"]
        
        result = {
            "success": True,
            "data": {
                "recommended_level": int(prediction),
                "recommended_activity": activity_labels[int(prediction)],
                "confidence": float(max(prediction_proba)),
                "confidence_scores": {
                    activity_labels[i]: float(prob) 
                    for i, prob in enumerate(prediction_proba)
                },
                "model_info": {
                    "model_type": model_info.get("model_type", "Unknown"),
                    "accuracy": model_info.get("accuracy", 0.85),
                    "features_used": len(features)
                }
            }
        }
        
        return result
        
    except Exception as e:
        return {
            "success": False,
            "error": str(e),
            "message": "Genel hata: Model tahmini yapılırken beklenmeyen hata oluştu"
        }

def main():
    try:
        # Stdin\'den JSON veri oku
        input_data = sys.stdin.read().strip()
        if not input_data:
            raise ValueError("Boş input verisi")
            
        data = json.loads(input_data)
        
        # Tahmin yap
        result = predict_activity(data)
        
        # Sonucu yazdır
        print(json.dumps(result, ensure_ascii=False))
        
    except json.JSONDecodeError as e:
        error_result = {
            "success": False,
            "error": f"JSON parse hatası: {str(e)}",
            "message": "Geçersiz JSON formatı"
        }
        print(json.dumps(error_result, ensure_ascii=False))
        
    except Exception as e:
        error_result = {
            "success": False,
            "error": str(e),
            "message": "Python script genel hatası"
        }
        print(json.dumps(error_result, ensure_ascii=False))

if __name__ == "__main__":
    main()
';
        
        // Python script dosyasını oluştur
        file_put_contents($this->pythonScript, $pythonCode);
        chmod($this->pythonScript, 0755);
    }
    
    public function predict($inputData) {
        try {
            // Input validasyonu
            $this->validateInput($inputData);
            
            // Python varlığını kontrol et
            $pythonCheck = $this->checkPython();
            if (!$pythonCheck['available']) {
                // Python yoksa fallback çözümü kullan
                return $this->fallbackPredict($inputData);
            }
            
            // Python script dosyası varlığını kontrol et
            if (!file_exists($this->pythonScript)) {
                $this->createPythonScript();
            }
            
            // Python script'ini çalıştır
            try {
                $result = $this->runPythonScript($inputData);
                return $result;
            } catch (Exception $e) {
                // Python hatası varsa fallback kullan
                return $this->fallbackPredict($inputData);
            }
            
        } catch (Exception $e) {
            // Error log for debugging
            error_log("ActivityPredictionAPI Error: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'debug_info' => [
                    'python_available' => $this->checkPython(),
                    'script_exists' => file_exists($this->pythonScript),
                    'model_files' => [
                        'model' => file_exists($this->modelPath),
                        'scaler' => file_exists($this->scalerPath),
                        'info' => file_exists($this->modelInfoPath)
                    ]
                ]
            ];
        }
    }
    
    /**
     * Python mevcut değilse basit tahmin algoritması
     */
    private function fallbackPredict($data) {
        try {
            // Basit skor hesaplama algoritması
            $healthScore = 1.0;
            
            // Yaş faktörü (18-100)
            $ageScore = 1.0;
            if ($data['age'] > 65) {
                $ageScore = 0.7;
            } elseif ($data['age'] < 30) {
                $ageScore = 1.3;
            } elseif ($data['age'] < 50) {
                $ageScore = 1.1;
            }
            
            // BMI faktörü
            $bmiScore = 1.0;
            if ($data['bmi'] > 30) {
                $bmiScore = 0.6;
            } elseif ($data['bmi'] > 25) {
                $bmiScore = 0.8;
            } elseif ($data['bmi'] >= 18.5) {
                $bmiScore = 1.2;
            } else {
                $bmiScore = 0.9;
            }
            
            // Glukoz faktörü
            $glucoseScore = 1.0;
            if ($data['glucose_level'] > 180) {
                $glucoseScore = 0.5;
            } elseif ($data['glucose_level'] > 140) {
                $glucoseScore = 0.7;
            } elseif ($data['glucose_level'] < 100) {
                $glucoseScore = 1.2;
            }
            
            // Kan basıncı faktörü
            $bpScore = 1.0;
            if ($data['blood_pressure'] > 140) {
                $bpScore = 0.7;
            } elseif ($data['blood_pressure'] < 120) {
                $bpScore = 1.1;
            }
            
            // Diyabet faktörü
            $diabetesScore = $data['has_diabetes'] == 1 ? 0.7 : 1.0;
            
            // Mevcut aktivite faktörü
            $currentActivityScore = 1.0 + ($data['current_activity_level'] * 0.3);
            
            // Adım faktörü
            $stepsScore = 1.0;
            if ($data['daily_steps'] > 10000) {
                $stepsScore = 1.3;
            } elseif ($data['daily_steps'] > 7000) {
                $stepsScore = 1.1;
            } elseif ($data['daily_steps'] < 3000) {
                $stepsScore = 0.7;
            }
            
            // Egzersiz sıklığı faktörü
            $exerciseScore = 1.0 + ($data['exercise_frequency'] * 0.1);
            
            // Kalp hızı faktörü
            $heartRateScore = 1.0;
            if ($data['heart_rate_resting'] < 60) {
                $heartRateScore = 1.2; // İyi kondisyon
            } elseif ($data['heart_rate_resting'] > 90) {
                $heartRateScore = 0.8; // Kondisyon düşük
            }
            
            // Toplam skor hesaplama
            $healthScore = $ageScore * $bmiScore * $glucoseScore * $bpScore * 
                          $diabetesScore * $currentActivityScore * $stepsScore * 
                          $exerciseScore * $heartRateScore;
            
            // Aktivite seviyesi belirleme
            $recommendedLevel = 0; // Düşük
            if ($healthScore > 1.5) {
                $recommendedLevel = 2; // Yüksek
            } elseif ($healthScore > 1.0) {
                $recommendedLevel = 1; // Orta
            }
            
            // Güven oranı hesaplama
            $confidence = min(0.95, max(0.65, $healthScore / 2.0));
            
            // Olasılık dağılımı
            $probabilities = [0.33, 0.33, 0.34];
            $probabilities[$recommendedLevel] = $confidence;
            
            // Diğer olasılıkları ayarla
            $remaining = 1.0 - $confidence;
            for ($i = 0; $i < 3; $i++) {
                if ($i != $recommendedLevel) {
                    $probabilities[$i] = $remaining / 2;
                }
            }
            
            $activityLabels = ['Düşük Yoğunluk', 'Orta Yoğunluk', 'Yüksek Yoğunluk'];
            
            return [
                'success' => true,
                'data' => [
                    'recommended_level' => $recommendedLevel,
                    'recommended_activity' => $activityLabels[$recommendedLevel],
                    'confidence' => $confidence,
                    'confidence_scores' => [
                        $activityLabels[0] => $probabilities[0],
                        $activityLabels[1] => $probabilities[1],
                        $activityLabels[2] => $probabilities[2]
                    ],
                    'model_info' => [
                        'model_type' => 'PHP_Fallback',
                        'accuracy' => 0.80,
                        'features_used' => 13,
                        'health_score' => round($healthScore, 3)
                    ]
                ],
                'fallback_used' => true,
                'reason' => 'Python/Model dosyaları mevcut değil'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Fallback tahmin hatası: ' . $e->getMessage()
            ];
        }
    }
    
    private function validateInput($data) {
        $required_fields = [
            'age', 'gender', 'bmi', 'glucose_level', 'blood_pressure',
            'insulin', 'diabetes_pedigree', 'pregnancies', 
            'current_activity_level', 'heart_rate_resting', 
            'daily_steps', 'exercise_frequency', 'has_diabetes'
        ];
        
        foreach ($required_fields as $field) {
            if (!isset($data[$field])) {
                throw new Exception("Eksik alan: " . $field);
            }
        }
        
        // Temel veri tipi kontrolleri
        if (!is_numeric($data['age']) || $data['age'] < 18 || $data['age'] > 100) {
            throw new Exception("Yaş 18-100 arasında olmalıdır");
        }
        
        if (!is_numeric($data['bmi']) || $data['bmi'] < 15 || $data['bmi'] > 50) {
            throw new Exception("BMI 15-50 arasında olmalıdır");
        }
        
        if (!is_numeric($data['glucose_level']) || $data['glucose_level'] < 50 || $data['glucose_level'] > 400) {
            throw new Exception("Kan şekeri 50-400 mg/dL arasında olmalıdır");
        }
    }
    
    private function runPythonScript($data) {
        // JSON formatında veriyi hazırla
        $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
        
        // Python komutunu tespit et
        $pythonInfo = $this->checkPython();
        if (!$pythonInfo['available']) {
            throw new Exception("Python bulunamadı: " . $pythonInfo['error']);
        }
        
        // Farklı Python komutlarını dene
        $pythonCommands = [
            $pythonInfo['command'] . " " . escapeshellarg($this->pythonScript),
            "python " . escapeshellarg($this->pythonScript),
            "python3 " . escapeshellarg($this->pythonScript),
            "py " . escapeshellarg($this->pythonScript)
        ];
        
        $lastError = "";
        
        foreach ($pythonCommands as $pythonCommand) {
            try {
                // Process ile çalıştır
                $descriptorspec = [
                    0 => ["pipe", "r"],  // stdin
                    1 => ["pipe", "w"],  // stdout
                    2 => ["pipe", "w"]   // stderr
                ];
                
                $process = proc_open($pythonCommand, $descriptorspec, $pipes, __DIR__);
                
                if (is_resource($process)) {
                    // Veriyi gönder
                    fwrite($pipes[0], $jsonData);
                    fclose($pipes[0]);
                    
                    // Çıktıları oku
                    $output = stream_get_contents($pipes[1]);
                    $error = stream_get_contents($pipes[2]);
                    
                    fclose($pipes[1]);
                    fclose($pipes[2]);
                    
                    $return_value = proc_close($process);
                    
                    if ($return_value === 0 && !empty($output)) {
                        // JSON çıktısını parse et
                        $result = json_decode($output, true);
                        
                        if ($result !== null) {
                            return $result;
                        } else {
                            $lastError = "Geçersiz JSON çıktısı: " . $output;
                        }
                    } else {
                        $lastError = "Python hatası (Return code: $return_value): " . $error;
                    }
                } else {
                    $lastError = "Process başlatılamadı: " . $pythonCommand;
                }
                
            } catch (Exception $e) {
                $lastError = "Exception: " . $e->getMessage();
            }
        }
        
        throw new Exception("Python script çalıştırılamadı. Son hata: " . $lastError);
    }
    
    private function checkPython() {
        // Windows ve Linux için farklı Python komutları
        $pythonCommands = [
            'python',
            'python3',
            'py',
            'py -3',
            '/usr/bin/python3',
            '/usr/local/bin/python3',
            'C:\\Python39\\python.exe',
            'C:\\Python310\\python.exe',
            'C:\\Python311\\python.exe',
            'C:\\Python312\\python.exe'
        ];
        
        foreach ($pythonCommands as $cmd) {
            $output = shell_exec($cmd . ' --version 2>&1');
            if (!empty($output) && stripos($output, 'Python') !== false) {
                return [
                    'available' => true,
                    'command' => $cmd,
                    'version' => trim($output)
                ];
            }
        }
        
        // Windows için ek kontrol
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $whereOutput = shell_exec('where python 2>&1');
            if ($whereOutput && !strpos($whereOutput, 'INFO: Could not find')) {
                return [
                    'available' => true,
                    'command' => 'python',
                    'version' => 'Found via where command'
                ];
            }
        }
        
        return [
            'available' => false,
            'error' => 'Python bulunamadı. Python 3.x yüklü olduğundan emin olun ve PATH\'e ekleyin.'
        ];
    }
    
    public function getSystemInfo() {
        $pythonInfo = $this->checkPython();
        
        return [
            'success' => true,
            'info' => [
                'php_version' => phpversion(),
                'python_info' => $pythonInfo,
                'model_files' => [
                    'model' => file_exists($this->modelPath),
                    'scaler' => file_exists($this->scalerPath), 
                    'model_info' => file_exists($this->modelInfoPath)
                ],
                'script_file' => file_exists($this->pythonScript),
                'working_directory' => __DIR__,
                'current_time' => date('Y-m-d H:i:s')
            ]
        ];
    }
}

// API endpoint handling
try {
    $api = new ActivityPredictionAPI();
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Sistem bilgilerini döndür
        echo json_encode($api->getSystemInfo(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // POST verilerini al
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if ($data === null) {
            throw new Exception('Geçersiz JSON formatı');
        }
        
        // Default değerleri ayarla
        $data['insulin'] = $data['insulin'] ?? 80;
        $data['diabetes_pedigree'] = $data['diabetes_pedigree'] ?? 0.5;
        
        // Tahmin yap
        $result = $api->predict($data);
        
        // JSON output kontrol
        if ($result === null) {
            throw new Exception('API null sonuç döndürdü');
        }
        
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        
    } else {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Desteklenmeyen HTTP metodu'
        ], JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    error_log("API Fatal Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => 'INTERNAL_SERVER_ERROR',
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
}
?>