<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FolioSequence extends Model
{
    protected $fillable = [
        'prefix',
        'current',
    ];
}
