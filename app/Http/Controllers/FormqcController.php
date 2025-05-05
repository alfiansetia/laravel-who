<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Str;

class FormqcController extends Controller
{
    public function create()
    {
        $products = Product::all();
        return view('qc.create', compact('products'))->with('title', 'Form QC');
    }

    public function store(Request $request)
    {
        // dd($request->all());
        try {
            $data_kel = array_values($request->kelengkapan ?? []);
            $data_kel_radio = array_values($request->kelengkapan_radio ?? []);
            $data_kel_desc = array_values($request->kelengkapan_desc ?? []);

            $file = public_path('master/qc.docx');
            $template = new TemplateProcessor($file);
            $total = count($data_kel);
            $kel_row = max(24, $total);
            $template->cloneRow('kel_v', $kel_row);

            $i = 1;
            foreach ($data_kel as $index => $row) {
                $i = $index + 1;
                $template->setValue("kel_v#$i", $row);
                $template->setValue("kel_y#$i", $data_kel_radio[$index] == 'yes' ? 'v' : '');
                $template->setValue("kel_n#$i", $data_kel_radio[$index] == 'no' ? 'v' : '');
                $template->setValue("kel_d#$i", $data_kel_desc[$index]);
            }
            for ($j = $total + 1; $j <= $kel_row; $j++) {
                $template->setValue("kel_v#$j", '');
                $template->setValue("kel_y#$j", '');
                $template->setValue("kel_n#$j", '');
                $template->setValue("kel_d#$j", '');
            }

            $tgl = Carbon::parse($request->tgl)->format('d-m-Y');

            $template->setValues([
                'tgl'       => htmlspecialchars($tgl),
                'pic'       => htmlspecialchars($request->pic),
                'name'      => htmlspecialchars($request->name),
                'merk'      => htmlspecialchars($request->merk),
                'type'      => htmlspecialchars($request->type),
                'sn_lot'    => htmlspecialchars($request->sn_lot ?? ''),
                'qty'       => htmlspecialchars($request->qty),
                'jenis'     => htmlspecialchars($request->jenis),
                'qc_sebelumnya' => htmlspecialchars($request->qc_sebelumnya),

                'fisik_0y' => htmlspecialchars($request->fisik_radio[0] == 'yes' ? 'v' : ''),
                'fisik_0n' => htmlspecialchars($request->fisik_radio[0] == 'no' ? 'v' : ''),
                'fisik_0d' => htmlspecialchars($request->fisik_desc[0]),
                'fisik_1y' => htmlspecialchars($request->fisik_radio[1] == 'yes' ? 'v' : ''),
                'fisik_1n' => htmlspecialchars($request->fisik_radio[1] == 'no' ? 'v' : ''),
                'fisik_1d' => htmlspecialchars($request->fisik_desc[1]),
                'fisik_2y' => htmlspecialchars($request->fisik_radio[2] == 'yes' ? 'v' : ''),
                'fisik_2n' => htmlspecialchars($request->fisik_radio[2] == 'no' ? 'v' : ''),
                'fisik_2d' => htmlspecialchars($request->fisik_desc[2]),
                'fisik_3y' => htmlspecialchars($request->fisik_radio[3] == 'yes' ? 'v' : ''),
                'fisik_3n' => htmlspecialchars($request->fisik_radio[3] == 'no' ? 'v' : ''),
                'fisik_3d' => htmlspecialchars($request->fisik_desc[3]),
                'fisik_4y' => htmlspecialchars($request->fisik_radio[4] == 'yes' ? 'v' : ''),
                'fisik_4n' => htmlspecialchars($request->fisik_radio[4] == 'no' ? 'v' : ''),
                'fisik_4d' => htmlspecialchars($request->fisik_desc[4]),
                'fisik_5y' => htmlspecialchars($request->fisik_radio[5] == 'yes' ? 'v' : ''),
                'fisik_5n' => htmlspecialchars($request->fisik_radio[5] == 'no' ? 'v' : ''),
                'fisik_5d' => htmlspecialchars($request->fisik_desc[5]),

                'reagen_0y' => htmlspecialchars($request->reagen_radio[0] == 'yes' ? 'v' : ''),
                'reagen_0n' => htmlspecialchars($request->reagen_radio[0] == 'no' ? 'v' : ''),
                'reagen_0d' => htmlspecialchars($request->reagen_desc[0]),
                'reagen_1y' => htmlspecialchars($request->reagen_radio[1] == 'yes' ? 'v' : ''),
                'reagen_1n' => htmlspecialchars($request->reagen_radio[1] == 'no' ? 'v' : ''),
                'reagen_1d' => htmlspecialchars($request->reagen_desc[1]),
                'reagen_2y' => htmlspecialchars($request->reagen_radio[2] == 'yes' ? 'v' : ''),
                'reagen_2n' => htmlspecialchars($request->reagen_radio[2] == 'no' ? 'v' : ''),
                'reagen_2d' => htmlspecialchars($request->reagen_desc[2]),
                'reagen_3y' => htmlspecialchars($request->reagen_radio[3] == 'yes' ? 'v' : ''),
                'reagen_3n' => htmlspecialchars($request->reagen_radio[3] == 'no' ? 'v' : ''),
                'reagen_3d' => htmlspecialchars($request->reagen_desc[3]),
            ]);

            $no = $request->no;
            $name = "$no. " . Str::slug($tgl . '_' . $request->type . '_' . $request->name, '_');
            $path = public_path('master/' . $name . '.docx');
            $template->saveAs($path);
            return response()->download($path)->deleteFileAfterSend();
        } catch (\Throwable $th) {
            return redirect()->route('qc.create')->with('error', 'Error : ' . $th->getMessage());
        }
    }
}
