<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'is_filterable',
        'is_required',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_filterable' => 'boolean',
        'is_required' => 'boolean',
    ];

    /**
     * Get the values for the attribute.
     */
    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
    }
}