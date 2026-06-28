import numpy as np

def adapt(data, scaler):
    expected = scaler.n_features_in_
    current = data.shape[1]

    if current == expected:
        return data

    if current < expected:
        last_col = data[:, -1].reshape(-1, 1)
        extra = np.repeat(last_col, expected - current, axis=1)
        return np.concatenate([data, extra], axis=1)

    return data[:, :expected]