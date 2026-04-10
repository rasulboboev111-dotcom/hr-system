<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = ['employee_id', 'date_key', 'shift_type'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
