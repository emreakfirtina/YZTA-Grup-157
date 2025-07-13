
import streamlit as st
import joblib
import pandas as pd


model = joblib.load("optimized_random_forest.pkl")
scaler = joblib.load("scaler.pkl")

st.set_page_config(page_title="Diyabet Tahmin", page_icon="🧪")
st.title("🩺 Diyabet Tahmin Uygulaması")

st.markdown("Lütfen aşağıdaki bilgileri girerek tahmin alınız:")

# Kullanıcıdan bilgi alma 
pregnancies = st.number_input("Hamilelik Sayısı", min_value=0, step=1)
glucose = st.number_input("Glukoz Seviyesi", min_value=0)
blood_pressure = st.number_input("Kan Basıncı (mm Hg)", min_value=0)
skin_thickness = st.number_input("Cilt Kalınlığı (mm)", min_value=0)
insulin = st.number_input("İnsülin Seviyesi", min_value=0)
bmi = st.number_input("Vücut Kitle İndeksi (BMI)", min_value=0.0, format="%.1f")
dpf = st.number_input("Diyabet Soy Geçmişi", min_value=0.0, max_value=2.5, format="%.2f")
age = st.number_input("Yaş", min_value=0, step=1)

if st.button("📊 Tahmin Et"):
    input_data = {
        "Pregnancies": pregnancies,
        "Glucose": glucose,
        "BloodPressure": blood_pressure,
        "SkinThickness": skin_thickness,
        "Insulin": insulin,
        "BMI": bmi,
        "DiabetesPedigreeFunction": dpf,
        "Age": age
    }

    
    input_df = pd.DataFrame([input_data])
    scaled_input = scaler.transform(input_df)

    
    prediction = model.predict(scaled_input)[0]
    probability = model.predict_proba(scaled_input)[0][1]

   
    if prediction == 1:
        st.error(f"🔴 Diyabet hastası olabilirsiniz. (Olasılık: %{probability*100:.1f})")
    else:
        st.success(f"🟢 Diyabet riski düşük. (Olasılık: %{probability*100:.1f})")
