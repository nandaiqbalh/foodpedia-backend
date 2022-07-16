<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Transaction extends Model
{
    use HasFactory;
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    // relasi ke tabel food
    public function food()
    {
        return $this->hasOne(Food::class, 'id', 'food_id');
    }

    // relasi ke tabel users
    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    // get value timestamp
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }
}
