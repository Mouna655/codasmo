<?php
namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Models\ThirdPartyCoal;
use Illuminate\Support\Facades\Auth;

class ThirdPartyCoalImport implements WithMultipleSheets
{
    public int    $imported  = 0;
    public array  $errors    = [];
    public string $batch;
    public int    $year;

    public function __construct(int $year)
    {
        $this->year  = $year;
        $this->batch = (string) Str::uuid();
    }

    public function sheets(): array
    {
        return [
            '3rd Party' => new ThirdPartySheetImport($this->year, $this->batch, $this),
        ];
    }
}

/* ══════════════════════════════════════════════════════════
   Sheet importer
══════════════════════════════════════════════════════════ */
class ThirdPartySheetImport implements ToCollection
{
    private int    $year;
    private string $batch;
    private ThirdPartyCoalImport $parent;

    // Kualitas valid
    private array $validQualities = ['ICI 1','ICI 2','ICI 3','ICI 4','ICI 5'];

    public function __construct(int $year, string $batch, ThirdPartyCoalImport $parent)
    {
        $this->year   = $year;
        $this->batch  = $batch;
        $this->parent = $parent;
    }

    public function collection(Collection $rows)
    {
        // Format Excel:
        // Row 1 = kosong (skip)
        // Row 2 = Header: [null, Bulan, Kualitas, Shipper, Plan, Actual, ...]
        // Row 3+ = Data
        //
        // Index: [0]=null [1]=Bulan [2]=Kualitas [3]=Shipper [4]=Plan [5]=Actual

        $uploaderId = Auth::id();

        // Hapus data lama untuk tahun ini sebelum insert yang baru
        ThirdPartyCoal::where('year', $this->year)->delete();

        foreach ($rows as $i => $row) {
            // Skip 2 baris pertama (kosong + header)
            if ($i < 2) continue;

            $bulan   = $row[1] ?? null;
            $quality = trim((string) ($row[2] ?? ''));
            $shipper = trim((string) ($row[3] ?? ''));
            $plan    = is_numeric($row[4] ?? null) ? (float) $row[4] : 0;
            $actual  = is_numeric($row[5] ?? null) ? (float) $row[5] : 0;

            // Skip baris kosong
            if (empty($quality) || empty($shipper)) continue;

            // Validasi quality
            if (!in_array($quality, $this->validQualities)) {
                $this->parent->errors[] = "Baris " . ($i + 1) . ": Quality '$quality' tidak dikenal.";
                continue;
            }

            // Parse bulan dari datetime atau string
            $month = null;
            if ($bulan instanceof \DateTime || $bulan instanceof \Carbon\Carbon) {
                $month = (int) date('n', strtotime($bulan));
            } elseif (is_numeric($bulan)) {
                // Excel date serial number
                $month = (int) \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($bulan))->format('n');
            }

            if (!$month || $month < 1 || $month > 12) {
                $this->parent->errors[] = "Baris " . ($i + 1) . ": Format bulan tidak valid.";
                continue;
            }

            ThirdPartyCoal::create([
                'year'        => $this->year,
                'month'       => $month,
                'quality'     => $quality,
                'shipper'     => $shipper,
                'plan'        => $plan,
                'actual'      => $actual,
                'upload_batch'=> $this->batch,
                'uploaded_by' => $uploaderId,
            ]);

            $this->parent->imported++;
        }
    }
}