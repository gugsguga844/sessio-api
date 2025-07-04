<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TimeBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'start_time',
        'end_time',
        'color',
    ];
}
