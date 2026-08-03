<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateGateSyncActionsRequest;
use App\Models\GateSyncItem;
use App\Models\GateSyncRun;
use App\Models\User;
use App\Services\ApplyGateSyncService;
use App\Services\GateProvisioningClient;
use App\Services\SyncReconciliationService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GateUserSyncController extends Controller
{
    public function index()
    {
        $runs = GateSyncRun::with('initiator')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.sync.index', compact('runs'));
    }

    /**
     * Step 1: Fetch & Preview Reconciliation (Dry-run without altering user domain data)
     */
    public function preview(
        GateProvisioningClient $client,
        SyncReconciliationService $reconciliationService
    ) {
        try {
            $gateUsers = $client->fetchCanonicalUsers();
            $localUsers = User::all();

            $report = $reconciliationService->reconcile($gateUsers, $localUsers);

            $summaryCounts = [];
            foreach ($report as $catKey => $items) {
                $summaryCounts[$catKey] = count($items);
            }

            $run = GateSyncRun::create([
                'uuid' => (string) Str::uuid(),
                'initiated_by' => auth()->id(),
                'status' => 'previewed',
                'preview_hash' => md5(json_encode($summaryCounts)),
                'summary_counts' => $summaryCounts,
                'started_at' => now(),
                'previewed_at' => now(),
            ]);

            foreach ($report as $catKey => $items) {
                foreach ($items as $item) {
                    GateSyncItem::create([
                        'gate_sync_run_id' => $run->id,
                        'gate_user_uuid' => $item['gate_user']['uuid'] ?? null,
                        'local_user_id' => $item['local_user']['id'] ?? null,
                        'category' => $catKey,
                        'selected_action' => $item['suggested_action'] ?? 'no_change',
                        'result_status' => 'pending',
                        'gate_snapshot' => $item['gate_user'] ?? null,
                        'local_snapshot' => $item['local_user'] ?? null,
                        'field_differences' => $item['differences'] ?? null,
                        'error_message' => $item['conflict_reason'] ?? null,
                    ]);
                }
            }

            return redirect()->route('admin.sync.show', $run->id)
                ->with('success', 'Dry-run preview sinkronisasi berhasil dibuat.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses preview sinkronisasi: '.$e->getMessage());
        }
    }

    public function show(GateSyncRun $run)
    {
        $run->load('items.localUser');

        $groupedItems = $run->items->groupBy('category');

        return view('admin.sync.show', compact('run', 'groupedItems'));
    }

    public function updateActions(UpdateGateSyncActionsRequest $request, GateSyncRun $run)
    {
        if ($run->status !== 'previewed') {
            return back()->with('error', 'Pilihan tindakan hanya dapat diubah saat status masih previewed.');
        }

        $allowedByCategory = [
            'matched' => ['no_change'],
            'needs_update' => ['update_local', 'no_change'],
            'missing_in_application' => ['create_local', 'no_change'],
            'access_revoked' => ['suspend_local', 'no_change'],
            'inactive_in_gate' => ['suspend_local', 'no_change'],
            'reactivation_required' => ['reactivate_local', 'no_change'],
            'local_only' => ['manual_review', 'no_change'],
            'conflict' => ['manual_review', 'no_change'],
        ];

        $items = $run->items()->whereIn('id', array_keys($request->validated('actions')))->get()->keyBy('id');
        if ($items->count() !== count($request->validated('actions'))) {
            throw ValidationException::withMessages(['actions' => 'Terdapat item sinkronisasi yang tidak termasuk dalam run ini.']);
        }

        foreach ($request->validated('actions') as $itemId => $action) {
            $item = $items->get((int) $itemId);
            if (! in_array($action, $allowedByCategory[$item->category] ?? [], true)) {
                throw ValidationException::withMessages([
                    "actions.{$itemId}" => 'Tindakan tidak diizinkan untuk kategori '.$item->category.'.',
                ]);
            }
        }

        foreach ($request->validated('actions') as $itemId => $action) {
            $items->get((int) $itemId)->update(['selected_action' => $action]);
        }

        return back()->with('success', 'Pilihan tindakan sinkronisasi berhasil disimpan.');
    }

    /**
     * Step 2: Apply Confirmed Changes & Report Back
     */
    public function apply(GateSyncRun $run, ApplyGateSyncService $applyService)
    {
        if ($run->status !== 'previewed') {
            return back()->with('error', 'Sinkronisasi ini sudah pernah diterapkan atau kadaluarsa.');
        }

        try {
            $updatedRun = $applyService->execute($run);

            return redirect()->route('admin.sync.show', $updatedRun->id)
                ->with('success', 'Sinkronisasi user dari Gate SSO berhasil diterapkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengaplikasikan sinkronisasi: '.$e->getMessage());
        }
    }

    public function retryReport(GateSyncRun $run, ApplyGateSyncService $applyService)
    {
        if ($run->status !== 'report_pending') {
            return back()->with('error', 'Hanya laporan berstatus report_pending yang dapat dikirim ulang.');
        }

        if (! $applyService->retryReport($run)) {
            return back()->with('error', 'Gate SSO belum menerima laporan. Silakan coba lagi setelah memeriksa koneksi.');
        }

        return back()->with('success', 'Laporan hasil sinkronisasi berhasil dikirim ulang ke Gate SSO.');
    }
}
