<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$user = \App\Models\User::where('username', 'admin')->first();
$user->role_ids = ['admin'];
$user->save();
echo "Updated roles for user admin\n";
