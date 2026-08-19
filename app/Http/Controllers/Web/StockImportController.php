<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Enums\UserRole;
use App\Models\Branch;
use App\Services\StockImportService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class StockImportController extends Controller
{
    protected $importService;

    public function __construct(StockImportService $importService)
    {
        $this->importService = $importService;
    }

    public function index()
    {
        return view('inventory.import.upload');
    }

    public function template()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\StockTemplateExport, 'plantilla_importacion.xlsx');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'nullable|file|mimes:csv,xlsx,txt|max:10240',
            'raw_text' => 'nullable|string',
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('temp_imports');
            $data = $this->importService::class; // dummy reference for clarity
            $parsed = $this->importService->parseFile($path);
        } elseif ($request->filled('raw_text')) {
            $parsed = $this->importService->parseRawText($request->raw_text);
            $fileName = 'raw_' . time() . '.txt';
            Storage::put('temp_imports/' . $fileName, $request->raw_text);
            $path = 'temp_imports/' . $fileName;
        } else {
            return back()->with('error', 'Por favor, sube un archivo o pega el contenido.');
        }

        if (empty($parsed['headers'])) {
            return back()->with('error', 'No se pudieron detectar encabezados en el archivo.');
        }

        $user = auth()->user();
        $branches = $user && $user->role === UserRole::SUPER_ADMIN
            ? Branch::all()
            : Branch::where('organization_id', $user?->organization_id)->get();

        $dbFields = $this->importService->getDbFields();

        return view('inventory.import.map', [
            'filePath' => $path,
            'headers' => $parsed['headers'],
            'normalizedHeaders' => $parsed['normalized_headers'],
            'sample' => $parsed['sample'],
            'dbFields' => $dbFields,
            'branches' => $branches
        ]);
    }

    public function preview(Request $request)
    {
        $user = auth()->user();
        $organizationId = $user?->organization_id;

        $branchExistsRule = Rule::exists('branches', 'id');
        if ($user && $user->role !== UserRole::SUPER_ADMIN) {
            $branchExistsRule = $branchExistsRule->where('organization_id', $organizationId);
        }

        $request->validate([
            'file_path' => 'required|string',
            'mapping' => 'required|array',
            'branch_id' => ['nullable', $branchExistsRule],
        ]);

        $preview = $this->importService->preparePreview($request->file_path, $request->mapping);

        return view('inventory.import.preview', [
            'filePath' => $request->file_path,
            'mapping' => $request->mapping,
            'branchId' => $request->branch_id,
            'preview' => $preview['preview'],
            'summary' => $preview['summary']
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $organizationId = $user?->organization_id;

        $branchExistsRule = Rule::exists('branches', 'id');
        if ($user && $user->role !== UserRole::SUPER_ADMIN) {
            $branchExistsRule = $branchExistsRule->where('organization_id', $organizationId);
        }

        $request->validate([
            'file_path' => 'required|string',
            'mapping' => 'required|array',
            'branch_id' => ['nullable', $branchExistsRule],
        ]);

        // Check if there are many rows to decide if we should queue
        // For now, let's do it directly and we can add queue logic if needed.
        // The service already handles batching.
        
        $result = $this->importService->performImport(
            $request->file_path,
            $request->mapping,
            $organizationId,
            $request->branch_id
        );

        return view('inventory.import.result', ['result' => $result]);
    }
}
