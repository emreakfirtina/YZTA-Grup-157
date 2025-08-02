from fastapi import FastAPI, Request
from fastapi.responses import JSONResponse
from fastapi.middleware.cors import CORSMiddleware
import numpy as np
import pickle
import os

app = FastAPI(title="DiabetCare AI API", version="1.0.0")

# CORS ayarları - XAMPP localhost için güncellenmiş
app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "http://localhost",
        "http://127.0.0.1",
        "http://localhost:80",
        "http://127.0.0.1:80",
        "*"  # Geliştirme için, production'da kaldırın
    ],
    allow_credentials=True,
    allow_methods=["GET", "POST", "OPTIONS"],
    allow_headers=["*"],
)

# Model ve scaler yükleme
try:
    # Dosya yollarını kontrol et
    model_path = "optimized_random_forest.pkl"
    scaler_path = "scaler.pkl"
    
    if not os.path.exists(model_path):
        raise FileNotFoundError(f"❌ {model_path} dosyası bulunamadı!")
    
    if not os.path.exists(scaler_path):
        raise FileNotFoundError(f"❌ {scaler_path} dosyası bulunamadı!")
    
    with open(model_path, "rb") as f:
        model = pickle.load(f)
        print("✅ Random Forest modeli yüklendi")
    
    with open(scaler_path, "rb") as f:
        scaler = pickle.load(f)
        print("✅ Scaler yüklendi")
    
    print("🚀 Sistem hazır!")
    
except Exception as e:
    print(f"❌ Model yükleme hatası: {e}")
    model = None
    scaler = None

@app.get("/")
async def root():
    """Ana endpoint - API durumu"""
    return {
        "message": "DiabetCare AI API",
        "status": "active",
        "model_loaded": model is not None,
        "scaler_loaded": scaler is not None
    }

@app.post("/predict")
async def predict_diabetes(request: Request):
    """Diyabet tahmini endpoint"""
    try:
        # Model kontrol
        if model is None or scaler is None:
            return JSONResponse({
                "success": False, 
                "error": "Model dosyaları yüklenemedi. Lütfen sistem yöneticisine başvurun."
            }, status_code=500)
        
        # JSON verisini al
        data = await request.json()
        print(f"📥 Gelen veri: {data}")
        
        # Gerekli alanları kontrol et
        required_fields = [
            "Pregnancies", "Glucose", "BloodPressure", "SkinThickness", 
            "Insulin", "BMI", "DiabetesPedigreeFunction", "Age"
        ]
        
        missing_fields = [field for field in required_fields if field not in data]
        if missing_fields:
            return JSONResponse({
                "success": False, 
                "error": f"Eksik alanlar: {', '.join(missing_fields)}"
            }, status_code=400)
        
        # Özellikleri hazırla
        try:
            features = [
                float(data["Pregnancies"]),
                float(data["Glucose"]),
                float(data["BloodPressure"]),
                float(data["SkinThickness"]),
                float(data["Insulin"]),
                float(data["BMI"]),
                float(data["DiabetesPedigreeFunction"]),
                float(data["Age"]),
            ]
        except (ValueError, TypeError) as e:
            return JSONResponse({
                "success": False,
                "error": "Lütfen tüm alanlara geçerli sayısal değerler girin."
            }, status_code=400)

        # Veri doğrulama (temel kontroller)
        if features[1] <= 0:  # Glukoz
            return JSONResponse({
                "success": False,
                "error": "Glukoz değeri 0'dan büyük olmalıdır."
            }, status_code=400)
        
        if features[5] <= 0:  # BMI
            return JSONResponse({
                "success": False,
                "error": "BMI değeri 0'dan büyük olmalıdır."
            }, status_code=400)

        # Özellikleri ölçeklendir
        X_scaled = scaler.transform([features])
        print(f"📊 Ölçeklendirilmiş özellikler şekli: {X_scaled.shape}")

        # Tahmin yap
        probability = model.predict_proba(X_scaled)[0][1]  # Pozitif sınıf olasılığı
        prediction = model.predict(X_scaled)[0]  # 0 veya 1
        
        # Olasılığı 0-1 aralığında tut
        probability = max(0.0, min(probability, 1.0))
        percentage = probability * 100
        
        print(f"📈 Tahmin: {prediction}, Olasılık: {probability:.4f}")

        return JSONResponse({
            "success": True,
            "probability": probability,
            "percentage": round(percentage, 2),
            "prediction": int(prediction),
            "risk_level": get_risk_level(probability),
            "message": "Tahmin başarıyla tamamlandı"
        })

    except Exception as e:
        print(f"❌ Tahmin hatası: {str(e)}")
        return JSONResponse({
            "success": False,
            "error": f"Beklenmeyen hata: {str(e)}"
        }, status_code=500)

def get_risk_level(probability):
    """Risk seviyesini belirle"""
    if probability < 0.3:
        return "low"
    elif probability < 0.7:
        return "medium"
    else:
        return "high"

@app.get("/health")
async def health_check():
    """Sistem sağlığı kontrolü"""
    return JSONResponse({
        "status": "healthy",
        "model_loaded": model is not None,
        "scaler_loaded": scaler is not None,
        "api_version": "1.0.0"
    })

# OPTIONS endpoint'i CORS için
@app.options("/predict")
async def options_predict():
    return JSONResponse({"message": "OK"})

if __name__ == "__main__":
    import uvicorn
    print("=" * 50)
    print("🏥 DiabetCare AI Backend Başlatılıyor...")
    print("=" * 50)
    
    if model is None or scaler is None:
        print("⚠️  UYARI: Model dosyaları eksik!")
        print("   Lütfen şu dosyaların mevcut olduğundan emin olun:")
        print("   - optimized_random_forest.pkl")
        print("   - scaler.pkl")
        print("   🔧 model.py dosyasını çalıştırarak scaler.pkl oluşturabilirsiniz")
    else:
        print("✅ Tüm modeller hazır!")
    
    print("\n🌐 Sunucu bilgileri:")
    print("   URL: http://127.0.0.1:8000")
    print("   API Docs: http://127.0.0.1:8000/docs")
    print("   Health Check: http://127.0.0.1:8000/health")
    print("   XAMPP ile uyumlu: http://localhost/diabetcareai2/")
    print("=" * 50)
    
    uvicorn.run(
        app, 
        host="127.0.0.1", 
        port=8000,
        log_level="info"
    )