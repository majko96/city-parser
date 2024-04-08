<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CityDetail extends Model
{
    use HasFactory;
    protected $fillable = ['mayor_name', 'address', 'phone', 'mobile', 'fax', 'email', 'website', 'imagePath', 'name'];
}
