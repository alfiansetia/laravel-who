<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Services\VendorOdooServices;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Inertia;

class VendorController extends Controller
{
    public function __construct()
    {
        $this->middleware('env_auth')->only(['destroy', 'update', 'destroy_batch']);
    }

    public function index(Request $request)
    {
        if ($request->wantsJson()) {
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
                'data' => $data,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => (int) ceil($total / $perPage),
            ]);
        }

        return Inertia::render('Vendor/Index', [
            'title' => 'Vendor Odoo',
            'filters' => $request->only(['search', 'page']),
        ]);
    }

    public function show(Request $request, $id)
    {
        $data = Vendor::with(['packs'])->find($id);
        if (! $data) {
            if ($request->wantsJson()) {
                return $this->sendNotFound();
            }

            return redirect()->back()->with('error', 'Not Found!');
        }

        if ($request->wantsJson()) {
            return $this->sendResponse($data);
        }

        return redirect()->back();
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:vendors,name|string|max:200',
            'desc' => 'nullable|string|max:200',
        ]);
        $vendor = Vendor::create([
            'name' => $request->name,
            'desc' => $request->desc,
        ]);

        if ($request->wantsJson()) {
            return $this->sendResponse($vendor, 'Created!');
        }

        return redirect()->back()->with('success', 'Created!');
    }

    public function update(Request $request, $id)
    {
        $vendor = Vendor::find($id);
        if (! $vendor) {
            if ($request->wantsJson()) {
                return $this->sendNotFound();
            }

            return redirect()->back()->with('error', 'Not Found!');
        }
        $this->validate($request, [
            'name' => 'required|string|max:200|unique:vendors,name,'.$id,
            'desc' => 'nullable|string|max:200',
        ]);
        $vendor = Vendor::create([
            'name' => $request->name,
            'desc' => $request->desc,
        ]);

        if ($request->wantsJson()) {
            return $this->sendResponse($vendor, 'Created!');
        }

        return redirect()->back()->with('success', 'Created!');
    }

    public function destroy(Request $request, $id)
    {
        $vendor = Vendor::find($id);
        if (! $vendor) {
            if ($request->wantsJson()) {
                return $this->sendNotFound();
            }

            return redirect()->back()->with('error', 'Not Found!');
        }
        $vendor->delete();

        if ($request->wantsJson()) {
            return $this->sendResponse($vendor, 'Deleted!');
        }

        return redirect()->back()->with('success', 'Deleted!');
    }

    public function destroy_batch(Request $request)
    {
        $this->validate($request, [
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:vendors,id',
        ]);
        $deleted = Vendor::whereIn('id', $request->ids)->delete();

        if ($request->wantsJson()) {
            return $this->sendResponse([
                'deleted_count' => $deleted,
            ], 'Vendor deleted successfully.');
        }

        return redirect()->back()->with('success', 'Vendor deleted successfully.');
    }

    public function vendorOdooIndex(Request $request)
    {
        $perPage = max((int) $request->input('per_page', 10), 1);
        $page = max((int) $request->input('page', 1), 1);
        $offset = ($page - 1) * $perPage;
        $search = (string) ($request->input('search') ?? '');

        $response = VendorOdooServices::getAll($search, $perPage, $offset);
        $total = Arr::get($response, 'length', 0);
        $data = Arr::get($response, 'records', []);

        return response()->json([
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $perPage > 0 ? (int) ceil($total / $perPage) : 0,
        ]);
    }

    public function vendorOdooDetail(Request $request, $id)
    {
        $response = VendorOdooServices::detail((int) $id);

        return $this->sendResponse($response);
    }
}
