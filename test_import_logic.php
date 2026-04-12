<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = new \App\Http\Controllers\TimesheetController();

// Simulate exported csv line
$exported = [
    "Алексей Иванов", "P", "", "L", "A"
];
// Wait, export actually outputs 1 name, 31 dates, 1 total = 33 columns.
$row = ["Алексей Иванов"];
for ($i=1; $i<=31; $i++) {
    $row[] = ($i%2 == 0) ? 'P' : '';
}
$row[] = "15"; // Total

$data = [$row];

foreach ($data as $r) {
    if (count($r) < 32 || empty($r[0])) {
        echo "Row skipped! Count: " . count($r) . ", Col 0: " . $r[0] . "\n";
        continue;
    }
    
    $nameStr = trim($r[0]);
    $nameParts = explode(' ', $nameStr, 2);
    $firstName = $nameParts[0];
    $lastName = $nameParts[1] ?? null;

    $employee = App\Models\Employee::firstOrCreate(
        ['name' => $firstName, 'last_name' => $lastName],
        ['status' => 'Active', 'department' => null]
    );
    echo "Employee resolved: " . $employee->id . "\n";
    
    for ($d = 1; $d <= 31; $d++) {
        $status = trim($r[$d] ?? '');
        if ($status) {
            $att = App\Models\Attendance::updateOrCreate(
                ['employee_id' => $employee->id, 'date_key' => $d],
                ['status' => strtoupper($status)]
            );
            echo "Day $d: $status updated (ID: ".$att->id.")\n";
        }
    }
}
