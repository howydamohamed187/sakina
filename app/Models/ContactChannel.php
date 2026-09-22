<?php

namespace App\Models;

use App\Support\ContactTypes;
use App\Support\PhoneNumber;
use Database\Factories\ContactChannelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactChannel extends Model
{
    /** @use HasFactory<ContactChannelFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'contact_type_id',
        'type',
        'value',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function contactType(): BelongsTo
    {
        return $this->belongsTo(ContactType::class);
    }

    public function kind(): string
    {
        return $this->contactType?->kind ?? $this->type;
    }

    public function formattedValue(): string
    {
        if (ContactTypes::isPhone($this->kind())) {
            return PhoneNumber::format($this->value) ?? $this->value;
        }

        return $this->value;
    }
}
