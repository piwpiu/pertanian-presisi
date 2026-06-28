<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Klimatologi;
use App\Services\PredictionGenerationService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AdminController extends Controller
{
    // Login page
    public function loginPage()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    // Handle login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|min:6',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    // Admin dashboard
    public function dashboard(Request $request)
    {
        $today = Carbon::today();
        $selectedYear = (int) $request->input('year', $today->year);
        $selectedMonth = max(1, min(12, (int) $request->input('month', $today->month)));
        $selectedDate = Carbon::create($selectedYear, $selectedMonth, 1);
        $startOfMonth = $selectedDate->copy()->startOfMonth();
        $endOfMonth = $selectedDate->copy()->endOfMonth();
        $missingEndDate = $endOfMonth->greaterThan($today) ? $today : $endOfMonth;

        $klimatologiData = Klimatologi::whereYear('tanggal', $selectedYear)
            ->whereMonth('tanggal', $selectedMonth)
            ->orderBy('tanggal')
            ->paginate(31)
            ->withQueryString();

        $oldestDate = Klimatologi::min('tanggal');
        $latestDate = Klimatologi::max('tanggal');
        $availableYears = range(
            $oldestDate ? Carbon::parse($oldestDate)->year : $today->year,
            max($today->year, $latestDate ? Carbon::parse($latestDate)->year : $today->year)
        );

        $existingDates = [];
        if ($startOfMonth->lessThanOrEqualTo($today)) {
            $existingDates = Klimatologi::whereBetween('tanggal', [
                    $startOfMonth->toDateString(),
                    $missingEndDate->toDateString(),
                ])
                ->pluck('tanggal')
                ->map(fn ($date) => Carbon::parse($date)->toDateString())
                ->all();
        }

        $missingDates = [];
        if ($startOfMonth->lessThanOrEqualTo($today)) {
            foreach (CarbonPeriod::create($startOfMonth, $missingEndDate) as $date) {
                if (!in_array($date->toDateString(), $existingDates, true)) {
                    $missingDates[] = $date->copy();
                }
            }
        }

        $summary = [
            'total' => Klimatologi::count(),
            'selected_total' => $klimatologiData->total(),
            'latest_date' => $latestDate,
            'oldest_date' => $oldestDate,
            'missing_dates' => $missingDates,
            'missing_count' => count($missingDates),
            'selected_month_label' => $selectedDate->locale('id')->translatedFormat('F Y'),
            'period_label' => $this->formatPeriodLabel($startOfMonth, $endOfMonth),
            'missing_period_end' => $startOfMonth->lessThanOrEqualTo($today) ? $missingEndDate : null,
        ];

        return view('admin.dashboard', [
            'data' => $klimatologiData,
            'summary' => $summary,
            'selectedYear' => $selectedYear,
            'selectedMonth' => $selectedMonth,
            'availableYears' => $availableYears,
        ]);
    }

    private function formatPeriodLabel(Carbon $startDate, Carbon $endDate): string
    {
        return $startDate->format('j') . ' - ' . $endDate->locale('id')->translatedFormat('j F Y');
    }

    // Show form edit/create
    public function edit($id = null)
    {
        $data = null;
        if ($id) {
            $data = Klimatologi::findOrFail($id);
        }
        return view('admin.form', ['data' => $data]);
    }

    // Store/Update data
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'TN' => 'required|numeric',
            'TX' => 'required|numeric',
            'TAVG' => 'required|numeric',
            'RH_AVG' => 'required|numeric',
            'RR' => 'required|numeric',
            'SS' => 'required|numeric',
            'id' => 'nullable|integer',
        ]);

        $id = $validated['id'] ?? null;
        unset($validated['id']);

        $validated['data_json'] = $this->buildDataJson($validated);
        
        // Check duplicate tanggal (exclude current record if updating)
        $query = Klimatologi::where('tanggal', $validated['tanggal']);
        if ($id) {
            $query->where('id', '!=', $id);
        }
        
        if ($query->exists()) {
            return back()->withErrors([
                'tanggal' => 'Data untuk tanggal ini sudah ada di database.',
            ])->withInput();
        }

        if ($id) {
            $klimatologi = Klimatologi::findOrFail($id);
            $klimatologi->update($validated);
            $message = 'Data berhasil diperbarui.';
        } else {
            Klimatologi::create($validated);
            $message = 'Data berhasil ditambahkan.';
        }

        return redirect()->route('admin.dashboard')->with('success', $message);
    }

    private function buildDataJson(array $data): array
    {
        return [
            'tanggal' => Carbon::parse($data['tanggal'])->format('d-m-Y'),
            'tn' => (float) $data['TN'],
            'tx' => (float) $data['TX'],
            'tavg' => (float) $data['TAVG'],
            'rh_avg' => (float) $data['RH_AVG'],
            'rr' => (float) $data['RR'],
            'ss' => (float) $data['SS'],
        ];
    }

    public function generatePrediksi(PredictionGenerationService $service)
    {
        try {
            $total = $service->generate();

            return redirect()
                ->route('admin.dashboard')
                ->with('success', "Prediksi berhasil diperbarui. Total {$total} data prediksi disimpan.");
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Prediksi gagal diperbarui: ' . $e->getMessage());
        }
    }

    // Delete data
    public function delete($id)
    {
        $klimatologi = Klimatologi::findOrFail($id);
        $klimatologi->delete();
        
        return redirect()->route('admin.dashboard')->with('success', 'Data berhasil dihapus.');
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
