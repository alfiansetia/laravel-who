<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpWord\TemplateProcessor;

class QcController extends Controller
{
    public function index(): Response
    {
        $now = Carbon::now();
        if ($now->isMonday()) {
            $date = $now->copy()->subDays(3)->toDateString();
        } else {
            $date = $now->copy()->subDay()->toDateString();
        }

        return Inertia::render('Qc/Index', [
            'title' => 'Form QC',
            'defaultDate' => $date,
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'no' => 'required|max:200',
        ]);
        // return $this->sendResponse($request->all());
        try {
            $data_kel = array_values($request->kelengkapan ?? []);
            $data_kel_radio = array_values($request->kelengkapan_radio ?? []);
            $data_kel_desc = array_values($request->kelengkapan_desc ?? []);
            $fisik_radio = array_values($request->fisik_radio ?? []);
            $fisik_desc = array_values($request->fisik_desc ?? []);
            $data_reagen = array_values($request->reagen ?? []);
            $reagen_radio = array_values($request->reagen_radio ?? []);
            $reagen_desc = array_values($request->reagen_desc ?? []);

            $file = public_path('master/qc.docx');
            $template = new TemplateProcessor($file);
            $extra_reagen = [];
            foreach ($data_reagen as $index => $row) {
                if ($index < 4) {
                    continue;
                }
                $extra_reagen[] = [
                    'v' => $row ?? '',
                    'radio' => $reagen_radio[$index] ?? null,
                    'd' => $reagen_desc[$index] ?? '',
                ];
            }
            if (in_array('reagen_v', $template->getVariables())) {
                $reagen_row = max(1, count($extra_reagen));
                $template->cloneRow('reagen_v', $reagen_row);
                foreach ($extra_reagen as $index => $row) {
                    $i = $index + 1;
                    $template->setValue("reagen_v#$i", htmlspecialchars($row['v']));
                    $template->setValue('reagen_y#'.$i, htmlspecialchars($row['radio'] == 'yes' ? 'v' : ''));
                    $template->setValue('reagen_n#'.$i, htmlspecialchars($row['radio'] == 'no' ? 'v' : ''));
                    $template->setValue("reagen_d#$i", htmlspecialchars($row['d']));
                }
                for ($j = count($extra_reagen) + 1; $j <= $reagen_row; $j++) {
                    $template->setValue("reagen_v#$j", '');
                    $template->setValue("reagen_y#$j", '');
                    $template->setValue("reagen_n#$j", '');
                    $template->setValue("reagen_d#$j", '');
                }
            }
            $total = count($data_kel);
            $kel_row = max(23, $total);
            $template->cloneRow('kel_v', $kel_row);

            $i = 1;
            foreach ($data_kel as $index => $row) {
                $i = $index + 1;
                $template->setValue("kel_v#$i", htmlspecialchars($row));
                $template->setValue("kel_y#$i", htmlspecialchars($data_kel_radio[$index]) == 'yes' ? 'v' : '');
                $template->setValue("kel_n#$i", htmlspecialchars($data_kel_radio[$index]) == 'no' ? 'v' : '');
                $template->setValue("kel_d#$i", htmlspecialchars($data_kel_desc[$index]));
            }
            for ($j = $total + 1; $j <= $kel_row; $j++) {
                $template->setValue("kel_v#$j", '');
                $template->setValue("kel_y#$j", '');
                $template->setValue("kel_n#$j", '');
                $template->setValue("kel_d#$j", '');
            }

            $tgl = Carbon::parse($request->tgl)->format('d-m-Y');

            $template->setValues([
                'tgl' => htmlspecialchars($tgl),
                'pic' => htmlspecialchars($request->pic),
                'name' => htmlspecialchars($request->name),
                'merk' => htmlspecialchars($request->merk),
                'type' => htmlspecialchars($request->type),
                'sn_lot' => htmlspecialchars($request->sn_lot ?? ''),
                'qty' => htmlspecialchars($request->qty),
                'jenis' => htmlspecialchars($request->jenis),
                'qc_sebelumnya' => htmlspecialchars($request->qc_sebelumnya),

                'fisik_0y' => htmlspecialchars(($fisik_radio[0] ?? null) == 'yes' ? 'v' : ''),
                'fisik_0n' => htmlspecialchars(($fisik_radio[0] ?? null) == 'no' ? 'v' : ''),
                'fisik_0d' => htmlspecialchars($fisik_desc[0] ?? ''),
                'fisik_1y' => htmlspecialchars(($fisik_radio[1] ?? null) == 'yes' ? 'v' : ''),
                'fisik_1n' => htmlspecialchars(($fisik_radio[1] ?? null) == 'no' ? 'v' : ''),
                'fisik_1d' => htmlspecialchars($fisik_desc[1] ?? ''),
                'fisik_2y' => htmlspecialchars(($fisik_radio[2] ?? null) == 'yes' ? 'v' : ''),
                'fisik_2n' => htmlspecialchars(($fisik_radio[2] ?? null) == 'no' ? 'v' : ''),
                'fisik_2d' => htmlspecialchars($fisik_desc[2] ?? ''),
                'fisik_3y' => htmlspecialchars(($fisik_radio[3] ?? null) == 'yes' ? 'v' : ''),
                'fisik_3n' => htmlspecialchars(($fisik_radio[3] ?? null) == 'no' ? 'v' : ''),
                'fisik_3d' => htmlspecialchars($fisik_desc[3] ?? ''),
                'fisik_4y' => htmlspecialchars(($fisik_radio[4] ?? null) == 'yes' ? 'v' : ''),
                'fisik_4n' => htmlspecialchars(($fisik_radio[4] ?? null) == 'no' ? 'v' : ''),
                'fisik_4d' => htmlspecialchars($fisik_desc[4] ?? ''),
                'fisik_5y' => htmlspecialchars(($fisik_radio[5] ?? null) == 'yes' ? 'v' : ''),
                'fisik_5n' => htmlspecialchars(($fisik_radio[5] ?? null) == 'no' ? 'v' : ''),
                'fisik_5d' => htmlspecialchars($fisik_desc[5] ?? ''),
                'fisik_6y' => htmlspecialchars(($fisik_radio[6] ?? null) == 'yes' ? 'v' : ''),
                'fisik_6n' => htmlspecialchars(($fisik_radio[6] ?? null) == 'no' ? 'v' : ''),
                'fisik_6d' => htmlspecialchars($fisik_desc[6] ?? ''),
                'fisik_7y' => htmlspecialchars(($fisik_radio[7] ?? null) == 'yes' ? 'v' : ''),
                'fisik_7n' => htmlspecialchars(($fisik_radio[7] ?? null) == 'no' ? 'v' : ''),
                'fisik_7d' => htmlspecialchars($fisik_desc[7] ?? ''),

                'reagen_0y' => htmlspecialchars(($reagen_radio[0] ?? null) == 'yes' ? 'v' : ''),
                'reagen_0n' => htmlspecialchars(($reagen_radio[0] ?? null) == 'no' ? 'v' : ''),
                'reagen_0d' => htmlspecialchars($reagen_desc[0] ?? ''),
                'reagen_1y' => htmlspecialchars(($reagen_radio[1] ?? null) == 'yes' ? 'v' : ''),
                'reagen_1n' => htmlspecialchars(($reagen_radio[1] ?? null) == 'no' ? 'v' : ''),
                'reagen_1d' => htmlspecialchars($reagen_desc[1] ?? ''),
                'reagen_2y' => htmlspecialchars(($reagen_radio[2] ?? null) == 'yes' ? 'v' : ''),
                'reagen_2n' => htmlspecialchars(($reagen_radio[2] ?? null) == 'no' ? 'v' : ''),
                'reagen_2d' => htmlspecialchars($reagen_desc[2] ?? ''),
                'reagen_3y' => htmlspecialchars(($reagen_radio[3] ?? null) == 'yes' ? 'v' : ''),
                'reagen_3n' => htmlspecialchars(($reagen_radio[3] ?? null) == 'no' ? 'v' : ''),
                'reagen_3d' => htmlspecialchars($reagen_desc[3] ?? ''),
            ]);

            $no = $request->no;
            $file_name = $no.'.'.$tgl.'_'.$request->type.'_'.$request->name.'.docx';
            $name = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $file_name);
            $path = storage_path('app/public/'.$name);
            $template->saveAs($path);

            return response()->download($path)->deleteFileAfterSend();
        } catch (\Throwable $th) {
            if ($request->wantsJson()) {
                return $this->sendError($th->getMessage());
            }

            return redirect()->back()->withInput()->with('error', $th->getMessage());
        }
    }
}
