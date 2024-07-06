<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'description', 'parent_id'];

    // Parent relationship
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Children relationship (recursive)
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->with('children');
    }

    // Products relationship
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
