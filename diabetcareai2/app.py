from fastapi import FastAPI, Request
from fastapi.responses import JSONResponse
from fastapi.middleware.cors import CORSMiddleware
import numpy as np
import pickle
import os
import logging

# Loglama ayarları
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = FastAPI(title="DiabetCare AI API", version="1.0.0")

# CORS ayarları - XAMPP localhost için
app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "http://localhost",
        "http://127.0.0.1",
        "http://localhost:80",
        "http://localhost:8080"
    ],
    allow_credentials=True,
    allow_methods=["GET", "POST"],
    allow_headers=["*"],
)

# Global değişkenler
model = None
scaler = None

# Model ve scaler yükleme
def load_models():
    global model, scaler
    try:
        # Model dosyası kontrolü
        model_path = "optimized_random_forest.pkl"
        if not os.path.exists(model_path):
            logger.error(f"❌ {model_path} dosyası bulunamadı!")
            return False
        
        # Model yükleme
        with open(model_path, "rb") as f:
            model = pickle.load(f)
            logger.info("✅ Random Forest modeli yüklendi")
        
        # Scaler yükleme (isteğe bağlı)
        scaler_path = "scaler.pkl"
        if os.path.exists(scaler_path):
            with open(scaler_path, "rb") as f:
                scaler = pickle.load(f)
                logger.info("✅ Scaler yüklendi")
        else:
            logger.warning("⚠️ scaler.pkl bulunamadı, normalizasyon yapılmayacak")
            scaler = None
        
        logger.info("🚀 Sistem hazır!")
        return True
        
    except Exception as e:
        logger.error(f"❌ Model yükleme hatası: {e}")
        return False

# Uygulama başlangıcında modelleri yükle
@app.on_event("startup")
async def startup_event():
    success = load_models()
    if not success:
        logger.error("Sistem başlatılamadı!")

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
        if model is None:
            return JSONResponse({
                "success": False, 
                "error": "Model dosyası yüklenemedi. Lütfen sistem yöneticisine başvurun."
            }, status_code=500)
        
        # JSON verisini al
        data = await request.json()
        logger.info(f"📥 Gelen veri: {data}")
        
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
        
        # Özellikleri hazırla ve doğrula
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
        except (ValueError, TypeError):
            return JSONResponse({
                "success": False,
                "error": "Lütfen tüm alanlara geçerli sayısal değerler girin."
            }, status_code=400)

        # Temel veri doğrulama
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

        # Özellikleri numpy array'e çevir
        X = np.array([features])
        
        # Scaler varsa uygula
        if scaler is not None:
            X_scaled = scaler.transform(X)
            logger.info(f"📊 Veri ölçeklendirildi: {X_scaled.shape}")
        else:
            X_scaled = X
            logger.info(f"📊 Ham veri kullanılıyor: {X.shape}")

        # Tahmin yap
        try:
            probability = model.predict_proba(X_scaled)[0][1]  # Pozitif sınıf olasılığı
            prediction = model.predict(X_scaled)[0]  # 0 veya 1
        except Exception as e:
            logger.error(f"Tahmin hatası: {e}")
            return JSONResponse({
                "success": False,
                "error": "Model tahmin hatası oluştu."
            }, status_code=500)
        
        # Olasılığı 0-1 aralığında tut
        probability = max(0.0, min(float(probability), 1.0))
        
        logger.info(f"📈 Tahmin: {prediction}, Olasılık: {probability:.4f}")

        return JSONResponse({
            "success": True,
            "probability": round(probability, 4),
            "prediction": int(prediction),
            "risk_level": get_risk_level(probability),
            "percentage": round(probability * 100, 2),
            "message": "Tahmin başarıyla tamamlandı"
        })

    except Exception as e:
        logger.error(f"❌ Genel hata: {str(e)}")
        return JSONResponse({
            "success": False,
            "error": "Beklenmeyen bir hata oluştu. Lütfen tekrar deneyin."
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
        "status": "healthy" if model is not None else "unhealthy",
        "model_loaded": model is not None,
        "scaler_loaded": scaler is not None,
        "api_version": "1.0.0"
    })

if __name__ == "__main__":
    import uvicorn
    print("=" * 50)
    print("🏥 DiabetCare AI Backend Başlatılıyor...")
    print("=" * 50)
    
    # Modelleri yükle
    if load_models():
        print("✅ Tüm modeller hazır!")
    else:
        print("⚠️  UYARI: Model dosyaları eksik!")
        print("   Lütfen şu dosyanın mevcut olduğundan emin olun:")
        print("   - optimized_random_forest.pkl")
    
    print("\n🌐 Sunucu bilgileri:")
    print("   URL: http://127.0.0.1:8000")
    print("   API Docs: http://127.0.0.1:8000/docs")
    print("   Health Check: http://127.0.0.1:8000/health")
    print("=" * 50)
    
    uvicorn.run(
        app, 
        host="127.0.0.1", 
        port=8000,
        log_level="info",
        reload=True  # Geliştirme için otomatik yeniden yükleme
    )
