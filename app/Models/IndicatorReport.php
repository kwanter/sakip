<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot model for the indicator_report table.
 *
 * The pivot has a UUID primary key, but plain attach() never fills it,
 * so every attach() failed with a NOT NULL violation. HasUuids generates
 * the id when the relation saves through this class.
 */
class IndicatorReport extends Pivot
{
    use HasUuids;

    protected $table = 'indicator_report';

    public $incrementing = false;
}
