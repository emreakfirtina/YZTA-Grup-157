# C:\xampp\htdocs\diabetcareai2\backend\nutrition_api.py
from flask import Flask, request, jsonify
from flask_cors import CORS
import pickle
import numpy as np
import pandas as pd

app = Flask(__name__)
CORS(app)  # CORS sorunlarını çözer

# Model yükleme
def load_model():
    """Eğitilmiş modeli yükle"""
    try:
        with open('diabetcare_nutrition_model.pkl', 'rb') as f:
            return pickle.load(f)
    except Exception as e:
        print(f"Model yükleme hatası: {e}")
        return None

# Global model yükleme
model_data = load_model()

# Gıda veritabanı (genişletilebilir)
FOOD_DATABASE = {
    'beyaz_ekmek': {
        'name': 'Beyaz Ekmek (1 dilim)',
        'kalori': 80, 'karbohidrat': 15, 'protein': 3, 'yag': 1,
        'lif': 1, 'seker': 1, 'glisemik_indeks': 75, 'porsiyon': 30
    },
    'tam_bugday_ekmek': {
        'name': 'Tam Buğday Ekmek (1 dilim)',
        'kalori': 70, 'karbohidrat': 12, 'protein': 4, 'yag': 1,
        'lif': 3, 'seker': 1, 'glisemik_indeks': 45, 'porsiyon': 30
    },
    'pirinc': {
        'name': 'Beyaz Pirinç (1 porsiyon)',
        'kalori': 200, 'karbohidrat': 45, 'protein': 4, 'yag': 0,
        'lif': 1, 'seker': 0, 'glisemik_indeks': 85, 'porsiyon': 150
    },
    'kahverengi_pirinc': {
        'name': 'Kahverengi Pirinç (1 porsiyon)',
        'kalori': 180, 'karbohidrat': 40, 'protein': 5, 'yag': 1,
        'lif': 4, 'seker': 0, 'glisemik_indeks': 55, 'porsiyon': 150
    },
    'elma': {
        'name': 'Elma (1 adet)',
        'kalori': 80, 'karbohidrat': 20, 'protein': 0, 'yag': 0,
        'lif': 4, 'seker': 15, 'glisemik_indeks': 35, 'porsiyon': 150
    },
    'muz': {
        'name': 'Muz (1 adet)',
        'kalori': 100, 'karbohidrat': 25, 'protein': 1, 'yag': 0,
        'lif': 3, 'seker': 18, 'glisemik_indeks': 60, 'porsiyon': 120
    },
    'tavuk_gogus': {
        'name': 'Tavuk Göğsü (100g)',
        'kalori': 165, 'karbohidrat': 0, 'protein': 31, 'yag': 4,
        'lif': 0, 'seker': 0, 'glisemik_indeks': 0, 'porsiyon': 100
    },
    'yogurt': {
        'name': 'Yoğurt (1 kase)',
        'kalori': 120, 'karbohidrat': 12, 'protein': 8, 'yag': 4,
        'lif': 0, 'seker': 12, 'glisemik_indeks': 35, 'porsiyon': 150
    },
    'makarna': {
        'name': 'Makarna (1 porsiyon)',
        'kalori': 220, 'karbohidrat': 45, 'protein': 8, 'yag': 1,
        'lif': 2, 'seker': 2, 'glisemik_indeks': 65, 'porsiyon': 100
    },
    'badem': {
        'name': 'Badem (1 avuç)',
        'kalori': 160, 'karbohidrat': 6, 'protein': 6, 'yag': 14,
        'lif': 4, 'seker': 1, 'glisemik_indeks': 15, 'porsiyon': 28
    }
}

def get_nutrition_recommendations(food_data):
    """Beslenme önerileri oluştur"""
    recommendations = []
    
    carbs = food_data['karbohidrat']
    gi = food_data['glisemik_indeks']
    sugar = food_data['seker']
    fiber = food_data['lif']
    
    # Karbohidrat kontrolü
    if carbs > 60:
        recommendations.append({
            'type': 'warning',
            'icon': 'fas fa-exclamation-triangle',
            'message': 'Yüksek karbohidrat içeriği! Porsiyonu azaltmayı düşünün.'
        })
    elif carbs < 15:
        recommendations.append({
            'type': 'success',
            'icon': 'fas fa-check-circle',
            'message': 'Düşük karbohidrat içeriği, kan şekeri için uygun.'
        })
    
    # Glisemik indeks kontrolü
    if gi > 70:
        recommendations.append({
            'type': 'warning',
            'icon': 'fas fa-arrow-up',
            'message': 'Yüksek glisemik indeks. Lif açısından zengin gıdalarla kombine edin.'
        })
    elif gi < 55:
        recommendations.append({
            'type': 'success',
            'icon': 'fas fa-thumbs-up',
            'message': 'Düşük glisemik indeks, kan şekeri kontrolü için ideal.'
        })
    
    # Şeker kontrolü
    if sugar > 25:
        recommendations.append({
            'type': 'danger',
            'icon': 'fas fa-times-circle',
            'message': 'Çok yüksek şeker içeriği! Bu gıdayı sınırlayın.'
        })
    elif sugar < 10:
        recommendations.append({
            'type': 'success',
            'icon': 'fas fa-check',
            'message': 'Düşük şeker içeriği, diyabet için uygun.'
        })
    
    # Lif kontrolü
    if fiber > 5:
        recommendations.append({
            'type': 'success',
            'icon': 'fas fa-leaf',
            'message': 'Yüksek lif içeriği kan şekeri kontrolüne yardımcı olur.'
        })
    elif fiber < 2:
        recommendations.append({
            'type': 'info',
            'icon': 'fas fa-info-circle',
            'message': 'Düşük lif içeriği. Sebze ve tam tahıl ekleyin.'
        })
    
    return recommendations

