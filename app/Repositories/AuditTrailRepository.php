<?php

namespace App\Repositories;

use App\Models\AuditTrail;
use App\Repositories\Interfaces\AuditTrailRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class AuditTrailRepository implements AuditTrailRepositoryInterface
{
    public function log(string $entityType, int $entityId, string $action, ?array $oldValues, ?array $newValues): AuditTrail
    {
        return AuditTrail::create([
            'user_id' => Auth::id(),
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }

    public function findByEntity(string $entityType, int $entityId): Collection
    {
        return AuditTrail::where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->get();
    }

    public function findByUser(int $userId): Collection
    {
        return AuditTrail::where('user_id', $userId)->get();
    }

    public function findByDateRange(string $startDate, string $endDate): Collection
    {
        return AuditTrail::whereBetween('created_at', [$startDate, $endDate])->get();
    }
}