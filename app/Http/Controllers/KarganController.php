<?php

namespace App\Http\Controllers;

use App\Models\Kargan;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpWord\TemplateProcessor;

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
        $data->save();

        if ($request->wantsJson()) {
            return $this->sendResponse($data, 'Success Duplicate!');
        }

        return redirect()->back()->with('success', 'Success Duplicate!');
    }

    public function download(Kargan $kargan)
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

        return response()->download($path)->deleteFileAfterSend();
    }
}
