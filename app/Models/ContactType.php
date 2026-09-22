<?php

namespace App\Models;

use App\Support\ContactTypes;
use Database\Factories\ContactTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactType extends Model
{
    /** @use HasFactory<ContactTypeFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'kind',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function suggestions(): HasMany
    {
        return $this->hasMany(ContactChannel::class);
    }

    public function isPhone(): bool
    {
        return ContactTypes::isPhone($this->kind);
    }

    public function kindLabel(): string
    {
        return ContactTypes::label($this->kind);
    }
}
