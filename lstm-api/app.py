from flask import Flask, jsonify, request
import json
import os

import pandas as pd
import numpy as np
from sqlalchemy import create_engine, text

from model_loader import *

from predictors.tavg import predict_tavg
from predictors.rh import predict_rh
from predictors.rr import predict_rr_hybrid


TIMESTEPS = 7

REQUIRED_COLS = [
    'TANGGAL','TN','TX','TAVG','RH_AVG','SS','RR',
    'sin_day','cos_day','month_sin','month_cos',
    'RR_lag1'
]

FEATURES_TAVG = [
    'TAVG',
    'sin_day','cos_day',
    'month_sin','month_cos'
]

FEATURES_RH = [
    'RH_AVG',
    'sin_day','cos_day',
    'month_sin','month_cos'
]

FEATURES_RR = [
    'TN','TX','TAVG','RH_AVG','SS',
    'sin_day','cos_day','month_sin','month_cos',
    'RR_lag1'
]


def load_dotenv_file():
    base_dir = os.path.abspath(os.path.join(os.path.dirname(__file__), '..'))
    dotenv_path = os.path.join(base_dir, '.env')

    if not os.path.exists(dotenv_path):
        return

    with open(dotenv_path, 'r', encoding='utf-8') as f:
        for line in f:
            line = line.strip()

            if not line or line.startswith('#') or '=' not in line:
                continue

            name, value = line.split('=', 1)
            name = name.strip()
            value = value.strip().strip('"').strip("'")

            if name and name not in os.environ:
                os.environ[name] = value


load_dotenv_file()


def get_db_engine():
    user = os.getenv('DB_USERNAME', 'root')
    password = os.getenv('DB_PASSWORD', '')
    host = os.getenv('DB_HOST')
    port = os.getenv('DB_PORT', '3306')
    database = os.getenv('DB_DATABASE', 'smart_farming')

    if not host:
        raise RuntimeError('DB_HOST is not configured.')

    auth = f"{user}:{password}" if password else user
    url = f"mysql+pymysql://{auth}@{host}:{port}/{database}"

    return create_engine(url)


def parse_json(value):
    if isinstance(value, dict):
        return value

    if isinstance(value, str) and value.strip():
        return json.loads(value)

    return {}


def remove_date_key(value):
    if isinstance(value, dict):
        return {
            k: v for k, v in value.items()
            if k.lower() != 'tanggal'
        }

    return value


def load_data_from_db():
    engine = get_db_engine()
    query = text('SELECT tanggal, data_json FROM klimatologi ORDER BY tanggal')

    df = pd.read_sql(query, engine)

    if 'data_json' not in df.columns or df.empty:
        raise RuntimeError('Tidak ada data_json atau tabel klimatologi kosong di database.')

    df['data_json'] = df['data_json'].apply(parse_json)
    df['data_json'] = df['data_json'].apply(remove_date_key)

    json_df = pd.json_normalize(df['data_json'])
    json_df.columns = [str(col).upper() for col in json_df.columns]

    df = pd.concat([df[['tanggal']].rename(columns={'tanggal': 'TANGGAL'}), json_df], axis=1)

    df['TANGGAL'] = pd.to_datetime(df['TANGGAL'])

    numeric_cols = ['TN', 'TX', 'RH_AVG', 'SS', 'TAVG', 'RR']

    for col in numeric_cols:
        if col in df.columns:
            df[col] = pd.to_numeric(df[col], errors='coerce')

    return df


def prepare_features(df):
    df = df.copy()

    df['day_of_year'] = df['TANGGAL'].dt.dayofyear
    df['sin_day'] = np.sin(2 * np.pi * df['day_of_year'] / 365)
    df['cos_day'] = np.cos(2 * np.pi * df['day_of_year'] / 365)

    df['month'] = df['TANGGAL'].dt.month
    df['month_sin'] = np.sin(2 * np.pi * df['month'] / 12)
    df['month_cos'] = np.cos(2 * np.pi * df['month'] / 12)

    df['RR_lag1'] = df['RR'].shift(1)

    return df


def validate_dataframe(df, timesteps=TIMESTEPS):
    missing_cols = [
        col for col in REQUIRED_COLS
        if col not in df.columns
    ]

    if missing_cols:
        return None, {
            "error": "Kolom yang dibutuhkan tidak tersedia",
            "missing_columns": missing_cols
        }

    df = df.dropna(subset=REQUIRED_COLS)

    if df.empty:
        return None, {"error": "Data klimatologi tidak cukup setelah preprocessing"}

    if len(df) < timesteps:
        return None, {"error": f"Data minimal harus memiliki {timesteps} baris"}

    return df, None


def create_sequences(X_scaled, y_scaled, timesteps):
    X_seq = []
    y_seq = []

    for i in range(len(X_scaled) - timesteps):
        X_seq.append(X_scaled[i:i + timesteps])
        y_seq.append(y_scaled[i + timesteps])

    return np.array(X_seq), np.array(y_seq)


def create_residual_tavg_rh(df, model, scaler_X, scaler_y, input_features, target_col, timesteps=TIMESTEPS):
    X = df[input_features].values
    y = df[target_col].values.reshape(-1, 1)

    X_scaled = scaler_X.transform(X)
    y_scaled = scaler_y.transform(y)

    X_seq, y_seq = create_sequences(X_scaled, y_scaled, timesteps)

    y_pred_scaled = model.predict(X_seq, verbose=0)

    y_actual = scaler_y.inverse_transform(
        y_seq.reshape(-1, 1)
    ).flatten()

    y_pred = scaler_y.inverse_transform(
        y_pred_scaled.reshape(-1, 1)
    ).flatten()

    residual = y_actual - y_pred

    return residual


