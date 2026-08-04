<?php

namespace App\Services;

use App\Models\GateSyncItem;
use App\Models\GateSyncRun;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ApplyGateSyncService
{
    public function __construct(
        protected GateProvisioningClient $client,
        protected GateUserMapper $mapper,
        protected GateDomainProfileProvisioner $profileProvisioner,
    ) {}

    public function execute(GateSyncRun $run): GateSyncRun
    {
        $syncResultItems = [];

        DB::transaction(function () use ($run, &$syncResultItems) {
            $lockedRun = GateSyncRun::query()->lockForUpdate()->findOrFail($run->id);
            if ($lockedRun->status !== 'previewed') {
                throw ValidationException::withMessages(['run' => 'Run sinkronisasi sudah diproses.']);
            }
            $lockedRun->update(['status' => 'applying']);

            $items = GateSyncItem::where('gate_sync_run_id', $run->id)->lockForUpdate()->get();

            foreach ($items as $item) {
                $gu = $item->gate_snapshot;
                $lu = $item->local_snapshot;
                $category = $item->category;

                if ($item->selected_action === 'no_change' && $category !== 'matched') {
                    $item->update(['result_status' => 'skipped']);

                    continue;
                }

                switch ($category) {
                    case 'missing_in_application':
                        if ($gu) {
                            $newUser = User::create([
                                ...$this->mapper->map($gu),
                                'password' => Hash::make(Str::random(32)),
                            ]);
                            $this->profileProvisioner->sync($newUser, $gu);

                            $item->update([
                                'local_user_id' => $newUser->id,
                                'selected_action' => 'create_local',
                                'result_status' => 'success',
                                'external_user_id' => (string) $newUser->id,
                            ]);

                            $syncResultItems[] = [
                                'gate_user_uuid' => $gu['uuid'],
                                'status' => 'matched',
                                'external_user_id' => (string) $newUser->id,
                            ];
                        }
                        break;

                    case 'needs_update':
                        if ($gu && $item->local_user_id) {
                            $mapped = $this->mapper->map($gu);
                            $localUser = User::query()->findOrFail($item->local_user_id);
                            $localUser->update([
                                'name' => $mapped['name'],
                                'email' => $mapped['email'],
                                'username' => $mapped['username'],
                                'type' => $mapped['type'],
                                'role' => $mapped['role'],
                                'application_role' => $mapped['application_role'],
                                'auth_source' => $mapped['auth_source'],
                            ]);
                            $this->profileProvisioner->sync($localUser, $gu);

                            $item->update([
                                'selected_action' => 'update_local',
                                'result_status' => 'success',
                                'external_user_id' => (string) $item->local_user_id,
                            ]);

                            $syncResultItems[] = [
                                'gate_user_uuid' => $gu['uuid'],
                                'status' => 'matched',
                                'external_user_id' => (string) $item->local_user_id,
                            ];
                        }
                        break;

                    case 'access_revoked':
                    case 'inactive_in_gate':
                        if ($item->local_user_id) {
                            User::where('id', $item->local_user_id)->update([
                                'status' => 'suspended',
                                'suspended_at' => now(),
                                'suspension_reason' => $category === 'access_revoked' ? 'Akses dicabut di Gate SSO' : 'Identitas inactive di Gate SSO',
                            ]);

                            $item->update([
                                'selected_action' => 'suspend_local',
                                'result_status' => 'success',
                                'external_user_id' => (string) $item->local_user_id,
                            ]);

                            if ($gu && isset($gu['uuid'])) {
                                $syncResultItems[] = [
                                    'gate_user_uuid' => $gu['uuid'],
                                    'status' => 'suspended',
                                    'external_user_id' => (string) $item->local_user_id,
                                ];
                            }
                        }
                        break;

                    case 'reactivation_required':
                        if ($item->local_user_id) {
                            User::where('id', $item->local_user_id)->update([
                                'status' => 'aktif',
                                'suspended_at' => null,
                                'suspension_reason' => null,
                            ]);

                            $item->update([
                                'selected_action' => 'reactivate_local',
                                'result_status' => 'success',
                                'external_user_id' => (string) $item->local_user_id,
                            ]);

                            if ($gu && isset($gu['uuid'])) {
                                $syncResultItems[] = [
                                    'gate_user_uuid' => $gu['uuid'],
                                    'status' => 'matched',
                                    'external_user_id' => (string) $item->local_user_id,
                                ];
                            }
                        }
                        break;

                    case 'matched':
                        $item->update([
                            'selected_action' => 'no_change',
                            'result_status' => 'success',
                            'external_user_id' => (string) $item->local_user_id,
                        ]);
                        if ($gu && isset($gu['uuid'])) {
                            $syncResultItems[] = [
                                'gate_user_uuid' => $gu['uuid'],
                                'status' => 'matched',
                                'external_user_id' => (string) $item->local_user_id,
                            ];
                        }
                        break;

                    case 'conflict':
                        $errorCode = $item->error_code ?: 'unlinked_matching_identifier';
                        $errorMessage = $item->error_message
                            ?: 'Email atau username cocok tetapi gate_user_uuid belum terhubung.';
                        $item->update([
                            'selected_action' => 'manual_review',
                            'result_status' => 'skipped',
                            'error_code' => $errorCode,
                            'error_message' => $errorMessage,
                        ]);
                        if ($gu && isset($gu['uuid'])) {
                            $syncResultItems[] = [
                                'gate_user_uuid' => $gu['uuid'],
                                'status' => 'conflict',
                                'external_user_id' => null,
                                'error_code' => $errorCode,
                                'error_message' => $errorMessage,
                            ];
                        }
                        break;

                    case 'local_only':
                        $item->update([
                            'selected_action' => 'manual_review',
                            'result_status' => 'skipped',
                        ]);
                        break;
                }
            }

            $run->update([
                'status' => 'applied',
                'applied_at' => now(),
            ]);
        });

        // Report results back to Gate SSO outside DB transaction
        if (! empty($syncResultItems)) {
            $reported = $this->client->sendSyncResults($syncResultItems);
            if ($reported) {
                $run->update([
                    'status' => 'completed',
                    'reported_at' => now(),
                ]);
            } else {
                $run->update([
                    'status' => 'report_pending',
                ]);
            }
        } else {
            $run->update([
                'status' => 'completed',
                'reported_at' => now(),
            ]);
        }

        return $run->fresh();
    }

    public function retryReport(GateSyncRun $run): bool
    {
        $run->load('items');
        $items = $this->buildReportItems($run);

        if ($items === [] || ! $this->client->sendSyncResults($items)) {
            return false;
        }

        $run->update([
            'status' => 'completed',
            'reported_at' => now(),
            'error_message' => null,
        ]);

        return true;
    }

    private function buildReportItems(GateSyncRun $run): array
    {
        return $run->items
            ->filter(fn (GateSyncItem $item) => $item->gate_user_uuid && in_array($item->result_status, ['success', 'skipped'], true))
            ->map(function (GateSyncItem $item) {
                $status = match ($item->category) {
                    'access_revoked', 'inactive_in_gate' => 'suspended',
                    'conflict' => 'conflict',
                    default => 'matched',
                };

                return array_filter([
                    'gate_user_uuid' => $item->gate_user_uuid,
                    'status' => $status,
                    'external_user_id' => $item->external_user_id,
                    'error_code' => $item->error_code,
                    'error_message' => $item->error_message,
                ], fn ($value) => $value !== null);
            })
            ->values()
            ->all();
    }
}
