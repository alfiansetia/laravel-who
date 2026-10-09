<?php

namespace App\Http\Controllers;

use App\Models\Bast;
use App\Models\DetailBast;
use App\Models\Kargan;
use App\Models\Product;
use App\Services\DoServices;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;
use PhpOffice\PhpWord\TemplateProcessor;
use ZipArchive;

class BastController extends Controller
{
    public function __construct()
    {
        $this->middleware('env_auth')->only(['destroy', 'destroy_batch']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            $perPage = min((int) $request->input('per_page', 25), 200);
            $page = max((int) $request->input('page', 1), 1);

            $query = Bast::query();

            if ($request->filled('search')) {
                $keyword = $request->search;
                $query->where(function ($q) use ($keyword) {
                    $q->where('do', 'like', "%{$keyword}%")
                        ->orWhere('name', 'like', "%{$keyword}%")
                        ->orWhere('city', 'like', "%{$keyword}%")
                        ->orWhere('address', 'like', "%{$keyword}%");
                });
            }

            $total = (clone $query)->count();

            $data = $query->orderBy('id', 'desc')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get(['id', 'do', 'name', 'city', 'address', 'created_at']);

            return response()->json([
                'data' => $data,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => (int) ceil($total / $perPage),
            ]);
        }

        return Inertia::render('Bast/Index', [
            'title' => 'List BAST',
            'filters' => $request->only(['search', 'page']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $products = Product::query()->select('id', 'code', 'name')->orderBy('code')->get();

        return Inertia::render('Bast/Create', [
            'title' => 'Create BAST',
            'products' => $products,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(Bast $bast)
    {
        $products = Product::query()->select('id', 'code', 'name')->orderBy('code')->get();
        $data = $bast->load('details.product');

        return Inertia::render('Bast/Edit', [
            'title' => 'Edit BAST',
            'bast' => $data,
            'products' => $products,
        ]);
    }

    public function show(Request $request, Bast $bast)
    {
        $detail = $bast->load('details.product');
        foreach ($detail->details as $key => $item) {
            $item->order = $key;
            $item->save();
        }

        if ($request->wantsJson()) {
            return $this->sendResponse($detail, 'Success!');
        }

        return redirect()->route('basts.edit', $bast);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:250',
            'address' => 'required|max:250',
            'city' => 'required|max:250',
            'do' => 'required|max:250',
        ]);
        $bast = Bast::create([
            'name' => $request->name,
            'address' => $request->address,
            'city' => $request->city,
            'do' => $request->do,
        ]);

        if ($request->wantsJson()) {
            return $this->sendResponse($bast, 'Created!');
        }

        return redirect()->route('basts.index')->with('success', 'Created!');
    }

    public function update(Request $request, Bast $bast)
    {
        $this->validate($request, [
            'name' => 'required|max:250',
            'address' => 'required|max:250',
            'city' => 'required|max:250',
            'do' => 'required|max:250',
        ]);
        $bast->update([
            'name' => $request->name,
            'address' => $request->address,
            'city' => $request->city,
            'do' => $request->do,
        ]);

        if ($request->wantsJson()) {
            return $this->sendResponse($bast, 'Updated!');
        }

        return redirect()->route('basts.index')->with('success', 'Updated!');
    }

    public function destroy(Request $request, Bast $bast)
    {
        $bast->delete();

        if ($request->wantsJson()) {
            return $this->sendResponse($bast, 'Deleted!');
        }

        return redirect()->route('basts.index')->with('success', 'Deleted!');
    }

    public function destroy_batch(Request $request)
    {
        $this->validate($request, [
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:basts,id',
        ]);
        $deleted = Bast::whereIn('id', $request->ids)->delete();

        if ($request->wantsJson()) {
            return $this->sendResponse([
                'deleted_count' => $deleted,
            ], 'Bast deleted successfully.');
        }

        return redirect()->route('basts.index')->with('success', 'Bast deleted successfully.');
    }

    public function sync(Request $request, Bast $bast)
    {
        $id = 0;
        $do = $bast->do;
        $json = DoServices::getAll($do);
        if (count($json['records'] ?? []) > 0) {
            $id = intval($json['records'][0]['id']);
        }
        $detail = DoServices::detail($id);
        $pd_jd = [];
        foreach (($detail['move_ids_detail'] ?? []) as $item) {
            $lot = collect(($detail['move_line_detail'] ?? []))->filter(function ($value) use ($item) {
                if (isset($item['product_id'][0], $value['product_id'][0])) {
                    return $item['product_id'][0] === $value['product_id'][0];
                }
            });

            if ($lot->count() <= 10) {
                $values = $lot->map(function ($item) {
                    $lot = $item['lot_id'][1] ?? '';
                    $ed = $item['expired_date_do'] ?? '';
                    if ($lot && $ed) {
                        $ed = odoo_datetime($ed, 'd/m/Y');

                        return $lot.' Ed. '.$ed;
                    } elseif ($lot) {
                        return $lot;
                    }
                })->implode(', ');
            } else {
                $values = '';
            }

            preg_match('/\[(.*?)\]/', $item['product_id'][1], $matches);
            if (isset($matches[1])) {
                $pro = Product::query()->where('code', $matches[1])->first();
                if ($pro) {
                    array_push($pd_jd, [
                        'code' => $matches[1],
                        'qty' => $item['quantity_done'],
                        'satuan' => 'EA',
                        'default' => $item['product_id'][1],
                        'lot' => $values,
                    ]);
                    DetailBast::create([
                        'product_id' => $pro->id,
                        'bast_id' => $bast->id,
                        'qty' => $item['quantity_done'],
                        'satuan' => 'EA',
                        'lot' => $values,
                    ]);
                }
            }
        }

        if ($request->wantsJson()) {
            return $this->sendResponse(['message' => 'Success!', 'pd_jd' => $pd_jd, 'do' => $do, 'detail' => $detail]);
        }

        return redirect()->back()->with('success', 'Success!');
    }

    public function download(Request $request, Bast $bast)
    {
        if ($request->type == 'training') {
            $path = $this->training($bast);
        } elseif ($request->type == 'bast') {
            $path = $this->bast($bast);
        } else {
            $path = $this->tanda_terima($bast);
        }

        return response()->download($path)->deleteFileAfterSend();
    }

    public function downloadZip(Request $request, Bast $bast)
    {
        $bast->load('details.product');

        // Format nama file: $bast->do ($bast->name) dengan karakter yang dilarang diubah jadi _
        $filename = "{$bast->do} ({$bast->name})";
        $safeFilename = preg_replace('/[\/\\\\\:\*\?"<>\|]/', '_', $filename);
        $zipName = $safeFilename.'.zip';
        $zipPath = storage_path('app/'.$zipName);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $files = [
                $this->tanda_terima($bast),
                $this->training($bast),
                $this->bast($bast),
            ];

            foreach ($files as $file) {
                if (File::exists($file)) {
                    $zip->addFile($file, basename($file));
                }
            }
            $zip->close();

            // Hapus file docx sementara setelah dimasukkan ke ZIP
            foreach ($files as $file) {
                if (File::exists($file)) {
                    File::delete($file);
                }
            }

            return response()->download($zipPath)->deleteFileAfterSend();
        }

        if ($request->wantsJson()) {
            return $this->sendResponse(null, 'Gagal membuat file ZIP');
        }

        return redirect()->back()->with('error', 'Gagal membuat file ZIP');
    }

    public function print(Request $request, Bast $bast)
    {
        $type = $request->input('type', 'tanda_terima');
        $data = $bast->load('details');
        if ($type == 'tanda_terima') {
            return view('bast.print.tanda_terima', compact(['data', 'type']));
        } elseif ($type == 'training') {
            return view('bast.print.training', compact(['data', 'type']));
        } else {
            return view('bast.print.bast', compact(['data', 'type']));
        }
    }

    public function detailIndex(Request $request)
    {
        $data = DetailBast::query()
            ->with('product')
            ->filter($request->only(['bast_id']))
            ->orderBy('order', 'asc')
            ->get();

        return $this->sendResponse($data);
    }

    public function detailStore(Request $request)
    {
        $this->validate($request, [
            'bast' => 'required|exists:basts,id',
            'product' => 'required|exists:products,id',
            'qty' => 'required',
            'lot' => 'nullable',
            'satuan' => 'required|in:Pcs,Pck,Unit,EA,Box,Btl,Vial',
        ]);

        $lastOrder = DetailBast::where('bast_id', $request->bast)->max('order') ?? -1;

        $data = DetailBast::create([
            'bast_id' => $request->bast,
            'product_id' => $request->product,
            'qty' => $request->qty,
            'lot' => $request->lot,
            'satuan' => $request->satuan,
            'order' => $lastOrder + 1,
        ]);

        if ($request->wantsJson()) {
            return $this->sendResponse($data, 'Created!');
        }

        return redirect()->back()->with('success', 'Created!');
    }

    public function detailShow(Request $request, DetailBast $detail_bast)
    {
        $data = $detail_bast->load('product');

        if ($request->wantsJson()) {
            return $this->sendResponse($data);
        }

        return redirect()->back();
    }

    public function detailUpdate(Request $request, DetailBast $detail_bast)
    {
        $this->validate($request, [
            'qty' => 'required',
            'lot' => 'nullable',
            'satuan' => 'required|in:Pcs,Pck,Unit,EA,Box,Btl,Vial',
        ]);
        $detail_bast->update([
            'qty' => $request->qty,
            'lot' => $request->lot,
            'satuan' => $request->satuan,
        ]);

        if ($request->wantsJson()) {
            return $this->sendResponse($detail_bast, 'Updated!');
        }

        return redirect()->back()->with('success', 'Updated!');
    }

    public function detailDestroy(Request $request, DetailBast $detail_bast)
    {
        $detail_bast->delete();

        if ($request->wantsJson()) {
            return $this->sendResponse($detail_bast, 'Deleted!');
        }

        return redirect()->back()->with('success', 'Deleted!');
    }

    public function detailOrder(Request $request, DetailBast $detail_bast)
    {
        $this->validate($request, [
            'type' => 'required|in:up,down',
        ]);

        try {
            DB::beginTransaction();

            $bastId = $detail_bast->bast_id;

            // Re-index all items to ensure unique sequential orders
            $items = DetailBast::where('bast_id', $bastId)
                ->orderBy('order', 'asc')
                ->orderBy('id', 'asc')
                ->lockForUpdate() // Lock rows to prevent race conditions
                ->get();

            foreach ($items as $index => $item) {
                $item->update(['order' => $index]);
            }

            // Refresh current item to get updated order
            $detail_bast->refresh();
            $currentOrder = $detail_bast->order;

            if ($request->type === 'up') {
                $swapItem = DetailBast::where('bast_id', $bastId)
                    ->where('order', '<', $currentOrder)
                    ->orderBy('order', 'desc')
                    ->first();

                if ($swapItem) {
                    $tempOrder = $swapItem->order;
                    $swapItem->update(['order' => $currentOrder]);
                    $detail_bast->update(['order' => $tempOrder]);
                }
            } else {
                $swapItem = DetailBast::where('bast_id', $bastId)
                    ->where('order', '>', $currentOrder)
                    ->orderBy('order', 'asc')
                    ->first();

                if ($swapItem) {
                    $tempOrder = $swapItem->order;
                    $swapItem->update(['order' => $currentOrder]);
                    $detail_bast->update(['order' => $tempOrder]);
                }
            }

            DB::commit();

            if ($request->wantsJson()) {
                return $this->sendResponse($detail_bast->load('product'), 'Order updated!');
            }

            return redirect()->back()->with('success', 'Order updated!');
        } catch (\Throwable $th) {
            DB::rollBack();

            if ($request->wantsJson()) {
                return $this->sendError($th->getMessage());
            }

            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function detailKargan(Request $request, DetailBast $detail_bast)
    {
        $last = Kargan::generateNumber();
        $product = $detail_bast->product;
        $newkargan = Kargan::create([
            'product_id' => $product->id,
            'date' => date('Y-m-d'),
            'number' => $last,
            'sn' => $detail_bast->lot,
            'masa' => Kargan::getDefaultMasaAttribute(),
            'pic' => Kargan::getDefaultPicAttribute(),
        ]);

        if ($request->wantsJson()) {
            return $this->sendResponse($newkargan, 'Kargan created!');
        }

        return redirect()->back()->with('success', 'Kargan created!');
    }

    private function tanda_terima(Bast $bast)
    {
        $file = public_path('master/tanda_terima.docx');
        $items = [];
        foreach ($bast->details as $key => $item) {
            $lot = ! empty($item->lot) ? ('SN/Lot : '.$item->lot) : '';
            $text = $key + 1 .'. '.$item->qty.' ('.ucfirst(trim(terbilang($item->qty))).') '.$item->satuan.' '.$item->product->name.' ('.$item->product->code.') '.$lot.'.';
            array_push($items, ['items' => htmlspecialchars($text)]);
        }
        $template = new TemplateProcessor($file);
        $template->setValue('name', htmlspecialchars($bast->name));
        $template->setValue('city', htmlspecialchars($bast->city));
        $template->cloneBlock('item_block', 0, true, false, $items);
        $filename = "Tanda_Terima_{$bast->do}_{$bast->name}";
        $safeFilename = preg_replace('/[\/\\\\\:\*\?"<>\|]/', '_', $filename);
        $path = storage_path('app/'.$safeFilename.'.docx');
        $template->saveAs($path);

        return $path;
    }

    private function training(Bast $bast)
    {
        $file = public_path('master/training.docx');
        $items = [];
        foreach ($bast->details as $key => $item) {
            $lot = ! empty($item->lot) ? ('SN/Lot : '.$item->lot) : '';
            $text = $item->qty.' ('.ucfirst(trim(terbilang($item->qty))).') '.$item->satuan.' '.$item->product->name.' ('.$item->product->code.') '.$lot.'.';
            array_push($items, ['items' => '• '.htmlspecialchars($text)]);
        }
        $template = new TemplateProcessor($file);
        $template->setValue('name', htmlspecialchars($bast->name));
        $template->setValue('city', htmlspecialchars($bast->city));
        $template->cloneBlock('item_block', 0, true, false, $items);
        $filename = "Training_{$bast->do}_{$bast->name}";
        $safeFilename = preg_replace('/[\/\\\:\*\?"<>\|]/', '_', $filename);
        $path = storage_path('app/'.$safeFilename.'.docx');
        $template->saveAs($path);

        return $path;
    }

    private function bast(Bast $bast)
    {
        $file = public_path('master/bast.docx');
        $items = [];
        foreach ($bast->details as $key => $item) {
            $lot = ! empty($item->lot) ? ('SN/Lot : '.$item->lot) : '';
            $text = $item->qty.' ('.ucfirst(trim(terbilang($item->qty))).') '.$item->satuan.' '.$item->product->name.' ('.$item->product->code.') '.$lot.'.';
            array_push($items, ['items' => '• '.htmlspecialchars($text)]);
        }
        $template = new TemplateProcessor($file);
        $template->setValue('name', htmlspecialchars($bast->name));
        $template->setValue('city', htmlspecialchars($bast->city));
        $template->setValue('address', htmlspecialchars($bast->address));
        $template->cloneBlock('item_block', 0, true, false, $items);
        $filename = "BAST_{$bast->do}_{$bast->name}";
        $safeFilename = preg_replace('/[\/\\\:\*\?"<>\|]/', '_', $filename);
        $path = storage_path('app/'.$safeFilename.'.docx');
        $template->saveAs($path);

        return $path;
    }
}
