from fastapi import FastAPI, Request, Form
from fastapi.responses import HTMLResponse
from fastapi.templating import Jinja2Templates
from typing import Optional
from model import predict_diabetes

app = FastAPI()
templates = Jinja2Templates(directory="templates")

def safe_int(value: Optional[str]) -> int:
    try:
        return int(value)
    except (TypeError, ValueError):
        return 0

def safe_float(value: Optional[str]) -> float:
    try:
        return float(value)
    except (TypeError, ValueError):
        return 0.0

@app.get("/", response_class=HTMLResponse)
async def form_get(request: Request):
    return templates.TemplateResponse("form.html", {"request": request})

@app.post("/predict", response_class=HTMLResponse)
async def predict(
    request: Request,
    Pregnancies: Optional[str] = Form(None),
    Glucose: Optional[str] = Form(None),
    BloodPressure: Optional[str] = Form(None),
    SkinThickness: Optional[str] = Form(None),
    Insulin: Optional[str] = Form(None),
    BMI: Optional[str] = Form(None),
    DiabetesPedigreeFunction: Optional[str] = Form(None),
    Age: Optional[str] = Form(None),
):
    input_data = {
        "Pregnancies": safe_int(Pregnancies),
        "Glucose": safe_int(Glucose),
        "BloodPressure": safe_int(BloodPressure),
        "SkinThickness": safe_int(SkinThickness),
        "Insulin": safe_int(Insulin),
        "BMI": safe_float(BMI),
        "DiabetesPedigreeFunction": safe_float(DiabetesPedigreeFunction),
        "Age": safe_int(Age),
    }

    prediction, probability = predict_diabetes(input_data)
    result = "🔴 Diyabet riski yüksek." if prediction == 1 else "🟢 Diyabet riski düşük."

    return templates.TemplateResponse("form.html", {
        "request": request,
        "result": result,
        "probability": probability
    })
