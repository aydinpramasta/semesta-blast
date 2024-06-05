<?php

namespace App\Models;

use App\Enums\RecipientStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'content',
        'status',
    ];

    protected $casts = [
        'status' => RecipientStatus::class,
    ];
}
