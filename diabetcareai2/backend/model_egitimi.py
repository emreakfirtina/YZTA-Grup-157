# DiabetCare AI - Beslenme Önerisi ve Kan Şekeri Etkisi Tahmin Modeli
import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestRegressor, RandomForestClassifier
from sklearn.preprocessing import LabelEncoder, StandardScaler
from sklearn.metrics import mean_squared_error, classification_report, accuracy_score
import pickle
import warnings
warnings.filterwarnings('ignore')

# Örnek veri seti oluşturma (gerçek projede Kaggle veya UCI'dan veri çekeceksiniz)
def create_nutrition_dataset():
    """
    Diyabet hastaları için beslenme veri seti oluşturur
    Gerçek projede Food Dataset veya USDA Food Database kullanılabilir
    """
    np.random.seed(42)
    n_samples = 2000
    
    # Gıda kategorileri
    food_categories = ['Tahıllar', 'Sebzeler', 'Meyveler', 'Et/Balık', 'Süt Ürünleri', 
                      'Baklagiller', 'Yağlı Tohumlar', 'Şekerli Gıdalar', 'Fast Food']
    
    # Öğün türleri
    meal_types = ['Kahvaltı', 'Ara Öğün', 'Öğle Yemeği', 'İkindi', 'Akşam Yemeği']
    
    data = []
    for i in range(n_samples):
        # Temel besin değerleri
        calories = np.random.randint(50, 800)
        carbs = np.random.randint(5, 120)  # gram
        protein = np.random.randint(2, 50)  # gram
        fat = np.random.randint(1, 40)  # gram
        fiber = np.random.randint(0, 15)  # gram
        sugar = np.random.randint(0, 50)  # gram
        
        # Glisemik indeks (0-100)
        gi = np.random.randint(20, 95)
        
        # Porsiyon büyüklüğü
        portion_size = np.random.randint(50, 300)  # gram
        
        # Yemek kategorisi
        food_category = np.random.choice(food_categories)
        meal_type = np.random.choice(meal_types)
        
        # Hasta bilgileri
        age = np.random.randint(25, 80)
        bmi = np.random.uniform(18.5, 35.0)
        diabetes_type = np.random.choice(['Tip 1', 'Tip 2'])
        hba1c = np.random.uniform(5.5, 12.0)
        
        # Kan şekeri artışı tahmini (hedef değişken)
        # Basit bir formül ile simüle ediyoruz
        bg_increase = (carbs * 2.5 + sugar * 3.0 + gi * 0.5) / (fiber * 2 + protein * 0.5) 
        bg_increase += np.random.normal(0, 10)  # Gürültü ekle
        bg_increase = max(0, bg_increase)  # Negatif değerleri sıfırla
        
        # Risk kategorisi
        if bg_increase < 30:
            risk_category = 'Düşük Risk'
        elif bg_increase < 60:
            risk_category = 'Orta Risk'
        else:
            risk_category = 'Yüksek Risk'
        
        data.append({
            'kalori': calories,
            'karbohidrat': carbs,
            'protein': protein,
            'yag': fat,
            'lif': fiber,
            'seker': sugar,
            'glisemik_indeks': gi,
            'porsiyon': portion_size,
            'yemek_kategorisi': food_category,
            'ogun_turu': meal_type,
            'yas': age,
            'bmi': bmi,
            'diyabet_tipi': diabetes_type,
            'hba1c': hba1c,
            'kan_sekeri_artisi': bg_increase,
            'risk_kategorisi': risk_category
        })
    
    return pd.DataFrame(data)

# Veri setini oluştur
print("Veri seti oluşturuluyor...")
df = create_nutrition_dataset()
print(f"Veri seti boyutu: {df.shape}")
print("\nVeri seti önizlemesi:")
print(df.head())

# Model eğitimi için veri hazırlığı
def prepare_data(df):
    """Veriyi model eğitimi için hazırlar"""
    
    # Kategorik değişkenleri encode et
    le_food = LabelEncoder()
    le_meal = LabelEncoder()
    le_diabetes = LabelEncoder()
    le_risk = LabelEncoder()
    
    df_encoded = df.copy()
    df_encoded['yemek_kategorisi_encoded'] = le_food.fit_transform(df['yemek_kategorisi'])
    df_encoded['ogun_turu_encoded'] = le_meal.fit_transform(df['ogun_turu'])
    df_encoded['diyabet_tipi_encoded'] = le_diabetes.fit_transform(df['diyabet_tipi'])
    df_encoded['risk_kategorisi_encoded'] = le_risk.fit_transform(df['risk_kategorisi'])
    
    # Özellik seçimi
    features = ['kalori', 'karbohidrat', 'protein', 'yag', 'lif', 'seker', 
               'glisemik_indeks', 'porsiyon', 'yemek_kategorisi_encoded', 
               'ogun_turu_encoded', 'yas', 'bmi', 'diyabet_tipi_encoded', 'hba1c']
    
    X = df_encoded[features]
    y_regression = df_encoded['kan_sekeri_artisi']
    y_classification = df_encoded['risk_kategorisi_encoded']
    
    return X, y_regression, y_classification, le_food, le_meal, le_diabetes, le_risk

