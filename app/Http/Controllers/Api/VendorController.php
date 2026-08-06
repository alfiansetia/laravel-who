<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function __construct()
    {
        $this->middleware('env_auth')->only(['destroy', 'update', 'destroy_batch']);
    }

    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 25), 200);
        $page = max((int) $request->input('page', 1), 1);

        $query = Vendor::query()->withCount(['packs']);

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('desc', 'like', "%{$keyword}%");
            });
        }

        $total = (clone $query)->count();

        $data = $query->orderBy('name', 'asc')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return response()->json([
            'data'        => $data,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ]);
    }

    public function show($id)
    {
        $data = Vendor::with(['packs'])->find($id);
        if (!$data) {
            return $this->sendNotFound();
        }
        return $this->sendResponse($data);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name'          => 'required|unique:vendors,name|string|max:200',
            'desc'          => 'nullable|string|max:200',
        ]);
        $vendor = Vendor::create([
            'name'  => $request->name,
            'desc'  => $request->desc,
        ]);
        return $this->sendResponse($vendor, 'Created!');
    }

    public function update(Request $request, $id)
    {
        $vendor = Vendor::find($id);
        if (!$vendor) {
            return $this->sendNotFound();
        }
        $this->validate($request, [
            'name'          => 'required|string|max:200|unique:vendors,name,' . $id,
            'desc'          => 'nullable|string|max:200',
        ]);
        $vendor = Vendor::create([
            'name'  => $request->name,
            'desc'  => $request->desc,
        ]);
        return $this->sendResponse($vendor, 'Created!');
    }

    public function destroy($id)
    {
        $vendor = Vendor::find($id);
        if (!$vendor) {
            return $this->sendNotFound();
        }
        $vendor->delete();
        return $this->sendResponse($vendor, 'Deleted!');
    }

    public function destroy_batch(Request $request)
    {
        $this->validate($request, [
            'ids'       => 'required|array',
            'ids.*'     => 'integer|exists:vendors,id',
        ]);
        $deleted = Vendor::whereIn('id', $request->ids)->delete();

        return $this->sendResponse([
            'deleted_count' => $deleted
        ], 'Vendor deleted successfully.');
    }
}
