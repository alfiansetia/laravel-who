<?php

namespace App\Http\Controllers;

use App\Models\NotificationHistory;
use App\Services\FirebaseServices;
use App\Services\OdooSession;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationHistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('env_auth')->except(['poll']);
    }

    /**
     * Endpoint polling ringan untuk aplikasi desktop.
     * Public (tanpa env_auth) + rate-limit ketat di route.
     * GET /api/notif-histories/poll?since_id=0&limit=20
     */
    public function poll(Request $request)
    {
        $validated = $request->validate([
            'since_id' => ['nullable', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $sinceId = (int) ($validated['since_id'] ?? 0);
        $limit = (int) ($validated['limit'] ?? 20);

        $rows = NotificationHistory::query()
            ->select(['id', 'type', 'title', 'body', 'do_name', 'so_id', 'origin', 'partner', 'status', 'sent_at'])
            ->where('id', '>', $sinceId)
            ->orderBy('id')
            ->limit($limit + 1)
            ->get();

        $hasMore = $rows->count() > $limit;
        $data = $hasMore ? $rows->slice(0, $limit)->values() : $rows->values();

        return response()->json([
            'data' => $data,
            'latest_id' => $data->isNotEmpty() ? $data->max('id') : $sinceId,
            'count' => $data->count(),
            'has_more' => $hasMore,
        ]);
    }

    public function index(Request $request)
    {
        if ($this->isApi($request)) {
            $perPage = max((int) $request->input('per_page', 10), 1);
            $page = max((int) $request->input('page', 1), 1);
            $search = trim((string) $request->input('search', ''));
            $status = trim((string) $request->input('status', ''));
            $type = trim((string) $request->input('type', ''));
            $dateFrom = trim((string) $request->input('date_from', ''));
            $dateTo = trim((string) $request->input('date_to', ''));

            $query = NotificationHistory::query()->orderByDesc('sent_at')->orderByDesc('id');

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%")
                        ->orWhere('do_name', 'like', "%{$search}%")
                        ->orWhere('origin', 'like', "%{$search}%")
                        ->orWhere('partner', 'like', "%{$search}%");
                });
            }

            if ($status !== '' && $status !== 'all') {
                $query->where('status', $status);
            }

            if ($type !== '' && $type !== 'all') {
                $query->where('type', $type);
            }

            if ($dateFrom !== '') {
                $query->where('sent_at', '>=', Carbon::parse($dateFrom)->startOfDay());
            }

            if ($dateTo !== '') {
                $query->where('sent_at', '<=', Carbon::parse($dateTo)->endOfDay());
            }

            $total = $query->count();
            $data = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

            return response()->json([
                'data' => $data,
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => $perPage > 0 ? (int) ceil($total / $perPage) : 0,
            ]);
        }

        return Inertia::render('NotifHistory/Index', [
            'title' => 'History Notifikasi DO',
            'filters' => $request->only(['search', 'page', 'status', 'type', 'date_from', 'date_to']),
        ]);
    }

    public function show(Request $request, NotificationHistory $notifHistory)
    {
        if ($this->isApi($request)) {
            return $this->sendResponse($notifHistory->load('parent'));
        }

        return redirect()->route('notif_histories.index');
    }

    public function resend(Request $request, NotificationHistory $notifHistory)
    {
        $ok = FirebaseServices::sendToTopic(
            $notifHistory->title,
            $notifHistory->body,
            (int) ($notifHistory->so_id ?? 0),
            $notifHistory->topic ?? 'general'
        );

        $session = OdooSession::getCurrentSession();

        $log = NotificationHistory::create([
            'type' => $notifHistory->type,
            'title' => $notifHistory->title,
            'body' => $notifHistory->body,
            'topic' => $notifHistory->topic ?? 'general',
            'do_name' => $notifHistory->do_name,
            'so_id' => $notifHistory->so_id,
            'origin' => $notifHistory->origin,
            'partner' => $notifHistory->partner,
            'payload' => $notifHistory->payload,
            'channel' => 'fcm_topic',
            'status' => $ok ? 'success' : 'failed',
            'source' => 'manual',
            'triggered_by' => trim(($session['name'] ?? '').' ('.($session['uid'] ?? '-').')'),
            'parent_id' => $notifHistory->id,
            'sent_at' => now(),
        ]);

        if ($this->isApi($request)) {
            if (! $ok) {
                return $this->sendError('Gagal blast ulang notifikasi.', 422);
            }

            return $this->sendResponse($log, 'Notifikasi berhasil di-blast ulang.');
        }

        if (! $ok) {
            return back()->with('error', 'Gagal blast ulang notifikasi.');
        }

        return back()->with('success', 'Notifikasi berhasil di-blast ulang.');
    }

    public function destroy(Request $request, NotificationHistory $notifHistory)
    {
        $notifHistory->delete();

        if ($this->isApi($request)) {
            return $this->sendResponse($notifHistory, 'History notifikasi dihapus.');
        }

        return back()->with('success', 'History notifikasi dihapus.');
    }

    public function destroy_batch(Request $request)
    {
        $this->validate($request, [
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:notification_histories,id',
        ]);
        $deleted = NotificationHistory::whereIn('id', $request->ids)->delete();

        if ($this->isApi($request)) {
            return $this->sendResponse(['deleted_count' => $deleted], 'History notifikasi dihapus.');
        }

        return back()->with('success', 'History notifikasi dihapus.');
    }
}
