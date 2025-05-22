<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArtikelModel extends Model
{
    use HasFactory;

    protected $table = 'artikel';

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'image',
        'verifikasi_admin',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
