<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\PoaSnapshot;
use App\Models\PoaRecord;

class PoaController extends Controller
{
    // Urutan bulan tampil di chart
    private const MONTHS = [
        1=>'January',2=>'February',3=>'March',4=>'April',
        5=>'May',6=>'June',7=>'July',8=>'August',
        9=>'September',10=>'October',11=>'November',12=>'December',
    ];

    // Company yang ditampilkan di dashboard (sesuai gambar)
    private const COMPANIES = ['IMM', 'TCM', 'BEK', 'GPK', 'TIS'];

    /**
     * Halaman dashboard POA — PUBLIK
     */
    public function index(Request $request)
    {
        $availableDates = PoaSnapshot::availableDates();

        // Default: snapshot terbaru
        $selectedDate = $request->date
            ?? ($availableDates[0]['date'] ?? today()->toDateString());

        $selectedYear = $request->year
            ?? ($availableDates[0]['year'] ?? now()->year);

        $data = $this->buildChartData($selectedDate, (int) $selectedYear);

        return view('public.poa', compact(
            'data', 'availableDates', 'selectedDate', 'selectedYear'
        ));
    }

    /**
     * Halaman dashboard POA — ADMIN (sama konten, layout berbeda)
     */
    public function adminIndex(Request $request)
    {
        $availableDates = PoaSnapshot::availableDates();

        $selectedDate = $request->date
            ?? ($availableDates[0]['date'] ?? today()->toDateString());

        $selectedYear = $request->year
            ?? ($availableDates[0]['year'] ?? now()->year);

        $data = $this->buildChartData($selectedDate, (int) $selectedYear);

        // Info tambahan untuk admin
        $snapshotInfo = PoaSnapshot::latestBefore($selectedDate, (int) $selectedYear);

        return view('dashboard.poa', compact(
            'data', 'availableDates', 'selectedDate', 'selectedYear', 'snapshotInfo'
        ));
    }

    /**
     * API endpoint — untuk AJAX refresh & export
     */
    public function apiData(Request $request)
    {
        $date = $request->date ?? today()->toDateString();
        $year = (int) ($request->year ?? now()->year);

        return response()->json($this->buildChartData($date, $year));
    }

    /**
     * Inti — bangun data chart dari snapshot terbaru sebelum tanggal tertentu.
     */
    public function buildChartData(string $date, int $year): array
    {
        // Ambil snapshot terbaru sebelum/pada tanggal yang dipilih
        $snapshot = PoaSnapshot::latestBefore($date, $year);

        if (!$snapshot) {
            return [
                'snapshot'        => null,
                'date'            => $date,
                'year'            => $year,
                'companies'       => [],
                'months'          => array_values(self::MONTHS),
                'has_data'        => false,
            ];
        }

        // Ambil semua record dari snapshot ini
        $records = PoaRecord::where('snapshot_id', $snapshot->id)
            ->where('year', $year)
            ->get();

        // Bangun data per company
        $companiesData = [];

        foreach (self::COMPANIES as $company) {
            $companyRecords = $records->where('company', $company);

            $monthlyData = [];
            foreach (self::MONTHS as $num => $name) {
                $monthRecords = $companyRecords->where('month_number', $num);

                $monthlyData[] = [
                    'month_num'  => $num,
                    'month_name' => $name,
                    'previous'   => round($monthRecords->sum('previous'), 1),
                    'outlook'    => round($monthRecords->sum('outlook'), 1),
                    'actual'     => round($monthRecords->sum('actual'), 1),
                    // Tandai sebagai "masih prediksi" jika actual = 0 tapi previous > 0
                    'is_prediction' => $monthRecords->sum('actual') == 0
                                    && $monthRecords->sum('outlook') > 0,
                    // Flag manual untuk menandai data yang masih provisional
                    'is_provisional' => $monthRecords->max('is_provisional') ? true : false,
                ];
            }

            $companiesData[] = [
                'company'      => $company,
                'monthly_data' => $monthlyData,
                // Max value untuk skala Y-axis tiap company
                'y_max'        => $this->calcYMax($monthlyData),
            ];
        }

        return [
            'snapshot' => [
                'id'          => $snapshot->id,
                'upload_date' => $snapshot->upload_date->format('d M Y'),
                'data_year'   => $snapshot->data_year,
                'uploaded_by' => $snapshot->uploader?->name ?? 'System',
                'total_rows'  => $snapshot->total_rows,
            ],
            'date'        => $date,
            'year'        => $year,
            'companies'   => $companiesData,
            'months'      => array_values(self::MONTHS),
            'has_data'    => true,
        ];
    }

    /**
     * Hitung nilai maksimum Y-axis dengan padding 20%
     */
    private function calcYMax(array $monthlyData): int
    {
        $max = 0;
        foreach ($monthlyData as $m) {
            $max = max($max, $m['previous'], $m['outlook'], $m['actual']);
        }
        if ($max === 0) return 100;
        // Pembulatan ke atas ke ratusan terdekat + 20% padding
        return (int) (ceil(($max * 1.2) / 100) * 100);
    }

    /**
     * Update flag is_provisional untuk bulan tertentu
     */
    public function updateProvisional(Request $request)
    {
        // Hanya admin yang bisa edit
        if (!auth()->user()?->isSuperAdmin() && !auth()->user()?->isOperator()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'snapshot_id' => 'required|integer',
            'company'     => 'required|string',
            'month'       => 'required|integer|between:1,12',
            'year'        => 'required|integer',
            'is_provisional' => 'required|boolean',
        ]);

        // Update semua records untuk kombinasi snapshot/company/month/year ini
        $updated = PoaRecord::where('snapshot_id', $request->snapshot_id)
            ->where('company', $request->company)
            ->where('month_number', $request->month)
            ->where('year', $request->year)
            ->update(['is_provisional' => $request->is_provisional]);

        return response()->json([
            'success' => true,
            'updated' => $updated,
        ]);
    }
}