<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name','subject_id','page_title','page_text','page_file','main_product_id'];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
    public function subcategories()
    {
        return $this->hasMany(SubCategory::class);
    }

    public function products()
    {
        return $this->products_withimage();
        return $this->hasManyThrough(Product::class , SubCategory::class , 'category_id' , 'subcategory_id' , 'id' , 'id');
    }

    public function products_withimage()
    {
        return $this
            ->hasManyThrough(Product::class , SubCategory::class , 'category_id' , 'subcategory_id' , 'id' , 'id')
            ->with('images' , function ($query) {
                $query->where('order', '=' , 1);
            })
            ->with('favourites');
    }
}
