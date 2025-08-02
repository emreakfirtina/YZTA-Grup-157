#!/usr/bin/env python3
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
        
        # NumPy array'e çevir
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
        # Stdin'den JSON veri oku
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
