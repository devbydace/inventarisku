<?php

namespace App\Repositories\Interfaces;

use App\Models\AuditTrail;
use Illuminate\Database\Eloquent\Collection;

interface AuditTrailRepositoryInterface
{
    public function log(string $entityType, int $entityId, string $action, ?array $oldValues, ?array $newValues): AuditTrail;
    public function findByEntity(string $entityType, int $entityId): Collection;
    public function findByUser(int $userId): Collection;
    public function findByDateRange(string $startDate, string $endDate): Collection;
}