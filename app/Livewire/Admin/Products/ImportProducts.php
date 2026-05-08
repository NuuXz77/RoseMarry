<?php

namespace App\Livewire\Admin\Products;

use App\Models\Categories;
use App\Models\Divisions;
use App\Models\Products;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

#[Layout('components.layouts.app')]
#[Title('Import Data Produk')]
class ImportProducts extends Component
{
    use WithFileUploads;

    public $file;
    public $imageFiles = [];
    public array $previewData = [];
    public int $validCount = 0;
    public int $errorCount = 0;
    public int $importedCount = 0;
    public bool $isLoading = false;

    /** @var array Map of lowercase filename => temp path for uploaded images */
    public array $uploadedImageMap = [];

    protected $rules = [
        'file' => 'required|file|mimes:xlsx,xls|max:5120',
        'imageFiles.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    ];

    protected $messages = [
        'file.required'       => 'File Excel wajib dipilih',
        'file.mimes'          => 'File harus berformat .xlsx atau .xls',
        'file.max'            => 'Ukuran file maksimal 5MB',
        'imageFiles.*.image'  => 'File gambar harus berupa gambar',
        'imageFiles.*.mimes'  => 'Gambar harus berformat jpg, jpeg, png, atau webp',
        'imageFiles.*.max'    => 'Ukuran gambar maksimal 2MB per file',
    ];

    /**
     * Get validation reference data for Alpine.js client-side validation
     */
    private function getValidationRefs(): array
    {
        return [
            'categoryMap'      => Categories::where('type', 'product')->pluck('id', 'name')->toArray(),
            'divisionMap'      => Divisions::pluck('id', 'name')->toArray(),
            'existingNames'    => Products::pluck('name')->map(fn($v) => mb_strtolower(trim((string) $v)))->toArray(),
            'existingBarcodes' => Products::whereNotNull('barcode')->pluck('barcode')->map(fn($v) => mb_strtolower(trim((string) $v)))->toArray(),
            'uploadedImages'   => array_keys($this->uploadedImageMap),
        ];
    }

    public function downloadTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Template Products');

            $sheet->setCellValue('A1', 'Nama Produk');
            $sheet->setCellValue('B1', 'Barcode');
            $sheet->setCellValue('C1', 'Kategori');
            $sheet->setCellValue('D1', 'Divisi');
            $sheet->setCellValue('E1', 'Harga');
            $sheet->setCellValue('F1', 'Status');
            $sheet->setCellValue('G1', 'Gambar');

            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
            ];
            $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

            $sheet->setCellValue('A2', 'Roti Coklat');
            $sheet->setCellValue('B2', 'PRD0001');
            $sheet->setCellValue('C2', 'Snack');
            $sheet->setCellValue('D2', 'Pastry Bakery');
            $sheet->setCellValue('E2', '15000');
            $sheet->setCellValue('F2', 'active');
            $sheet->setCellValue('G2', 'roti_coklat.jpg');

            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $sheet->setCellValue('G3', '(Isi nama file gambar yang di-upload bersama Excel)');
            $noteStyle = [
                'font' => ['italic' => true, 'color' => ['rgb' => '888888'], 'size' => 9],
            ];
            $sheet->getStyle('G3')->applyFromArray($noteStyle);

