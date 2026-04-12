<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\Auditable;

class PayrollRecord extends Model
{
    use Auditable;

    protected $fillable = ['employee_id', 'month_year', 'salary', 'bonus', 'calculated_total'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