def create_residual_rr(df, model_rr_reg, scaler_X_reg, scaler_y_reg, features_reg, timesteps=TIMESTEPS):
    X = df[features_reg].values
    y = np.log1p(df['RR'].values).reshape(-1, 1)

    X_scaled = scaler_X_reg.transform(X)
    y_scaled = scaler_y_reg.transform(y)

    X_seq, y_seq = create_sequences(X_scaled, y_scaled, timesteps)

    y_pred_scaled = model_rr_reg.predict(X_seq, verbose=0)

    y_actual_log = scaler_y_reg.inverse_transform(
        y_seq.reshape(-1, 1)
    ).flatten()

    y_pred_log = scaler_y_reg.inverse_transform(
        y_pred_scaled.reshape(-1, 1)
    ).flatten()

    y_actual = np.expm1(y_actual_log)
    y_pred = np.expm1(y_pred_log)

    residual = y_actual - y_pred

    residual_min = np.percentile(residual, 5)
    residual_max = np.percentile(residual, 95)

    residual = np.clip(residual, residual_min, residual_max)

    return residual


def get_prepared_dataframe():
    df = load_data_from_db()
    df = prepare_features(df)

    df, error = validate_dataframe(df, TIMESTEPS)

    return df, error


def create_residuals(df):
    residual_tavg = create_residual_tavg_rh(df, model_tavg, scaler_X_tavg, scaler_y_tavg, FEATURES_TAVG, 'TAVG', TIMESTEPS)
    residual_rh = create_residual_tavg_rh(df, model_rh, scaler_X_rh, scaler_y_rh, FEATURES_RH, 'RH_AVG', TIMESTEPS)
    residual_rr = create_residual_rr(df, model_rr_reg, scaler_X_rr_reg, scaler_y_rr_reg, FEATURES_RR, TIMESTEPS)

    return residual_tavg, residual_rh, residual_rr


def create_prediction_input_data(df):
    return {
        "data_tavg": df[FEATURES_TAVG].tail(TIMESTEPS),
        "data_rh": df[FEATURES_RH].tail(TIMESTEPS),
        "data_rr": df[FEATURES_RR].tail(TIMESTEPS),
    }


def predict_future_values(df, steps):
    last_date = df['TANGGAL'].max()

    residual_tavg, residual_rh, residual_rr = create_residuals(df)
    input_data = create_prediction_input_data(df)

    tavg_preds = predict_tavg(
        model_tavg, scaler_X_tavg, scaler_y_tavg,
        input_data["data_tavg"],
        steps, last_date, residual_tavg
    )

    rh_preds = predict_rh(
        model_rh, scaler_X_rh, scaler_y_rh,
        input_data["data_rh"],
        steps, last_date, residual_rh
    )

    rr_preds = predict_rr_hybrid(
        model_rr_reg, model_rr_class,
        scaler_X_rr_reg, scaler_X_rr_class, scaler_y_rr_reg,
        input_data["data_rr"],
        steps, last_date, residual_rr
    )

    return {
        "last_date": last_date,
        "tavg": tavg_preds,
        "rh": rh_preds,
        "rr": rr_preds,
    }


app = Flask(__name__)


@app.route('/predict')
def predict():
    tanggal_input = request.args.get('TANGGAL')

    if not tanggal_input:
        return jsonify({"error": "Parameter TANGGAL wajib diisi"}), 400

    try:
        tanggal_input = pd.to_datetime(tanggal_input)
    except Exception:
        return jsonify({"error": "Format TANGGAL tidak valid"}), 400

    try:
        df, error = get_prepared_dataframe()

        if error:
            return jsonify(error), 500

        if tanggal_input in df['TANGGAL'].values:
            row = df[df['TANGGAL'] == tanggal_input].iloc[0]

            return jsonify({
                "TAVG": float(row['TAVG']),
                "RR": float(row['RR']),
                "RH_AVG": float(row['RH_AVG']),
                "source": "actual"
            })

        last_date = df['TANGGAL'].max()
        selisih = (tanggal_input - last_date).days

        if selisih <= 0:
            return jsonify({"error": "Tanggal harus setelah data terakhir"}), 400

        prediction_result = predict_future_values(df, selisih)

        return jsonify({
            "TAVG": float(prediction_result["tavg"][-1]),
            "RR": float(prediction_result["rr"][-1]),
            "RH_AVG": float(prediction_result["rh"][-1]),
            "source": "predicted"
        })

    except Exception as e:
        return jsonify({"error": str(e)}), 500


@app.route('/generate-prediksi')
def generate_prediksi():
    try:
        np.random.seed(42)

        df, error = get_prepared_dataframe()

        if error:
            return jsonify(error), 500

        jumlah_hari_prediksi = int(request.args.get('days', 150))

        prediction_result = predict_future_values(df, jumlah_hari_prediksi)

        last_date = prediction_result["last_date"]

        hasil = []

        for i in range(jumlah_hari_prediksi):
            tanggal_prediksi = last_date + pd.Timedelta(days=i + 1)

            hasil.append({
                "tanggal": tanggal_prediksi.strftime('%Y-%m-%d'),
                "prediksi_suhu": float(prediction_result["tavg"][i]),
                "prediksi_kelembaban": float(prediction_result["rh"][i]),
                "prediksi_curah_hujan": float(prediction_result["rr"][i])
            })

        return jsonify({
            "message": "Prediksi berhasil dibuat",
            "total": len(hasil),
            "data": hasil
        })

    except Exception as e:
        return jsonify({"error": str(e)}), 500


if __name__ == '__main__':
    app.run(debug=True, port=5000)
