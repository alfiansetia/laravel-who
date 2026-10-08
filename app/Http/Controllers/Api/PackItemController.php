<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pack;
use App\Models\PackItem;
use Illuminate\Http\Request;

class PackItemController extends Controller
{
    public function index(Request $request)
    {
        $packId = $request->input('pack_id');

        if ($packId) {
            $pack = Pack::find($packId);
            if (!$pack) {
                return $this->sendResponse([]);
            }
            $rows = PackItem::flattenedFor($pack);

            $data = collect($rows)->map(function ($r) {
                /** @var PackItem $m */
                $m = $r['model'];

                return [
                    'id'          => $m->id,
                    'pack_id'     => $m->pack_id,
                    'parent_id'   => $m->parent_id,
                    'item'        => $m->item,
                    'qty'         => $m->qty,
                    'is_group'    => (bool) $m->is_group,
                    'show_number' => (bool) $m->show_number,
                    'sort_order'  => $m->sort_order,
                    'level'       => $r['level'],
                    'display_no'  => $r['display_no'],
                ];
            })->values();

            return $this->sendResponse($data);
        }

        $data = PackItem::with(['pack'])->filter($request->only(['pack_id']))->get();

        return $this->sendResponse($data);
    }
}