@app.route('/api/foods', methods=['GET'])
def get_foods():
    """Mevcut gıda listesini döndür"""
    return jsonify({
        'success': True,
        'foods': FOOD_DATABASE
    })

@app.route('/api/analyze_food', methods=['POST'])
def analyze_food():
    """Seçilen gıdayı analiz et"""
    try:
        data = request.json
        food_key = data.get('food_id')
        meal_type = data.get('meal_type', 'Öğle Yemeği')
        quantity = data.get('quantity', 1)
        
        # Hasta bilgileri (varsayılan değerler)
        age = data.get('age', 45)
        bmi = data.get('bmi', 25.0)
        diabetes_type = data.get('diabetes_type', 'Tip 2')
        hba1c = data.get('hba1c', 7.2)
        
        if food_key not in FOOD_DATABASE:
            return jsonify({'success': False, 'message': 'Gıda bulunamadı'})
        
        food_info = FOOD_DATABASE[food_key].copy()
        
        # Miktara göre besin değerlerini ayarla
        for key in ['kalori', 'karbohidrat', 'protein', 'yag', 'lif', 'seker']:
            food_info[key] *= quantity
        
        if model_data is None:
            return jsonify({'success': False, 'message': 'Model yüklenemedi'})
        
        # Model tahmin verisi hazırla
        # Format: [kalori, karb, protein, yağ, lif, şeker, gi, porsiyon, yemek_kat, öğün, yaş, bmi, diyabet_tipi, hba1c]
        meal_mapping = {'Kahvaltı': 0, 'Ara Öğün': 1, 'Öğle Yemeği': 2, 'İkindi': 3, 'Akşam Yemeği': 4}
        diabetes_mapping = {'Tip 1': 0, 'Tip 2': 1}
        
        model_input = [
            food_info['kalori'],
            food_info['karbohidrat'],
            food_info['protein'],
            food_info['yag'],
            food_info['lif'],
            food_info['seker'],
            food_info['glisemik_indeks'],
            food_info['porsiyon'] * quantity,
            0,  # yemek kategorisi (basit)
            meal_mapping.get(meal_type, 2),
            age,
            bmi,
            diabetes_mapping.get(diabetes_type, 1),
            hba1c
        ]
        
        # Model tahmini
        input_array = np.array(model_input).reshape(1, -1)
        input_scaled = model_data['scaler'].transform(input_array)
        
        bg_increase = model_data['blood_sugar_model'].predict(input_scaled)[0]
        risk_level = model_data['risk_classification_model'].predict(input_scaled)[0]
        
        risk_labels = ['Düşük Risk', 'Orta Risk', 'Yüksek Risk']
        risk_text = risk_labels[risk_level] if risk_level < len(risk_labels) else 'Bilinmiyor'
        
        # Önerileri al
        recommendations = get_nutrition_recommendations(food_info)
        
        return jsonify({
            'success': True,
            'food_info': food_info,
            'predictions': {
                'blood_sugar_increase': round(bg_increase, 1),
                'risk_level': risk_text,
                'risk_color': ['success', 'warning', 'danger'][risk_level] if risk_level < 3 else 'secondary'
            },
            'recommendations': recommendations
        })
        
    except Exception as e:
        return jsonify({'success': False, 'message': f'Hata: {str(e)}'})

@app.route('/api/save_meal', methods=['POST'])
def save_meal():
    """Öğün kaydını kaydet"""
    try:
        data = request.json
        # Burada veritabanına kayıt yapılabilir
        # Şimdilik sadece başarılı response döndürüyoruz
        
        return jsonify({
            'success': True,
            'message': 'Öğün başarıyla kaydedildi!'
        })
        
    except Exception as e:
        return jsonify({'success': False, 'message': f'Kayıt hatası: {str(e)}'})

if __name__ == '__main__':
    print("DiabetCare AI Beslenme API başlatılıyor...")
    print("Model durumu:", "✅ Yüklendi" if model_data else "❌ Yüklenemedi")
    app.run(debug=True, host='127.0.0.1', port=5000)