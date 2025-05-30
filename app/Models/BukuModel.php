<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BukuModel extends Model
{
    use HasFactory;

    protected $table = 'tabelbuku';

    protected $fillable = [
        'title',
        'author',
        'penerbit',
        'tahun',
        'doi',
        'file',
      ];
  }