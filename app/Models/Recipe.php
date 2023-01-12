<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\RecipeContains;
use App\Traits\CafeId;
class Recipe extends Model
{
    use HasFactory,CafeId;

    public function recipeMethods()
    {
        return $this->hasMany(RecipeContains::class, 'recipe_id', 'id');
    }
}
