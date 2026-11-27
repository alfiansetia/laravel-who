<?php

namespace App\Http\Controllers;

use App\Models\Kargan;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpWord\TemplateProcessor;
use ZipArchive;

class KarganController extends Controller
{
    public function __construct()
    {
        $this->middleware('env_auth')->only(['destroy', 'destroy_batch']);
    }

    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $perPage = min((int) $request->input('per_page', 25), 200);
            $page = max((int) $request->input('page', 1), 1);

            $query = Kargan::query()->with('product');

            if ($request->filled('search')) {
                $keyword = $request->search;
                $query->where(function ($q) use ($keyword) {
                    $q->where('number', 'like', "%{$keyword}%")
                        ->orWhere('sn', 'like', "%{$keyword}%")
                        ->orWhereHas('product', function ($q2) use ($keyword) {
                            $q2->where('code', 'like', "%{$keyword}%")
                                ->orWhere('name', 'like', "%{$keyword}%");
                        });
                });
            }

            $total = (clone $query)->count();

            $data = $query->orderBy('id', 'desc')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

            return response()->json([
                'data' => $data,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => (int) ceil($total / $perPage),
            ]);
        }

        return Inertia::render('Kargan/Index', [
            'title' => 'List Kargan',
            'filters' => $request->only(['search', 'page']),
            'products' => Product::orderBy('code')->get(['id', 'code', 'name']),
            'picOptions' => ['Karim Ash Shidik', 'Sofyan Saputra'],
            'defaultDate' => now()->format('Y-m-d'),
        ]);
    }

    public function create(): Response
    {
        $last = Kargan::latest()->first();

        return Inertia::render('Kargan/Create', [
            'title' => 'Create Kargan',
            'products' => Product::orderBy('code')->get(['id', 'code', 'name']),
            'newNumber' => Kargan::generateNumber(),
            'lastNumber' => $last ? $last->number : '-',
            'defaultDate' => now()->format('Y-m-d'),
            'picOptions' => ['Karim Ash Shidik', 'Sofyan Saputra'],
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'product_id' => 'required|exists:products,id',
            'date' => 'date_format:Y-m-d',
            'number' => 'required|string|max:200',
            'sn' => 'nullable|string|max:200',
            'pic' => 'required|string|max:200',
        ]);
        $masa = Kargan::getDefaultMasaAttribute();
        $kargan = Kargan::create([
            'product_id' => $request->product_id,
            'date' => $request->date,
            'number' => $request->number,
            'sn' => $request->sn,
            'pic' => $request->pic,
            'masa' => $masa,
        ]);

        if ($request->wantsJson()) {
            return $this->sendResponse($kargan, 'Created!');
        }

        return redirect()->back()->with('success', 'Created!');
    }

    public function show(Request $request, Kargan $kargan)
    {
        if ($request->wantsJson()) {
            return $this->sendResponse($kargan->load('product'), 'Success!');
        }

        return redirect()->back();
    }

    public function edit(Kargan $kargan): Response
    {
        $last = Kargan::whereNot('id', $kargan->id)->latest()->first();

        return Inertia::render('Kargan/Edit', [
            'title' => 'Edit Kargan',
            'record' => $kargan->load('product:id,code,name'),
            'products' => Product::orderBy('code')->get(['id', 'code', 'name']),
            'lastNumber' => $last ? $last->number : '-',
            'picOptions' => ['Karim Ash Shidik', 'Sofyan Saputra'],
        ]);
    }

    public function update(Request $request, Kargan $kargan)
    {
        $this->validate($request, [
            'product_id' => 'required|exists:products,id',
            'date' => 'date_format:Y-m-d',
            'number' => 'required|string|max:200',
            'sn' => 'nullable|string|max:200',
            'pic' => 'required|string|max:200',
        ]);
        $masa = Kargan::getDefaultMasaAttribute();
        $kargan->update([
            'product_id' => $request->product_id,
            'date' => $request->date,
            'number' => $request->number,
            'sn' => $request->sn,
            'pic' => $request->pic,
            'masa' => $masa,
        ]);

        if ($request->wantsJson()) {
            return $this->sendResponse($kargan, 'Updated!');
        }

        return redirect()->back()->with('success', 'Updated!');
    }

    public function destroy(Request $request, Kargan $kargan)
    {
        $kargan->delete();

        if ($request->wantsJson()) {
            return $this->sendResponse($kargan, 'Deleted!');
        }

        return redirect()->back()->with('success', 'Deleted!');
    }

    public function destroy_batch(Request $request)
    {
        $this->validate($request, [
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:kargans,id',
        ]);
        $deleted = Kargan::whereIn('id', $request->ids)->delete();

        if ($request->wantsJson()) {
            return $this->sendResponse([
                'deleted_count' => $deleted,
            ], 'Kargan deleted successfully.');
        }

        return redirect()->back()->with('success', 'Kargan deleted successfully.');
    }

    public function duplicate(Request $request, Kargan $kargan)
    {
        $data = $kargan->replicate();
        $data->number = Kargan::generateNumber();
        $data->save();

        if ($request->wantsJson()) {
            return $this->sendResponse($data, 'Success Duplicate!');
        }

        return redirect()->back()->with('success', 'Success Duplicate!');
    }

    public function import(Request $request)
    {
        $this->validate($request, [
            'items' => 'required|array|min:1|max:200',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.sn' => 'nullable|string|max:200',
            'items.*.date' => 'nullable|date_format:Y-m-d',
            'items.*.pic' => 'nullable|string|max:200',
            'source' => 'nullable|string|max:50',
            'source_no' => 'nullable|string|max:100',
        ]);

        $defaultDate = now()->format('Y-m-d');
        $defaultPic = Kargan::getDefaultPicAttribute();
        $masa = Kargan::getDefaultMasaAttribute();
        $items = $request->input('items');
        $baseDate = $items[0]['date'] ?? $defaultDate;

        $created = DB::transaction(function () use ($items, $baseDate, $defaultDate, $defaultPic, $masa) {
            $numbers = Kargan::generateNumbersForDate($baseDate, count($items));
            $rows = [];
            foreach ($items as $i => $item) {
                $rows[] = Kargan::create([
                    'product_id' => $item['product_id'],
                    'date' => $item['date'] ?? $defaultDate,
                    'number' => $numbers[$i],
                    'sn' => $item['sn'] ?? null,
                    'masa' => $masa,
                    'pic' => $item['pic'] ?? $defaultPic,
                ]);
            }

            return $rows;
        });

        if ($this->isApi($request)) {
            return $this->sendResponse($created, count($created).' kargan dibuat.');
        }

        return redirect()->back()->with('success', count($created).' kargan dibuat.');
    }

    public function nextNumbers(Request $request)
    {
        $data = $request->validate([
            'count' => 'required|integer|min:1|max:200',
            'date' => 'nullable|date_format:Y-m-d',
        ]);

        $numbers = Kargan::generateNumbersForDate($data['date'] ?? now()->format('Y-m-d'), (int) $data['count']);

        return $this->sendResponse($numbers, 'OK');
    }

    public function download(Kargan $kargan)
    {
        $kargan->loadMissing('product');
        $path = $this->buildDocxPath($kargan);

        return response()->download($path)->deleteFileAfterSend();
    }

    public function downloadZip(Request $request)
    {
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        $ids = collect((array) $ids)->map(fn ($v) => (int) $v)->filter(fn ($v) => $v > 0)->unique()->take(100)->values()->all();

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Pilih minimal 1 kargan.');
        }

        $kargans = Kargan::with('product:id,code,name')->whereIn('id', $ids)->get()->keyBy('id');
        $ordered = collect($ids)->map(fn ($id) => $kargans->get($id))->filter();
        if ($ordered->isEmpty()) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $zipName = 'kargan-batch-'.now()->format('Ymd-His').'.zip';
        $zipPath = storage_path('app/'.$zipName);
        $files = [];

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('error', 'Gagal membuat file ZIP.');
        }

        foreach ($ordered as $kargan) {
            $path = $this->buildDocxPath($kargan);
            $files[] = $path;
            $zip->addFile($path, basename($path));
        }
        $zip->close();

        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        return response()->download($zipPath)->deleteFileAfterSend();
    }

    public function downloadMerged(Request $request)
    {
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        $ids = collect((array) $ids)->map(fn ($v) => (int) $v)->filter(fn ($v) => $v > 0)->unique()->take(100)->values()->all();

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Pilih minimal 1 kargan.');
        }

        $kargans = Kargan::with('product:id,code,name')->whereIn('id', $ids)->get()->keyBy('id');
        $ordered = collect($ids)->map(fn ($id) => $kargans->get($id))->filter()->values();
        if ($ordered->isEmpty()) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $files = [];
        foreach ($ordered as $kargan) {
            $files[] = $this->buildDocxPath($kargan);
        }

        $mergedName = 'kargan-gabungan-'.now()->format('Ymd-His').'.docx';
        $mergedPath = storage_path('app/'.$mergedName);
        $this->mergeDocxBodies($files, $mergedPath);

        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }

        return response()->download($mergedPath)->deleteFileAfterSend();
    }

    /**
     * Gabung N docx satu template jadi 1 file: body digabung dengan
     * page break antar dokumen. Aman karena semua dari template sama
     * (styles/header/footer identik) — cukup merge word/document.xml.
     */
    private function mergeDocxBodies(array $files, string $output): void
    {
        $wordNs = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
        copy($files[0], $output);

        foreach (array_slice($files, 1) as $file) {
            $this->appendDocxBody($output, $file, $wordNs);
        }
    }

    private function appendDocxBody(string $basePath, string $appendPath, string $wordNs): void
    {
        $zipBase = new ZipArchive;
        $zipBase->open($basePath);
        $base = new \DOMDocument;
        $base->loadXML($zipBase->getFromName('word/document.xml'));
        $xp = new \DOMXPath($base);
        $xp->registerNamespace('w', $wordNs);
        $baseBody = $xp->query('/w:document/w:body')->item(0);
        $baseSect = $xp->query('w:sectPr', $baseBody)->item(0);

        $zipApp = new ZipArchive;
        $zipApp->open($appendPath);
        $app = new \DOMDocument;
        $app->loadXML($zipApp->getFromName('word/document.xml'));
        $zipApp->close();
        $axp = new \DOMXPath($app);
        $axp->registerNamespace('w', $wordNs);
        $appBody = $axp->query('/w:document/w:body')->item(0);

        // Page break sebelum isi dokumen berikutnya.
        $br = $base->createElementNS($wordNs, 'w:p');
        $r = $base->createElementNS($wordNs, 'w:r');
        $brEl = $base->createElementNS($wordNs, 'w:br');
        $brEl->setAttributeNS($wordNs, 'type', 'page');
        $r->appendChild($brEl);
        $br->appendChild($r);
        if ($baseSect) {
            $baseBody->insertBefore($br, $baseSect);
        } else {
            $baseBody->appendChild($br);
        }

        foreach (iterator_to_array($appBody->childNodes) as $node) {
            if ($node->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }
            if ($node->localName === 'sectPr') {
                continue;
            }
            $imported = $base->importNode($node, true);
            if ($baseSect) {
                $baseBody->insertBefore($imported, $baseSect);
            } else {
                $baseBody->appendChild($imported);
            }
        }

        $zipBase->addFromString('word/document.xml', $base->saveXML());
        $zipBase->close();
    }

    private function buildDocxPath(Kargan $kargan): string
    {
        $file = public_path('master/kargan.docx');
        Carbon::setLocale('id');
        $date = Carbon::parse($kargan->date)->translatedFormat('d F Y');
        $template = new TemplateProcessor($file);
        $template->setValue('prod_name', htmlspecialchars($kargan->product->name));
        $template->setValue('prod_code', htmlspecialchars($kargan->product->code));
        $template->setValue('date', htmlspecialchars($date));
        $template->setValue('number', htmlspecialchars($kargan->number));
        $template->setValue('sn', htmlspecialchars($kargan->sn));
        $template->setValue('masa', htmlspecialchars($kargan->masa));
        $template->setValue('pic', htmlspecialchars($kargan->pic));
        $name = Kargan::generateNameFile($kargan->number);
        $path = storage_path('app/'.$name.'.docx');
        $template->saveAs($path);

        return $path;
    }
}
