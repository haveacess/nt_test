<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppsModel extends Model
{
    use HasFactory;

    protected $table = 'apps';
    protected $fillable = ['id_category', 'name'];
    public $timestamps = false;
}
