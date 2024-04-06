<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
// use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    // use SoftDeletes;

    protected $table='product';
    
    protected $fillable = [
        'title',
        'description',
        'image',
        'category',
        'price'
    ];
    // public function __get($key)
    // {
    //     if (!isset($this->$key)) {
    //         throw new \Exception("Property '$key' does not exist in Product model.");
    //     }
    //     return $this->$key;
    // }
}
