<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kargan;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Str;

class KarganController extends Controller
{
    public function __construct()
    {
        $this->middleware('env_auth')->only(['destroy', 'destroy_batch']);
    }

    public function index()
    {
        $data = Kargan::with('product')->latest()->get();
        return $this->sendResponse($data, 'Success!');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'product_id'    => 'required|exists:products,id',
            'date'          => 'date_format:Y-m-d',
            'number'        => 'required|string|max:200',
            'sn'            => 'nullable|string|max:200',
            'pic'           => 'required|string|max:200',
        ]);
        $masa = '1 Tahun ( Unit Utama )';
        $kargan = Kargan::create([
            'product_id'    => $request->product_id,
            'date'          => $request->date,
            'number'        => $request->number,
            'sn'            => $request->sn,
            'pic'           => $request->pic,
            'masa'          => $masa,
        ]);
        return $this->sendResponse($kargan, 'Created!');
    }

    public function update(Request $request, Kargan $kargan)
    {
        $this->validate($request, [
            'product_id'    => 'required|exists:products,id',
            'date'          => 'date_format:Y-m-d',
            'number'        => 'required|string|max:200',
            'sn'            => 'nullable|string|max:200',
            'pic'           => 'required|string|max:200',
        ]);
        $masa = '1 Tahun ( Unit Utama )';
        $kargan->update([
            'product_id'    => $request->product_id,
            'date'          => $request->date,
            'number'        => $request->number,
            'sn'            => $request->sn,
            'pic'           => $request->pic,
            'masa'          => $masa,
        ]);
        return $this->sendResponse($kargan, 'Updated!');
    }

    public function show(Request $request, Kargan $kargan)
    {
        return $this->sendResponse($kargan->load('product'), 'Success!');
    }

    public function destroy(Kargan $kargan)
    {
        $kargan->delete();
        return $this->sendResponse($kargan, 'Deleted!');
    }

    public function destroy_batch(Request $request)
    {
        $this->validate($request, [
            'ids'       => 'required|array',
            'ids.*'     => 'integer|exists:kargans,id',
        ]);
        $deleted = Kargan::whereIn('id', $request->ids)->delete();

        return $this->sendResponse([
            'deleted_count' => $deleted
        ], 'Kargan deleted successfully.');
    }

    public function duplicate(Kargan $kargan)
    {
        $data = $kargan->replicate();
        $data->save();
        return $this->sendResponse($data, 'Success Duplicate!');
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
        $name = Str::slug('krg_' . $kargan->number, '_');
        $path = storage_path('app/' . $name . '.docx');
        $template->saveAs($path);
        return response()->download($path)->deleteFileAfterSend();
    }
}
