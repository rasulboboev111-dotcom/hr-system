<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\Auditable;

class Department extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'name',
    ];
}
