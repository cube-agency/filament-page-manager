<?php

namespace CubeAgency\FilamentPageManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagePreview extends Model
{
    protected $fillable = [
        'page_id',
        'data',
        'token',
        'expires_at',
    ];

    protected $casts = [
        'data' => 'array',
        'expires_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = config('filament-page-manager.previews.table_name');

        parent::__construct($attributes);
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