# Veri hazırlığı
X, y_reg, y_clf, le_food, le_meal, le_diabetes, le_risk = prepare_data(df)

# Veriyi eğitim ve test setlerine ayır
X_train, X_test, y_reg_train, y_reg_test, y_clf_train, y_clf_test = train_test_split(
    X, y_reg, y_clf, test_size=0.2, random_state=42)

# Veri standardizasyonu
scaler = StandardScaler()
X_train_scaled = scaler.fit_transform(X_train)
X_test_scaled = scaler.transform(X_test)

print("\nModel eğitimi başlıyor...")

# 1. Kan şekeri artışı tahmini modeli (Regresyon)
print("1. Kan şekeri artışı tahmin modeli eğitiliyor...")
bg_model = RandomForestRegressor(n_estimators=100, random_state=42)
bg_model.fit(X_train_scaled, y_reg_train)

# Regresyon modeli değerlendirmesi
y_reg_pred = bg_model.predict(X_test_scaled)
rmse = np.sqrt(mean_squared_error(y_reg_test, y_reg_pred))
print(f"Kan şekeri tahmin modeli RMSE: {rmse:.2f}")

# 2. Risk kategorisi sınıflandırma modeli
print("2. Risk kategorisi sınıflandırma modeli eğitiliyor...")
risk_model = RandomForestClassifier(n_estimators=100, random_state=42)
risk_model.fit(X_train_scaled, y_clf_train)

# Sınıflandırma modeli değerlendirmesi
y_clf_pred = risk_model.predict(X_test_scaled)
accuracy = accuracy_score(y_clf_test, y_clf_pred)
print(f"Risk kategorisi modeli doğruluğu: {accuracy:.3f}")

# Özellik önem derecelerini göster
feature_names = ['kalori', 'karbohidrat', 'protein', 'yag', 'lif', 'seker', 
                'glisemik_indeks', 'porsiyon', 'yemek_kategorisi', 
                'ogun_turu', 'yas', 'bmi', 'diyabet_tipi', 'hba1c']

print("\nKan şekeri tahmininde en önemli özellikler:")
feature_importance = pd.DataFrame({
    'feature': feature_names,
    'importance': bg_model.feature_importances_
}).sort_values('importance', ascending=False)
print(feature_importance.head(8))

# Beslenme önerisi fonksiyonu
def get_nutrition_recommendation(carbs, gi, sugar, fiber, meal_type, diabetes_type):
    """
    Girilen besin değerlerine göre diyabet hastası için öneri verir
    """
    recommendations = []
    
    # Karbohidrat kontrolü
    if carbs > 60:
        recommendations.append("⚠️ Yüksek karbohidrat içeriği! Porsiyonu azaltmayı düşünün.")
    elif carbs < 15:
        recommendations.append("✅ Düşük karbohidrat içeriği, kan şekeri için uygun.")
    
    # Glisemik indeks kontrolü
    if gi > 70:
        recommendations.append("⚠️ Yüksek glisemik indeksli gıda. Lif açısından zengin gıdalarla kombine edin.")
    elif gi < 55:
        recommendations.append("✅ Düşük glisemik indeksli gıda, kan şekeri kontrolü için ideal.")
    
    # Şeker kontrolü
    if sugar > 25:
        recommendations.append("❌ Çok yüksek şeker içeriği! Bu gıdayı sınırlayın.")
    elif sugar < 10:
        recommendations.append("✅ Düşük şeker içeriği, diyabet için uygun.")
    
    # Lif kontrolü
    if fiber > 5:
        recommendations.append("✅ Yüksek lif içeriği kan şekeri kontrolüne yardımcı olur.")
    elif fiber < 2:
        recommendations.append("⚠️ Düşük lif içeriği. Sebze ve tam tahıl ekleyin.")
    
    # Öğün bazlı öneriler
    if meal_type == 'Kahvaltı':
        recommendations.append("🍳 Kahvaltıda protein ekleyerek kan şekeri dengesini koruyun.")
    elif meal_type == 'Akşam Yemeği':
        recommendations.append("🌙 Akşam yemeğinde karbohidratı sınırlı tutun.")
    
    return recommendations

