<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuranTafsir extends Model
{
    protected $fillable = [
        'sura',
        'aya',
        'locale',
        'text',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'sura' => 'integer',
            'aya' => 'integer',
        ];
    }
}
