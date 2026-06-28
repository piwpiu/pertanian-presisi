import numpy as np
import pandas as pd

def predict_rh(model_rh, scaler_X_rh, scaler_y_rh, data, steps, last_date, forecast=None):
    input_features = [
        'RH_AVG',
        'sin_day',
        'cos_day',
        'month_sin',
        'month_cos'
    ]

    target_col = 'RH_AVG'

    # np.random.seed(42)
    data = data[input_features].copy()
    current_input = scaler_X_rh.transform(data.values)

    future_preds_scaled = []

    for i in range(steps):
        pred_scaled = model_rh.predict(
            current_input.reshape(1, current_input.shape[0], current_input.shape[1]),
            verbose=0
        )[0][0]

        pred_actual = scaler_y_rh.inverse_transform(
            [[pred_scaled]]
        )[0][0]

        if forecast is not None and len(forecast) > 0:
            forecasting = np.random.choice(forecast)
            pred_actual_var = pred_actual + (0.5 * forecasting)
        else:
            pred_actual_var = pred_actual

        pred_actual_var = np.clip(pred_actual_var, 0, 100)

        pred_scaled_var = scaler_y_rh.transform(
            [[pred_actual_var]]
        )[0][0]

        future_preds_scaled.append(pred_scaled_var)

        next_date = last_date + pd.Timedelta(days=i + 1)
        day_of_year = next_date.dayofyear
        month = next_date.month

        new_row = current_input[-1].copy()

        new_row[input_features.index(target_col)] = pred_scaled_var
        new_row[input_features.index('sin_day')] = np.sin(2 * np.pi * day_of_year / 365)
        new_row[input_features.index('cos_day')] = np.cos(2 * np.pi * day_of_year / 365)
        new_row[input_features.index('month_sin')] = np.sin(2 * np.pi * month / 12)
        new_row[input_features.index('month_cos')] = np.cos(2 * np.pi * month / 12)

        current_input = np.vstack([current_input[1:], new_row])

    future_preds = scaler_y_rh.inverse_transform(
        np.array(future_preds_scaled).reshape(-1, 1)
    ).flatten()

    future_preds = np.round(future_preds, 1)

    # return round(float(future_preds[-1]), 1)
    return future_preds.tolist()