<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $table = 'article';
    protected $fillable = [
        'title',
        'content',
        'image',
        'status'
    ];

    public function Tags(){
        return $this->belongsToMany(Tag::class, 'article_tag');
    }
}
