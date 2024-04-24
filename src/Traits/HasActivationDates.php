<?php

namespace CubeAgency\FilamentPageManager\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait HasActivationDates
{
    public function getActivateAtAttribute($value): ?Carbon
    {
        return is_null($value) ? null : Carbon::createFromFormat('Y-m-d H:i:s', $value);
    }

    public function getExpireAtAttribute($value): ?Carbon
    {
        return is_null($value) ? null : Carbon::createFromFormat('Y-m-d H:i:s', $value);
    }

    public function getActiveAttribute(): bool
    {
        return $this->hasActivated() && !$this->hasExpired();
    }

    public function scopeActive(Builder $query): Builder
    {
        $table = $this->getTable();
        $now = date('Y-m-d H:i:s');

        return $query->where($table . '.activate_at', '<=', $now)
            ->where(function (Builder $query) use ($table, $now) {
                return $query->where($table . '.expire_at', '>=', $now)
                    ->orWhereNull($table . '.expire_at');
            });
    }

    public function hasExpired(): bool
    {
        return !is_null($this->expire_at) && $this->expire_at->isPast();
    }

    public function hasActivated(): bool
    {
        return !is_null($this->activate_at) && $this->activate_at->isPast();
    }
}
