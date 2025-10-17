<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $data = Vendor::query()->withCount(['packs'])->filter($request->only(['name', 'desc']))->get();
        return $this->sendResponse($data);
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