# Model tahmin fonksiyonu
def predict_blood_sugar_impact(food_data):
    """
    Girilen gıda verisine göre kan şekeri etkisini tahmin eder
    """
    # Veriyi model formatına dönüştür
    input_data = np.array(food_data).reshape(1, -1)
    input_scaled = scaler.transform(input_data)
    
    # Tahminleri yap
    bg_increase = bg_model.predict(input_scaled)[0]
    risk_level = risk_model.predict(input_scaled)[0]
    
    risk_labels = ['Düşük Risk', 'Orta Risk', 'Yüksek Risk']
    risk_text = risk_labels[risk_level]
    
    return bg_increase, risk_text

# Test fonksiyonu
def test_model_prediction():
    """Model tahminini test eder"""
    print("\n" + "="*50)
    print("MODEL TEST EDİLİYOR")
    print("="*50)
    
    # Örnek gıda verisi: [kalori, karb, protein, yağ, lif, şeker, gi, porsiyon, yemek_kat, öğün, yaş, bmi, diyabet_tipi, hba1c]
    test_foods = [
        {
            'name': 'Beyaz Ekmek (2 dilim)',
            'data': [160, 30, 6, 2, 2, 3, 75, 60, 0, 0, 45, 25.0, 1, 7.2],
            'carbs': 30, 'gi': 75, 'sugar': 3, 'fiber': 2, 'meal_type': 'Kahvaltı', 'diabetes_type': 'Tip 2'
        },
        {
            'name': 'Sebzeli Omlet',
            'data': [200, 8, 18, 12, 3, 4, 35, 150, 1, 0, 45, 25.0, 1, 7.2],
            'carbs': 8, 'gi': 35, 'sugar': 4, 'fiber': 3, 'meal_type': 'Kahvaltı', 'diabetes_type': 'Tip 2'
        },
        {
            'name': 'Tam Buğday Makarna',
            'data': [180, 35, 8, 1, 6, 2, 50, 100, 0, 2, 45, 25.0, 1, 7.2],
            'carbs': 35, 'gi': 50, 'sugar': 2, 'fiber': 6, 'meal_type': 'Öğle Yemeği', 'diabetes_type': 'Tip 2'
        }
    ]
    
    for food in test_foods:
        print(f"\n🍽️ {food['name']}:")
        
        # Tahmin yap
        bg_increase, risk_level = predict_blood_sugar_impact(food['data'])
        
        print(f"   📊 Tahmini kan şekeri artışı: {bg_increase:.1f} mg/dL")
        print(f"   ⚡ Risk seviyesi: {risk_level}")
        
        # Önerileri al
        recommendations = get_nutrition_recommendation(
            food['carbs'], food['gi'], food['sugar'], food['fiber'], 
            food['meal_type'], food['diabetes_type']
        )
        
        print("   💡 Öneriler:")
        for rec in recommendations:
            print(f"      {rec}")

# Test çalıştır
test_model_prediction()

# Modelleri kaydet
print("\n" + "="*50)
print("MODELLER KAYDEDİLİYOR")
print("="*50)

models = {
    'blood_sugar_model': bg_model,
    'risk_classification_model': risk_model,
    'scaler': scaler,
    'label_encoders': {
        'food_category': le_food,
        'meal_type': le_meal,
        'diabetes_type': le_diabetes,
        'risk_category': le_risk
    },
    'feature_names': feature_names
}

# Model dosyasını kaydet
with open('diabetcare_nutrition_model.pkl', 'wb') as f:
    pickle.dump(models, f)

print("✅ Model başarıyla 'diabetcare_nutrition_model.pkl' olarak kaydedildi!")
print("✅ Model sisteme entegre edilmeye hazır!")

# Model yükleme fonksiyonu
def load_trained_model():
    """Kaydedilen modeli yükler"""
    with open('diabetcare_nutrition_model.pkl', 'rb') as f:
        return pickle.load(f)

print("\n" + "="*50)
print("MODEL ÖZETİ")
print("="*50)
print(f"📊 Veri seti boyutu: {df.shape[0]} örnek")
print(f"🎯 Kan şekeri tahmin modeli RMSE: {rmse:.2f}")
print(f"🎯 Risk sınıflandırma doğruluğu: {accuracy:.3f}")
print(f"📁 Model dosyası: diabetcare_nutrition_model.pkl")
print("🚀 Sistem entegrasyonu için hazır!")