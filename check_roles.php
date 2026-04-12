<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
foreach(\App\Models\User::all() as $u) {
    echo $u->id . ': ' . $u->username . ' - roles: ' . json_encode($u->role_ids) . PHP_EOL;
}
