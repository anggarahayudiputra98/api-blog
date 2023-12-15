<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $table = 'tag';
    protected $fillable = [
        'title',
        'status'
    ];

    public function Articles(){
        return $this->belongsToMany(Article::class, 'article_tag');
    }
}
