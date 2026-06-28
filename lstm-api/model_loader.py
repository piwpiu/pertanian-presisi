import os

import joblib
from tensorflow.keras.models import load_model

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
SAVED_MODELS_DIR = os.path.join(BASE_DIR, "saved_models")


def model_path(filename):
    return os.path.join(SAVED_MODELS_DIR, filename)


model_tavg = load_model(model_path("model_tavg.h5"), compile=False)
model_rh = load_model(model_path("model_rh.h5"), compile=False)
model_rr_class = load_model(model_path("model_rr_class.h5"), compile=False)
model_rr_reg = load_model(model_path("model_rr_reg.h5"), compile=False)

scaler_X_tavg = joblib.load(model_path("scaler_X_tavg.pkl"))
scaler_y_tavg = joblib.load(model_path("scaler_y_tavg.pkl"))

scaler_X_rr_class = joblib.load(model_path("scaler_X_rr_class.pkl"))
scaler_X_rr_reg = joblib.load(model_path("scaler_X_rr_reg.pkl"))
scaler_y_rr_reg = joblib.load(model_path("scaler_y_rr_reg.pkl"))

scaler_X_rh = joblib.load(model_path("scaler_X_rh.pkl"))
scaler_y_rh = joblib.load(model_path("scaler_y_rh.pkl"))

forecast_tavg = joblib.load(model_path("forecast_tavg.pkl"))
forecast_rh = joblib.load(model_path("forecast_rh.pkl"))
forecast_rr = joblib.load(model_path("forecast_rr.pkl"))
# rr_last_input_reg = joblib.load(model_path(os.path.join("rr_regresi", "rr_last_input_reg.pkl")))
# rr_last_input_class = joblib.load(model_path(os.path.join("rr_regresi", "rr_last_input_class.pkl")))
# rr_last_rr = joblib.load(model_path(os.path.join("rr_regresi", "rr_last_rr.pkl")))
