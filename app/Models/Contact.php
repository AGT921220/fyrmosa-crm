<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    protected $fillable = [
        'client_id',
        'first_name',
        'last_name',
        'puesto',
        'email',
        'comentario',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function phones(): HasMany
    {
        return $this->hasMany(ContactPhone::class)->orderBy('id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function primaryPhone(): string
    {
        return (string) ($this->phones->first()?->phone ?? '');
    }

    public function phonesList(): string
    {
        return $this->phones->pluck('phone')->filter()->implode(' / ');
    }
}
