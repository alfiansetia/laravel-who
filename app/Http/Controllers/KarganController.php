<?php

namespace App\Http\Controllers;

use App\Models\Kargan;
use App\Models\Product;
use App\Services\Breadcrumb;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Str;

class KarganController extends Controller
{
    public function index()
    {
        $bcms = collect([
            new Breadcrumb('List Kargan', route('kargan.index'), false),
        ]);
        return view('kargan.index', compact('bcms'))->with(['title' => 'List Kargan']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $bcms = collect([
            new Breadcrumb('List Kargan', route('kargan.index'), true),
            new Breadcrumb('Create Kargan', route('kargan.create'), false),
        ]);
        $products = Product::all();
        return view('kargan.create', compact('products', 'bcms'))->with(['title' => 'Create Kargan']);
    }

    public function edit(Kargan $kargan)
    {
        $products = Product::all();
        $data = $kargan->load('product');
        $bcms = collect([
            new Breadcrumb('List Kargan', route('kargan.index'), true),
            new Breadcrumb($data->number . ' - ' . $data->do, route('kargan.edit', $data->id), false),
        ]);
        return view('kargan.edit', compact(['data', 'products', 'bcms']))->with(['title' => 'Edit Kargan']);
    }

    public function show(Request $request, Kargan $kargan)
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
        $path = public_path('master/' . $name . '.docx');
        $template->saveAs($path);
        return response()->download($path)->deleteFileAfterSend();
    }
}
