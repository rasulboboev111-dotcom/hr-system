<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\Auditable;

class Position extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'title',
        'department',
        'salary_range',
        'experience_level',
        'status',
        'required_skills',
    ];
}
