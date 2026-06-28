import numpy as np
import pandas as pd


def predict_rr_hybrid(model_rr_reg, model_rr_class, scaler_X_reg, scaler_X_class, scaler_y_reg, data, steps, last_date, forecast=None):
    input_features_class = [
        'TN', 'TX', 'TAVG',
        'RH_AVG', 'SS',
        'sin_day',
        'cos_day',
        'month_sin',
        'month_cos'
    ]

    features_reg = [
        'TAVG', 'TN', 'TX',
        'RH_AVG', 'SS',
        'sin_day',
        'cos_day',
        'month_sin',
        'month_cos',
        'RR_lag1'
    ]

    residual_weight = 0.5
    seasonal_weight = 0
    persistence_weight = 0

    # np.random.seed(42)
    # current_input_reg = scaler_X_reg.transform(data[features_reg].values)
    # current_input_class = scaler_X_class.transform(data[input_features_class].values)

    # n_steps = current_input_reg.shape[0]
    # n_features_reg = current_input_reg.shape[1]
    # n_features_class = current_input_class.shape[1]

    # if forecast is None or len(forecast) == 0:
    #     forecast = np.array([0])

    # last_rr = float(data['RR_lag1'].iloc[-1])

    current_input_reg = scaler_X_reg.transform(data[features_reg].values)
    current_input_class = scaler_X_class.transform(data[input_features_class].values)

    n_steps = current_input_reg.shape[0]
    n_features_reg = current_input_reg.shape[1]
    n_features_class = current_input_class.shape[1]

    if forecast is None or len(forecast) == 0:
        forecast = np.array([0])
    else:
        forecast = np.array(forecast)

        residual_min = np.percentile(forecast, 5)
        residual_max = np.percentile(forecast, 95)

        forecast = np.clip(forecast, residual_min, residual_max)

    last_rr = float(data['RR_lag1'].iloc[-1])

    future_rr = []

    # dry_streak = 0

    for i in range(steps):
        next_date = last_date + pd.Timedelta(days=i + 1)
        day_of_year = next_date.dayofyear
        month = next_date.month

        rain_prob = model_rr_class.predict(
            current_input_class.reshape(1, n_steps, n_features_class),
            verbose=0
        )[0][0]

        pred_scaled = model_rr_reg.predict(
            current_input_reg.reshape(1, n_steps, n_features_reg),
            verbose=0
        )[0][0]

        pred_log = scaler_y_reg.inverse_transform(
            [[pred_scaled]]
        )[0][0]

        base_rr = np.expm1(pred_log)
        base_rr = max(0, base_rr)

        seasonal_factor = 1 + (seasonal_weight * np.sin(2 * np.pi * day_of_year / 365))
        seasonal_rr = base_rr * seasonal_factor
        persistence_rr = ((1 - persistence_weight) * seasonal_rr + persistence_weight * last_rr)

        forecast_sample = np.random.choice(forecast)

        adjusted_rr = persistence_rr + (residual_weight * forecast_sample)
        adjusted_rr = max(0, adjusted_rr)

        # Soft Hybrid Classification
        if rain_prob < 0.3:
            final_rr = 0
        else:
            final_rr = adjusted_rr 
            # final_rr = adjusted_rr * rain_prob

        final_rr = max(0, final_rr)

        # Hard Hybrid Classification
        # if rain_prob < 0.4:
        #     if dry_streak >= 7 and np.random.rand() < 0.25:
        #         final_rr = min(adjusted_rr * 0.5, 15)
        #     else:
        #         final_rr = 0
        # else:
        #     final_rr = adjusted_rr
        # final_rr = max(0, final_rr)
        # if final_rr == 0:
        #     dry_streak += 1
        # else:
        #     dry_streak = 0

        future_rr.append(final_rr)

        new_row_reg = current_input_reg[-1].copy()

        if 'RR_lag1' in features_reg:new_row_reg[features_reg.index('RR_lag1')] = final_rr
        if 'sin_day' in features_reg:new_row_reg[features_reg.index('sin_day')] = np.sin(2 * np.pi * day_of_year / 365)
        if 'cos_day' in features_reg:new_row_reg[features_reg.index('cos_day')] = np.cos(2 * np.pi * day_of_year / 365)
        if 'month_sin' in features_reg:new_row_reg[features_reg.index('month_sin')] = np.sin(2 * np.pi * month / 12)
        if 'month_cos' in features_reg:new_row_reg[features_reg.index('month_cos')] = np.cos(2 * np.pi * month / 12)

        current_input_reg = np.vstack([current_input_reg[1:], new_row_reg])

        new_row_class = current_input_class[-1].copy()

        if 'RR' in input_features_class:new_row_class[input_features_class.index('RR')] = final_rr
        if 'sin_day' in input_features_class:new_row_class[input_features_class.index('sin_day')] = np.sin(2 * np.pi * day_of_year / 365)
        if 'cos_day' in input_features_class:new_row_class[input_features_class.index('cos_day')] = np.cos(2 * np.pi * day_of_year / 365)
        if 'month_sin' in input_features_class:new_row_class[input_features_class.index('month_sin')] = np.sin(2 * np.pi * month / 12)
        if 'month_cos' in input_features_class:new_row_class[input_features_class.index('month_cos')] = np.cos(2 * np.pi * month / 12)

        current_input_class = np.vstack([current_input_class[1:], new_row_class])

        last_rr = final_rr

    future_rr = np.round(np.array(future_rr), 1)

    #return round(float(future_rr[-1]), 2)
    return future_rr.tolist()