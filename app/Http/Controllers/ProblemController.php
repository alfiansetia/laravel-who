<?php

namespace App\Http\Controllers;

use App\Models\Problem;
use App\Models\ProblemItem;
use App\Models\ProblemLog;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProblemController extends Controller
{
    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            $query = Problem::query();

            // Filter by type (dus/unit)
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            // Filter by year
            if ($request->filled('year')) {
                $query->whereYear('date', $request->year);
            }

            // Filter by product (problems that have items with this product)
            if ($request->filled('product_id')) {
                $query->whereHas('items', function ($q) use ($request) {
                    $q->where('product_id', $request->product_id);
                });
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Search by number, pic, or ri_po
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('number', 'like', "%{$search}%")
                        ->orWhere('pic', 'like', "%{$search}%")
                        ->orWhere('ri_po', 'like', "%{$search}%");
                });
            }

            $perPage = (int) $request->input('per_page', 10);
            $page = (int) $request->input('page', 1);

            $total = $query->count();
            $totalPages = max(1, (int) ceil($total / $perPage));

            $data = $query->orderBy('date', 'desc')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            return response()->json([
                'data' => $data,
                'page' => $page,
                'total_pages' => $totalPages,
                'total' => $total,
            ]);
        }

        return Inertia::render('Problem/Index', [
            'title' => 'Data Problem',
            'filters' => $request->only(['search', 'page', 'type', 'year', 'product_id', 'status']),
            'products' => Product::orderBy('code')->get(['id', 'code', 'name']),
            'picOptions' => ['Karim Ash Shidik', 'Sofyan Saputra', 'Asep'],
            'typeOptions' => ['dus', 'unit'],
            'stockOptions' => ['stock', 'import'],
            'statusOptions' => ['pending', 'done'],
        ]);
    }

    public function import(): Response
    {
        return Inertia::render('Problem/Index', [
            'title' => 'Data Problem',
            'filters' => [],
            'products' => Product::orderBy('code')->get(['id', 'code', 'name']),
            'picOptions' => ['Karim Ash Shidik', 'Sofyan Saputra', 'Asep'],
            'typeOptions' => ['dus', 'unit'],
            'stockOptions' => ['stock', 'import'],
            'statusOptions' => ['pending', 'done'],
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'date' => 'required|date',
            'number' => 'required|string|unique:problems,number',
            'type' => 'required|in:dus,unit',
            'stock' => 'required|in:stock,import',
            'pic' => 'required|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'logs' => 'nullable|array',
            'logs.*.date' => 'required|date',
            'logs.*.desc' => 'required|string',
        ]);

        $problem = DB::transaction(function () use ($request) {
            $problem = Problem::create($request->only([
                'date',
                'number',
                'type',
                'stock',
                'ri_po',
                'status',
                'email_on',
                'pic',
            ]));

            foreach ($request->items as $item) {
                $problem->items()->create([
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'lot' => $item['lot'] ?? null,
                    'desc' => $item['desc'] ?? null,
                ]);
            }

            if ($request->has('logs')) {
                foreach ($request->logs as $log) {
                    $problem->logs()->create([
                        'date' => $log['date'],
                        'desc' => $log['desc'],
                    ]);
                }
            }

            return $problem;
        });

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Data Problem berhasil disimpan.', 'data' => $problem]);
        }

        return redirect()->route('problems.index')
            ->with('success', 'Data Problem berhasil disimpan.');
    }

    public function show(Request $request, Problem $problem): Response|JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            return response()->json(['data' => $problem->load(['items.product', 'logs'])]);
        }

        return Inertia::render('Problem/Show', [
            'title' => 'Detail Problem '.$problem->number,
            'record' => $problem->load(['items.product', 'logs']),
            'products' => Product::orderBy('code')->get(['id', 'code', 'name']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Problem/Create', [
            'title' => 'Tambah Problem',
            'nextNumber' => $this->generateNumber(),
            'defaultDate' => date('Y-m-d'),
            'products' => Product::orderBy('code')->get(['id', 'code', 'name']),
            'picOptions' => ['Karim Ash Shidik', 'Sofyan Saputra', 'Asep'],
            'typeOptions' => ['dus', 'unit'],
            'stockOptions' => ['stock', 'import'],
            'statusOptions' => ['pending', 'done'],
        ]);
    }

    public function edit(Problem $problem): Response
    {
        return Inertia::render('Problem/Edit', [
            'title' => 'Edit Problem '.$problem->number,
            'record' => $problem->load(['items.product', 'logs']),
            'products' => Product::orderBy('code')->get(['id', 'code', 'name']),
            'picOptions' => ['Karim Ash Shidik', 'Sofyan Saputra', 'Asep'],
            'typeOptions' => ['dus', 'unit'],
            'stockOptions' => ['stock', 'import'],
            'statusOptions' => ['pending', 'done'],
        ]);
    }

    public function update(Request $request, Problem $problem): JsonResponse|RedirectResponse
    {
        $request->validate([
            'date' => 'required|date',
            'number' => 'required|string|unique:problems,number,'.$problem->id,
            'type' => 'required|in:dus,unit',
            'stock' => 'required|in:stock,import',
            'pic' => 'required|string|max:100',
            'email_on' => 'nullable|date',
            'ri_po' => 'nullable|string|max:100',
            'status' => 'nullable|in:done,pending',
            'items' => 'sometimes|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'logs' => 'nullable|array',
            'logs.*.date' => 'required|date',
            'logs.*.desc' => 'required|string',
        ]);

        DB::transaction(function () use ($request, $problem) {
            $problem->update($request->only([
                'date',
                'number',
                'type',
                'stock',
                'email_on',
                'ri_po',
                'status',
                'pic',
            ]));

            if ($request->has('items')) {
                $problem->items()->delete();
                foreach ($request->items as $item) {
                    $problem->items()->create([
                        'product_id' => $item['product_id'],
                        'qty' => $item['qty'],
                        'lot' => $item['lot'] ?? null,
                        'desc' => $item['desc'] ?? null,
                    ]);
                }
            }

            if ($request->has('logs')) {
                $problem->logs()->delete();
                foreach ($request->logs as $log) {
                    $problem->logs()->create([
                        'date' => $log['date'],
                        'desc' => $log['desc'],
                    ]);
                }
            }
        });

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Data Problem berhasil diperbarui.']);
        }

        return redirect()->route('problems.index')
            ->with('success', 'Data Problem berhasil diperbarui.');
    }

    public function updateStatus(Request $request, Problem $problem): JsonResponse|RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:pending,done',
        ]);

        $problem->update(['status' => $request->status]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Status berhasil diperbarui.',
                'status' => $problem->status,
            ]);
        }

        return back()->with('success', 'Status berhasil diperbarui.');
    }

    public function destroy(Request $request, Problem $problem): JsonResponse|RedirectResponse
    {
        $problem->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Data Problem berhasil dihapus.']);
        }

        return redirect()->route('problems.index')
            ->with('success', 'Data Problem berhasil dihapus.');
    }

    public function duplicate(Request $request, Problem $problem): JsonResponse|RedirectResponse
    {
        $newProblem = DB::transaction(function () use ($problem) {
            $newProblem = $problem->replicate();

            $prefix = strtoupper(date('Y-M-'));
            $latest = Problem::where('number', 'like', $prefix.'%')->orderBy('number', 'desc')->first();

            if ($latest) {
                $parts = explode('-', $latest->number);
                $serial = intval(end($parts)) + 1;
                $newNumber = $prefix.str_pad($serial, 3, '0', STR_PAD_LEFT);
            } else {
                $newNumber = $prefix.'001';
            }

            $newProblem->number = $newNumber;
            $newProblem->date = date('Y-m-d');
            $newProblem->status = 'pending';
            $newProblem->save();

            foreach ($problem->items as $item) {
                $newItem = $item->replicate();
                $newItem->problem_id = $newProblem->id;
                $newItem->save();
            }

            return $newProblem;
        });

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Data Problem berhasil diduplikasi.', 'data' => $newProblem]);
        }

        return redirect()->route('problems.index')
            ->with('success', 'Data Problem berhasil diduplikasi.');
    }

    public function nextNumber(): JsonResponse
    {
        return response()->json(['number' => $this->generateNumber()]);
    }

    private function generateNumber(): string
    {
        $prefix = strtoupper(date('Y-M-'));
        $latest = Problem::where('number', 'like', $prefix.'%')->orderBy('number', 'desc')->first();

        if ($latest) {
            $parts = explode('-', $latest->number);
            $serial = intval(end($parts)) + 1;

            return $prefix.str_pad($serial, 3, '0', STR_PAD_LEFT);
        }

        return $prefix.'001';
    }

    public function destroy_batch(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:problems,id',
        ]);
        $deleted = Problem::whereIn('id', $request->ids)->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => $deleted.' data berhasil dihapus.']);
        }

        return redirect()->route('problems.index')
            ->with('success', $deleted.' data berhasil dihapus.');
    }

    public function problemItemStore(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'problem_id' => 'required|exists:problems,id',
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1',
            'lot' => 'nullable|string',
            'desc' => 'nullable|string',
        ]);

        $item = ProblemItem::create([
            'problem_id' => $request->problem_id,
            'product_id' => $request->product_id,
            'qty' => $request->qty,
            'lot' => $request->lot,
            'desc' => $request->desc,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Problem item created successfully',
                'data' => $item->load('product'),
            ], 201);
        }

        return back()->with('success', 'Problem item created successfully');
    }

    public function problemItemShow(Request $request, ProblemItem $problemItem): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            return response()->json([
                'data' => $problemItem->load('product'),
            ]);
        }

        return redirect()->route('problems.index');
    }

    public function problemItemUpdate(Request $request, ProblemItem $problemItem): JsonResponse|RedirectResponse
    {
        $request->validate([
            'qty' => 'sometimes|integer|min:1',
            'lot' => 'nullable|string',
            'desc' => 'nullable|string',
        ]);

        $problemItem->update($request->only(['qty', 'lot', 'desc']));

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Problem item updated successfully',
                'data' => $problemItem->load('product'),
            ]);
        }

        return back()->with('success', 'Problem item updated successfully');
    }

    public function problemItemDestroy(Request $request, ProblemItem $problemItem): JsonResponse|RedirectResponse
    {
        $problemItem->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Problem item deleted successfully',
            ]);
        }

        return back()->with('success', 'Problem item deleted successfully');
    }

    public function problemLogStore(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'problem_id' => 'required|exists:problems,id',
            'date' => 'required|date',
            'desc' => 'nullable|string',
        ]);

        $log = ProblemLog::create([
            'problem_id' => $request->problem_id,
            'date' => $request->date,
            'desc' => $request->desc,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Problem log created successfully',
                'data' => $log,
            ], 201);
        }

        return back()->with('success', 'Problem log created successfully');
    }

    public function problemLogShow(Request $request, ProblemLog $problemLog): JsonResponse|RedirectResponse
    {
        if ($request->wantsJson()) {
            return response()->json([
                'data' => $problemLog,
            ]);
        }

        return redirect()->route('problems.index');
    }

    public function problemLogUpdate(Request $request, ProblemLog $problemLog): JsonResponse|RedirectResponse
    {
        $request->validate([
            'date' => 'sometimes|date',
            'desc' => 'nullable|string',
        ]);

        $problemLog->update($request->only(['date', 'desc']));

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Problem log updated successfully',
                'data' => $problemLog,
            ]);
        }

        return back()->with('success', 'Problem log updated successfully');
    }

    public function problemLogDestroy(Request $request, ProblemLog $problemLog): JsonResponse|RedirectResponse
    {
        $problemLog->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Problem log deleted successfully',
            ]);
        }

        return back()->with('success', 'Problem log deleted successfully');
    }
}
