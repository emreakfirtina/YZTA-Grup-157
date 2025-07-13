import joblib
import pandas as pd

model = joblib.load("optimized_random_forest.pkl")
scaler = joblib.load("scaler.pkl")

def predict_diabetes(data: dict):
    df = pd.DataFrame([data])
    scaled = scaler.transform(df)
    prediction = model.predict(scaled)[0]
    probability = model.predict_proba(scaled)[0][1]
    return prediction, round(probability * 100, 2)
