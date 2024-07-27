<?php

namespace App\Http\Controllers;

use App\Models\Bast;
use App\Models\Product;
use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\Settings;

class BastController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('bast.index')->with(['title' => 'List BAST']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $products = Product::all();
        return view('bast.create')->with(['title' => 'Create BAST']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Bast  $bast
     * @return \Illuminate\Http\Response
     */
    public function edit(Bast $bast)
    {
        $products = Product::all();
        $data = $bast->load('details');
        return view('bast.edit', compact(['data', 'products']))->with(['title' => 'Edit BAST']);
    }

    public function show(Request $request, Bast $bast)
    {
        if ($request->type == 'training') {
            return $this->training($bast);
        } elseif ($request->type == 'bast') {
            return $this->bast($bast);
        } else {
            return $this->tanda_terima($bast);
        }
    }

    private function tanda_terima(Bast $bast)
    {
        $file = public_path('master/tanda_terima.docx');
        $items = [];
        foreach ($bast->details as $key => $item) {
            $lot = !empty($item->lot) ? ('Lot : ' . $item->lot) : '';
            $text = $key + 1 . '. ' . $item->qty . ' (' . ucfirst(trim(terbilang($item->qty))) . ') ' . $item->satuan . ' ' . $item->product->name . ' (' . $item->product->code . ') ' . $lot . '.';
            array_push($items, ['items' => htmlspecialchars($text)]);
        }
        $template = new TemplateProcessor($file);
        $template->setValue('name', htmlspecialchars($bast->name));
        $template->setValue('city', htmlspecialchars($bast->city));
        $template->cloneBlock('item_block', 0, true, false, $items);
        $name = Str::slug('tanda_terima_' . $bast->do . '_' . $bast->name, '_');
        $path = public_path('master/' . $name . '.docx');
        $template->saveAs($path);
        return response()->download($path)->deleteFileAfterSend();
    }

    private function training(Bast $bast)
    {
        $file = public_path('master/training.docx');
        $items = [];
        foreach ($bast->details as $key => $item) {
            $lot = !empty($item->lot) ? ('Lot : ' . $item->lot) : '';
            $text =  $item->qty . ' (' . ucfirst(trim(terbilang($item->qty))) . ') ' . $item->satuan . ' '  . $item->product->name . ' (' . $item->product->code . ') ' . $lot . '.';
            array_push($items, ['items' => '• ' . htmlspecialchars($text)]);
        }
        $template = new TemplateProcessor($file);
        $template->setValue('name', htmlspecialchars($bast->name));
        $template->setValue('city', htmlspecialchars($bast->city));
        $template->cloneBlock('item_block', 0, true, false, $items);
        $name = Str::slug('training_' . $bast->do . '_' . $bast->name, '_');
        $path = public_path('master/' . $name . '.docx');
        $template->saveAs($path);
        return response()->download($path)->deleteFileAfterSend();
    }

    private function bast(Bast $bast)
    {
        $file = public_path('master/bast.docx');
        $items = [];
        foreach ($bast->details as $key => $item) {
            $lot = !empty($item->lot) ? ('Lot : ' . $item->lot) : '';
            $text =  $item->qty . ' (' . ucfirst(trim(terbilang($item->qty))) . ') ' . $item->satuan . ' '  . $item->product->name . ' (' . $item->product->code . ') ' . $lot . '.';
            array_push($items, ['items' => '• ' . htmlspecialchars($text)]);
        }
        $template = new TemplateProcessor($file);
        $template->setValue('name', htmlspecialchars($bast->name));
        $template->setValue('city', htmlspecialchars($bast->city));
        $template->setValue('address', htmlspecialchars($bast->address));
        $template->cloneBlock('item_block', 0, true, false, $items);
        $name = Str::slug('bast_' . $bast->do . '_' . $bast->name, '_');
        $path = public_path('master/' . $name . '.docx');
        $template->saveAs($path);
        return response()->download($path)->deleteFileAfterSend();
    }
}
