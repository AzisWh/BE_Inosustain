<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogImageModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'blog_image';

    protected $fillable = [
        'blog_id',
        'image',
    ];

    public function blog()
    {
        return $this->belongsTo(BlogBeritaModel::class, 'blog_id');
    }
}
