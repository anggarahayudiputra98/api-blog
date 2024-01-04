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
        'status',
        'created_by'
    ];

    public function tags(){
        return $this->belongsToMany(Tag::class, 'article_tag');
    }

    public function writter(){
        return $this->hasOne(User::class, 'id','created_by');
    }
}
