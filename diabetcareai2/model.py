import pandas as pd
from sklearn.ensemble import RandomForestClassifier
from sklearn.preprocessing import StandardScaler
import pickle

# 1. Veri setini oku
df = pd.read_csv("diabetes.csv")

# 2. Özellikleri ve hedef değişkeni ayır
X = df.drop(columns=["Outcome"])
y = df["Outcome"]

# 3. Veriyi ölçeklendir
scaler = StandardScaler()
X_scaled = scaler.fit_transform(X)

# 4. Modeli oluştur ve eğit
model = RandomForestClassifier(random_state=42)
model.fit(X_scaled, y)

# 5. Scaler'ı kaydet
with open("scaler.pkl", "wb") as f:
    pickle.dump(scaler, f)

# 6. Modeli kaydet
with open("optimized_random_forest.pkl", "wb") as f:
    pickle.dump(model, f)

print("Model ve scaler başarıyla kaydedildi.")
