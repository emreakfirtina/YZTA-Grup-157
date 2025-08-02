#!/usr/bin/env python3
"""
DiabetCare AI - Semptom Analiz Scripti
PHP'den çağrılmak üzere tasarlanmış Python scripti
"""

import sys
import json
import pickle
import numpy as np
import warnings
warnings.filterwarnings('ignore')

def load_model():
    """Eğitilmiş modeli yükle"""
    try:
        with open('diabetcare_symptom_model.pkl', 'rb') as f:
            model_package = pickle.load(f)
        return model_package
    except FileNotFoundError:
        raise Exception("Model dosyası bulunamadı: diabetcare_symptom_model.pkl")
    except Exception as e:
        raise Exception(f"Model yükleme hatası: {str(e)}")

def predict_diabetes_symptoms(model_package, patient_data):
    """
    Hasta verilerine göre diyabet riski tahmini
    """
    try:
        # Model ve gerekli bileşenleri al
        model = model_package['model']
        scaler = model_package.get('scaler')
        model_name = model_package['model_name']
        
        # Hasta verilerini çıkar
        age = patient_data['age']
        bmi = patient_data['bmi']
        glucose = patient_data['glucose']
        blood_pressure = patient_data['blood_pressure']
        symptoms = patient_data['symptoms']
        severity = patient_data['severity']
        
        # Semptom skorunu hesapla
        symptom_score = len(symptoms) * severity
        
        # Varsayılan değerler
        pregnancies = 0  # Varsayılan
        skin_thickness = 20  # Varsayılan
        insulin = 100  # Varsayılan
        diabetes_pedigree = 0.5  # Varsayılan
        
        # Risk skorlarını hesapla
        glucose_risk = 2 if glucose > 140 else 1 if glucose > 100 else 0
        bmi_risk = 2 if bmi > 30 else 1 if bmi > 25 else 0
        age_risk = 2 if age > 50 else 1 if age > 35 else 0
        bp_risk = 2 if blood_pressure > 90 else 1 if blood_pressure > 80 else 0
        
        # Semptom etkisini dahil et
        symptom_risk = min(3, int(symptom_score / 3))  # 0-3 arası
        
        total_risk_score = glucose_risk + bmi_risk + age_risk + bp_risk + symptom_risk
        
        # Tahmin için özellik vektörü oluştur
        features = np.array([[
            pregnancies, glucose, blood_pressure, skin_thickness,
            insulin, bmi, diabetes_pedigree, age,
            glucose_risk, bmi_risk, age_risk, bp_risk, total_risk_score
        ]])
        
        # Model tipine göre tahmin yap
        if model_name in ['Logistic Regression', 'SVM'] and scaler is not None:
            features_scaled = scaler.transform(features)
            probability = model.predict_proba(features_scaled)[0][1]
            prediction = model.predict(features_scaled)[0]
        else:
            probability = model.predict_proba(features)[0][1]
            prediction = model.predict(features)[0]
        
        # Semptom etkisini olasılığa yansıt
        adjusted_probability = min(0.95, probability + (symptom_score * 0.02))
        
        # Risk kategorisi ve önerileri belirle
        if total_risk_score >= 8 or adjusted_probability > 0.7:
            risk_category = "Yüksek Risk"
            recommendation = "🚨 Acil doktor konsültasyonu önerilir! Semptomlarınız ve değerleriniz ciddi risk gösteriyor."
        elif total_risk_score >= 5 or adjusted_probability > 0.4:
            risk_category = "Orta Risk"
            recommendation = "⚠️ Doktor kontrolü ve yaşam tarzı değişiklikleri önerilir. Düzenli takip yapın."
        else:
            risk_category = "Düşük Risk"
            recommendation = "✅ Düzenli takip ve sağlıklı yaşam önerilir. Preventif önlemler alın."
        
        # Risk faktörlerini belirle
        risk_factors = []
        if glucose_risk > 0:
            risk_factors.append("Kan şekeri seviyesi")
        if bmi_risk > 0:
            risk_factors.append("Vücut kitle indeksi")
        if age_risk > 0:
            risk_factors.append("Yaş faktörü")
        if bp_risk > 0:
            risk_factors.append("Kan basıncı")
        if symptom_score > 3:
            risk_factors.append("Semptom profili")
        
        # Spesifik semptom uyarıları
        high_risk_symptoms = ['thirst', 'urination', 'weight-loss', 'blurred-vision']
        critical_symptoms = [s for s in symptoms if s in high_risk_symptoms]
        
        if critical_symptoms:
            risk_factors.append("Kritik semptomlar mevcut")
            if len(critical_symptoms) >= 2:
                adjusted_probability = min(0.9, adjusted_probability + 0.1)
        
        return {
            'tahmin': 'Diyabet Riski Var' if prediction == 1 else 'Diyabet Riski Düşük',
            'olasillik': f"{adjusted_probability*100:.1f}%",
            'risk_kategorisi': risk_category,
            'risk_skoru': total_risk_score,
            'oneri': recommendation,
            'risk_factors': risk_factors,
            'symptom_count': len(symptoms),
            'symptom_severity': severity,
            'critical_symptoms': critical_symptoms,
            'model_used': model_name,
            'detaylar': {
                'glucose_risk': glucose_risk,
                'bmi_risk': bmi_risk,
                'age_risk': age_risk,
                'bp_risk': bp_risk,
                'symptom_risk': symptom_risk
            }
        }
        
    except Exception as e:
        raise Exception(f"Tahmin hatası: {str(e)}")

def main():
    """Ana fonksiyon - PHP'den gelen veriyi işle"""
    try:
        # Komut satırı argümanını al
        if len(sys.argv) != 2:
            raise Exception("Eksik parametre")
        
        # JSON verisini parse et
        json_data = sys.argv[1]
        patient_data = json.loads(json_data)
        
        # Modeli yükle
        model_package = load_model()
        
        # Tahmin yap
        result = predict_diabetes_symptoms(model_package, patient_data)
        
        # Sonucu JSON olarak çıktıla
        print(json.dumps(result, ensure_ascii=False))
        
    except Exception as e:
        # Hata durumunda JSON formatında hata mesajı
        error_result = {
            'error': True,
            'message': str(e),
            'tahmin': 'Analiz Hatası',
            'olasillik': '0%',
            'risk_kategorisi': 'Belirsiz',
            'risk_skoru': 0,
            'oneri': 'Sistem hatası nedeniyle analiz tamamlanamadı. Lütfen doktorunuza başvurun.'
        }
        print(json.dumps(error_result, ensure_ascii=False))

if __name__ == "__main__":
    main()