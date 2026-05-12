<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ExcelImportService;

class ExcelImportController extends Controller
{
    public function __construct(private ExcelImportService $importer) {}

    public function store(Request $request)
    {
        $request->validate([
            'file' => [
                'required', 'file',
                'mimes:xlsx,xls',
                'max:10240', // 10MB
            ],
        ], [
            'file.required' => 'File Excel wajib diunggah.',
            'file.mimes'    => 'File harus berformat .xlsx atau .xls.',
            'file.max'      => 'Ukuran file maksimal 10MB.',
        ]);

        $path   = $request->file('file')->store('imports/temp');
        $result = $this->importer->import(storage_path("app/{$path}"), auth()->id());

        // Hapus file temp setelah import
        \Storage::delete($path);

        // Log hasil import
        \Log::info('Excel Import Result', $result);

        if ($result['success']) {
            return back()->with('import_success', $result);
        }

        return back()->with('import_error', $result)->withErrors(['file' => $result['message']]);
    }
}