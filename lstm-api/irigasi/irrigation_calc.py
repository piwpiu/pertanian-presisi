import pandas as pd
import numpy as np
import math

def degrees_to_radians(deg):
    return deg * math.pi / 180.0


def extraterrestrial_radiation(day_of_year, latitude_deg):
    """Menghitung radiasi luar angkasa Ra (MJ/m2/hari) dan lamanya siang hari N."""
    phi = degrees_to_radians(latitude_deg)
    dr = 1 + 0.033 * math.cos(2 * math.pi * day_of_year / 365)
    delta = 0.409 * math.sin(2 * math.pi * day_of_year / 365 - 1.39)
    ws = math.acos(-math.tan(phi) * math.tan(delta))
    ra = (24 * 60 / math.pi) * 0.0820 * dr * (
        ws * math.sin(phi) * math.sin(delta) + math.cos(phi) * math.cos(delta) * math.sin(ws)
    )
    n = (24 / math.pi) * ws
    return ra, n


# Fungsi untuk menghitung ETo menggunakan Penman-Monteith FAO
def calculate_eto(tmax, tmin, rh_mean, sunshine_hours=None, rs=None, u2=2.0, z=250, latitude_deg=-6.6, day_of_year=None):
    """
    Menghitung ETo harian menggunakan formula Penman-Monteith FAO.

    Parameters:
    - tmax: Suhu maksimum (°C)
    - tmin: Suhu minimum (°C)
    - rh_mean: Kelembaban relatif rata-rata (%)
    - sunshine_hours: Durasi sinar matahari (jam/hari)
    - rs: Radiasi matahari langsung (MJ/m²/hari)
    - u2: Kecepatan angin 2m (m/s)
    - z: Elevasi (m)
    - latitude_deg: Garis lintang lokasi (derajat)
    - day_of_year: Hari ke-berapa dalam tahun

    Returns:
    - ETo (mm/hari)
    """
    tmean = (tmax + tmin) / 2.0
    p = 101.3 * ((293.0 - 0.0065 * z) / 293.0) ** 5.26
    gamma = 0.000665 * p
    delta = 4098.0 * (0.6108 * np.exp(17.27 * tmean / (tmean + 237.3))) / ((tmean + 237.3) ** 2)
    es = (0.6108 * np.exp(17.27 * tmax / (tmax + 237.3)) + 0.6108 * np.exp(17.27 * tmin / (tmin + 237.3))) / 2.0
    ea = es * (rh_mean / 100.0)

    if day_of_year is None:
        raise ValueError('day_of_year harus diberikan untuk perhitungan sinar matahari')

    ra, n_max = extraterrestrial_radiation(day_of_year, latitude_deg)

    if rs is not None and rs > 0:
        if rs <= 24:
            # Jika nilai kurang dari 24, asumsikan ini adalah jam sinar matahari
            n = rs
            as_ = 0.25
            bs = 0.50
            rs = (as_ + bs * n / n_max) * ra
        # jika rs > 24, diasumsikan sudah dalam satuan MJ/m2/hari
    elif sunshine_hours is not None and sunshine_hours >= 0:
        n = sunshine_hours
        as_ = 0.25
        bs = 0.50
        rs = (as_ + bs * n / n_max) * ra
    else:
        rs = 0.16 * np.sqrt(max(tmax - tmin, 0.0)) * ra

    rso = (0.75 + 2e-5 * z) * ra
    if rso <= 0:
        rn = 0.0
    else:
        rns = 0.77 * rs
        rnl = 4.903e-9 * (((tmax + 273.16) ** 4 + (tmin + 273.16) ** 4) / 2.0) * (0.34 - 0.14 * np.sqrt(ea)) * (1.35 * rs / rso - 0.35)
        rn = rns - rnl

    numerator = 0.408 * delta * rn + gamma * (900.0 / (tmean + 273.0)) * u2 * (es - ea)
    denominator = delta + gamma * (1.0 + 0.34 * u2)
    eto = numerator / denominator
    return max(eto, 0.0)

# Fungsi untuk curah hujan efektif
def effective_rainfall(rr):
    """
    Menghitung curah hujan efektif berdasarkan FAO guidelines.
    Asumsi sederhana: 80% dari hujan total untuk tanah sedang.
    """
    if rr < 5:
        return rr  # Semua efektif
    elif rr < 10:
        return 0.8 * rr
    else:
        return 0.7 * rr  # Kurangi runoff

