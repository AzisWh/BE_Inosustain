<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogBeritaModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'blogberita';

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'image',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function images()
    {
        return $this->hasMany(BlogImageModel::class, 'blog_id');
    }
}
