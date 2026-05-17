<?php

namespace App\Services;

use Illuminate\Http\Request;

class Datatable
{
    public array $data = [];
    public int $draw = 0;
    public int $recordsTotal = 0;
    public int $recordsFiltered = 0;
    public int $page = 1;
    public int $limit = 10;
    public int $offset = 0;
    public string $search = '';

    public function __construct()
    {
        //
    }

    public static function fromRequest(Request $request)
    {
        $dt = new self();
        $dt->draw = $request->draw;
        $dt->recordsTotal = $request->recordsTotal;
        $dt->recordsFiltered = $request->recordsFiltered;
        $dt->page = $request->page;
        $dt->limit = $request->limit;
        $dt->offset = $request->offset;
        $dt->search = $request->search;
        if (is_array($dt->search)) {
            $dt->search = $dt->search['value'] ?? '';
        }
        return $dt;
    }
}