# Fungsi utama perhitungan irigasi
def calculate_irrigation_need(data_path, kc_values, efficiency=0.6):
    """
    Menghitung kebutuhan irigasi padi.

    Parameters:
    - data_path: Path ke file Excel data cuaca
    - kc_values: Dict fase pertumbuhan {'initial': kc, 'mid': kc, 'late': kc}
    - efficiency: Efisiensi sistem irigasi (0-1)

    Returns:
    - DataFrame dengan hasil perhitungan
    """
    # Baca data
    df = pd.read_excel(data_path)

    # Mapping kolom (sesuai file Excel)
    col_map = {
        'tanggal': 'TANGGAL',
        'tmin': 'TN',
        'tmax': 'TX',
        'suhu': 'TAVG',
        'curah_hujan': 'RR',
        'kelembaban': 'RH_AVG',
        'radiasi': 'SS'
    }

    # Rename kolom jika perlu
    df.rename(columns={v: k for k, v in col_map.items()}, inplace=True)

    # Pastikan kolom ada
    required_cols = ['tanggal', 'tmin', 'tmax', 'suhu', 'curah_hujan', 'kelembaban']
    if not all(col in df.columns for col in required_cols):
        raise ValueError(f"Kolom data tidak lengkap. Pastikan ada: {required_cols}. Kolom tersedia: {df.columns.tolist()}")

    # Asumsi fase pertumbuhan (sederhana: initial 30 hari, mid 60 hari, late sisanya)
    df['tanggal'] = pd.to_datetime(df['tanggal'], format='%d-%m-%Y')
    df['hari'] = (df['tanggal'] - df['tanggal'].min()).dt.days
    df['day_of_year'] = df['tanggal'].dt.dayofyear
    df['fase'] = pd.cut(df['hari'], bins=[-1, 30, 90, 999], labels=['initial', 'mid', 'late'])

    # Hitung ETo
    def safe_calculate_eto(row):
        try:
            return calculate_eto(
                tmax=row['tmax'],
                tmin=row['tmin'],
                rh_mean=row['kelembaban'],
                rs=row.get('radiasi', None),
                u2=2.0,
                z=250,
                latitude_deg=-6.6,
                day_of_year=int(row['day_of_year'])
            )
        except Exception as e:
            print(f"Error calculating ETo for row {row.name}: {e}")
            return 0

    df['eto'] = df.apply(safe_calculate_eto, axis=1)

    # Kc berdasarkan fase
    df['kc'] = df['fase'].map(kc_values).astype(float)

    # ETc = Kc * ETo
    df['etc'] = df['kc'] * df['eto']

    # Curah hujan efektif
    df['rain_eff'] = df['curah_hujan'].apply(effective_rainfall)

    # Kebutuhan air bersih = ETc - Rain Eff
    df['water_need'] = df['etc'] - df['rain_eff']
    df['water_need'] = df['water_need'].clip(lower=0)  # Tidak negatif

    # Kebutuhan irigasi disalurkan = Water Need / Efficiency
    df['irrigation_need'] = df['water_need'] / efficiency

    return df[['tanggal', 'suhu', 'curah_hujan', 'kelembaban', 'eto', 'kc', 'etc', 'rain_eff', 'water_need', 'irrigation_need']]

# Contoh penggunaan
if __name__ == "__main__":
    # Path data
    data_path = "data/Data_Kota_Bogor_clean.xlsx"

    # Kc untuk padi (FAO)
    kc_padi = {
        'initial': 1.05,
        'mid': 1.15,
        'late': 0.95
    }

    # Efisiensi irigasi (60%)
    efficiency = 0.6

    try:
        result = calculate_irrigation_need(data_path, kc_padi, efficiency)
        print("Hasil perhitungan kebutuhan irigasi padi:")
        print(result.head(10))  # Tampilkan 10 baris pertama

        # Simpan ke CSV
        result.to_csv("irrigation_results.csv", index=False)
        print("Hasil disimpan ke irrigation_results.csv")

        # Rata-rata bulanan
        result['tanggal'] = pd.to_datetime(result['tanggal'])
        monthly = result.groupby(result['tanggal'].dt.to_period('M')).agg({
            'irrigation_need': 'sum',
            'curah_hujan': 'sum',
            'etc': 'mean'
        })
        print("\nRata-rata bulanan:")
        print(monthly)

    except Exception as e:
        print(f"Error: {e}")