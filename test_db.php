<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (App\Models\Employee::all() as $e) {
    echo "ID: $e->id, Name: $e->name, Last Name: $e->last_name\n";
}

$start = current(App\Models\Attendance::all()->toArray());
echo "Sample Attendance:\n";
print_r($start);