            $writer = new Xlsx($spreadsheet);
            $fileName = 'template_import_products.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), 'import_products_');
            $writer->save($tempFile);

            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('ImportProducts downloadTemplate error: ' . $e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Gagal download template');
            return null;
        }
    }

    public function updatedFile(): void
    {
        $this->validateOnly('file');
        $this->isLoading = true;
        $this->reset(['previewData', 'importedCount']);
        $this->previewAllSheets();
    }

    public function updatedImageFiles(): void
    {
        $this->validate([
            'imageFiles.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $this->uploadedImageMap = [];
        foreach ($this->imageFiles as $img) {
            $originalName = mb_strtolower(trim($img->getClientOriginalName()));
            $this->uploadedImageMap[$originalName] = $img->getRealPath();
        }

        $count = count($this->uploadedImageMap);
        $this->dispatch('show-toast', type: 'success', message: "{$count} gambar berhasil di-upload");

        // Notify Alpine about updated image list
        $this->dispatch('images-updated', uploadedImages: array_keys($this->uploadedImageMap));
    }

    public function previewAllSheets(): void
    {
        try {
            if (!$this->file) {
                $this->dispatch('show-toast', type: 'error', message: 'Pilih file terlebih dahulu');
                $this->isLoading = false;
                return;
            }

            $path = $this->file->getRealPath();
            $spreadsheet = IOFactory::load($path);

            $refs = $this->getValidationRefs();
            $categories = $refs['categoryMap'];
            $divisions = $refs['divisionMap'];
            $existingNames = $refs['existingNames'];
            $existingBarcodes = $refs['existingBarcodes'];

            $this->previewData = [];

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $sheetName = $worksheet->getTitle();
                $rows = $worksheet->toArray();
                array_shift($rows);

                foreach ($rows as $rowIndex => $row) {
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    $name = isset($row[0]) ? trim((string) $row[0]) : '';
                    $barcode = isset($row[1]) ? trim((string) $row[1]) : '';
                    $categoryName = isset($row[2]) ? trim((string) $row[2]) : '';
                    $divisionName = isset($row[3]) ? trim((string) $row[3]) : '';
                    $priceRaw = isset($row[4]) ? trim((string) $row[4]) : '0';
                    $statusRaw = isset($row[5]) ? strtolower(trim((string) $row[5])) : 'active';
                    $imageName = isset($row[6]) ? trim((string) $row[6]) : '';
                    $price = is_numeric($priceRaw) ? (float) $priceRaw : null;

                    $rowData = [
                        'sheet_name'    => $sheetName,
                        'row_number'    => $rowIndex + 2,
                        'name'          => $name,
                        'barcode'       => $barcode,
                        'category_name' => $categoryName,
                        'division_name' => $divisionName,
                        'price'         => $price,
                        'status'        => in_array($statusRaw, ['active', 'inactive'], true) ? $statusRaw : 'active',
                        'image_name'    => $imageName,
                        'category_id'   => null,
                        'division_id'   => null,
                        'errors'        => [],
                        'has_error'     => false,
                    ];

                    // Server-side initial validation
                    if ($rowData['name'] === '') {
                        $rowData['errors'][] = 'Nama produk wajib diisi';
                    } elseif (in_array(mb_strtolower($rowData['name']), $existingNames, true)) {
                        $rowData['errors'][] = 'Nama produk sudah terdaftar';
                    }

                    if ($rowData['barcode'] !== '' && in_array(mb_strtolower($rowData['barcode']), $existingBarcodes, true)) {
                        $rowData['errors'][] = 'Barcode sudah terdaftar';
                    }

                    if ($rowData['category_name'] === '') {
                        $rowData['errors'][] = 'Kategori wajib diisi';
                    } elseif (!isset($categories[$rowData['category_name']])) {
                        $rowData['errors'][] = 'Kategori produk tidak ditemukan';
                    } else {
                        $rowData['category_id'] = $categories[$rowData['category_name']];
                    }

                    if ($rowData['division_name'] === '') {
                        $rowData['errors'][] = 'Divisi wajib diisi';
                    } elseif (!isset($divisions[$rowData['division_name']])) {
                        $rowData['errors'][] = 'Divisi tidak ditemukan';
                    } else {
                        $rowData['division_id'] = $divisions[$rowData['division_name']];
                    }

                    if ($rowData['price'] === null || $rowData['price'] < 0) {
                        $rowData['errors'][] = 'Harga harus angka >= 0';
                    }

                    if ($rowData['image_name'] !== '') {
                        $imgKey = mb_strtolower($rowData['image_name']);
                        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                        $ext = pathinfo($imgKey, PATHINFO_EXTENSION);
                        if (!in_array($ext, $allowedExtensions, true)) {
                            $rowData['errors'][] = 'Format gambar harus jpg, jpeg, png, atau webp';
                        } elseif (!empty($this->uploadedImageMap) && !isset($this->uploadedImageMap[$imgKey])) {
                            $rowData['errors'][] = 'File gambar "' . $rowData['image_name'] . '" tidak ditemukan di upload';
                        }
                    }

                    $rowData['has_error'] = !empty($rowData['errors']);
                    $this->previewData[] = $rowData;
                }
            }

            if (count($this->previewData) === 0) {
                $this->dispatch('show-toast', type: 'warning', message: 'Tidak ada data ditemukan di file ini');
            } else {
                $errorCount = collect($this->previewData)->where('has_error', true)->count();
                $msg = 'File berhasil diupload: ' . count($this->previewData) . ' data';
                if ($errorCount > 0) {
                    $msg .= " ({$errorCount} error)";
                }
                $this->dispatch('show-toast', type: 'success', message: $msg);
            }

            // Dispatch to Alpine with rows + validation refs
            $this->dispatch('preview-data-loaded',
                rows: $this->previewData,
                refs: $refs,
            );
        } catch (\Exception $e) {
            Log::error('ImportProducts preview error: ' . $e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Error membaca file: ' . $e->getMessage());
            $this->reset(['previewData']);
        } finally {
            $this->isLoading = false;
        }
    }

    /**
     * Receive edited rows from Alpine.js and import to database
     */
    public function importDataFromAlpine(array $rows): void
    {
        if (empty($rows)) {
            $this->dispatch('show-toast', type: 'error', message: 'Tidak ada data untuk diimport');
            return;
        }

        $this->isLoading = true;

        try {
            DB::beginTransaction();

            // Fresh validation refs
            $categories = Categories::where('type', 'product')->pluck('id', 'name')->toArray();
            $divisions = Divisions::pluck('id', 'name')->toArray();
            $existingNames = Products::pluck('name')->map(fn($v) => mb_strtolower(trim((string) $v)))->toArray();
            $existingBarcodes = Products::whereNotNull('barcode')->pluck('barcode')->map(fn($v) => mb_strtolower(trim((string) $v)))->toArray();

            $imported = 0;
            $errors = [];

            foreach ($rows as $row) {
                $name = trim((string) ($row['name'] ?? ''));
                $barcode = trim((string) ($row['barcode'] ?? ''));
                $categoryName = trim((string) ($row['category_name'] ?? ''));
                $divisionName = trim((string) ($row['division_name'] ?? ''));
                $priceRaw = $row['price'] ?? null;
                $status = ($row['status'] ?? 'active') === 'active';
                $imageName = trim((string) ($row['image_name'] ?? ''));

                $nameKey = mb_strtolower($name);
                $barcodeKey = mb_strtolower($barcode);

                // Server-side validation
                if ($name === '' || !isset($categories[$categoryName]) || !isset($divisions[$divisionName])) {
                    continue;
                }
                if ($priceRaw === null || !is_numeric($priceRaw) || (float) $priceRaw < 0) {
                    continue;
                }
                if (in_array($nameKey, $existingNames, true)) {
                    continue;
                }
                if ($barcodeKey !== '' && in_array($barcodeKey, $existingBarcodes, true)) {
                    continue;
                }

                try {
                    // Handle image upload
                    $fotoPath = null;
                    if ($imageName !== '' && !empty($this->imageFiles)) {
                        $imgKey = mb_strtolower($imageName);
                        foreach ($this->imageFiles as $img) {
                            if (mb_strtolower(trim($img->getClientOriginalName())) === $imgKey) {
                                $fotoPath = $img->store('products', 'public');
                                break;
                            }
                        }
                    }

                    $product = Products::create([
                        'name'         => $name,
                        'barcode'      => $barcode !== '' ? $barcode : null,
                        'foto_product' => $fotoPath,
                        'category_id'  => $categories[$categoryName],
                        'division_id'  => $divisions[$divisionName],
                        'price'        => (float) $priceRaw,
                        'status'       => $status,
                    ]);

                    $product->stock()->create(['qty_available' => 0]);

                    $imported++;
                    $existingNames[] = $nameKey;
                    if ($barcodeKey !== '') {
                        $existingBarcodes[] = $barcodeKey;
                    }
                } catch (\Exception $e) {
                    $sheetName = $row['sheet_name'] ?? '?';
                    $rowNum = $row['row_number'] ?? '?';
                    $errors[] = "Sheet {$sheetName}, Baris {$rowNum}: " . $e->getMessage();
                }
            }

            if ($imported > 0) {
                DB::commit();
                $this->importedCount = $imported;

                $msg = "Berhasil import {$imported} data produk!";
                if (!empty($errors)) {
                    $msg .= ' (' . count($errors) . ' gagal)';
                }
                $this->dispatch('show-toast', type: 'success', message: $msg);
                $this->reset(['file', 'imageFiles', 'previewData', 'uploadedImageMap']);
                $this->dispatch('redirect-after-import');
            } else {
                DB::rollBack();
                $this->dispatch('show-toast', type: 'error', message: 'Tidak ada data yang berhasil diimport');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ImportProducts importData error: ' . $e->getMessage());
            $this->dispatch('show-toast', type: 'error', message: 'Error: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    public function clearPreview(): void
    {
        $this->reset(['file', 'imageFiles', 'previewData', 'importedCount', 'uploadedImageMap']);
        $this->dispatch('show-toast', type: 'info', message: 'Data dibersihkan');
    }

    public function render()
    {
        $refs = $this->getValidationRefs();

        return view('livewire.admin.products.import-products', [
            'categoryMap'      => $refs['categoryMap'],
            'divisionMap'      => $refs['divisionMap'],
            'existingNames'    => $refs['existingNames'],
            'existingBarcodes' => $refs['existingBarcodes'],
            'uploadedImages'   => $refs['uploadedImages'],
        ]);
    }
}
