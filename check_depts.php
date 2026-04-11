<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;

echo "Departments: " . Department::count() . PHP_EOL;
echo "Employees: " . Employee::count() . PHP_EOL;
echo "Positions: " . Position::count() . PHP_EOL;
echo "---" . PHP_EOL;

foreach(Department::all() as $d) {
    $ec = Employee::where('department', $d->name)->count();
    $vc = Position::where('department', $d->name)->where('status','vacant')->count();
    $fc = Position::where('department', $d->name)->where('status','filled')->count();
    $tp = $vc + $fc;
    $fr = $tp > 0 ? round(($fc / $tp) * 100) : ($ec > 0 ? 100 : 0);
    echo $d->name . " => emp:" . $ec . " vac:" . $vc . " filled:" . $fc . " fillRate:" . $fr . "%" . PHP_EOL;
}

echo "---" . PHP_EOL;
echo "Employee departments: " . PHP_EOL;
foreach(Employee::select('department')->distinct()->get() as $e) {
    echo "  - '" . $e->department . "' (" . Employee::where('department', $e->department)->count() . ")" . PHP_EOL;
}
echo "Position departments: " . PHP_EOL;
foreach(Position::select('department')->distinct()->get() as $p) {
    echo "  - '" . $p->department . "' (" . Position::where('department', $p->department)->count() . ")" . PHP_EOL;
}
