<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Department;

class AppSeeder extends Seeder
{
    public function run()
    {
        // 1. Seed Admin User
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@siizi.ru'],
            [
                'username' => 'admin',
                'first_name' => 'Admin',
                'last_name' => 'System',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'is_active' => true,
            ]
        );

        // 2. Seed Departments based on original mocks
        $departments = [
            ['name' => 'Engineering'],
            ['name' => 'Design'],
            ['name' => 'Human Resources'],
            ['name' => 'Marketing'],
            ['name' => 'Sales']
        ];
        foreach ($departments as $dept) {
            Department::updateOrCreate(['name' => $dept['name']], $dept);
        }

        // 2. Seed Employees exactly mimicking mock-data.ts
        $employees = [
            [
                'name' => 'Алексей',
                'last_name' => 'Иванов',
                'patronymic' => 'Сергеевич',
                'email' => 'alex.ivanov@siizi.ru',
                'role' => 'Senior Frontend Developer',
                'department' => 'Engineering',
                'status' => 'active',
                'joined_date' => '2021-03-15',
                'birth_date' => '1992-05-12',
                'gender' => 'male',
                'marital_status' => 'Муҷаррад',
                'nationality' => 'Тоҷик',
                'region_of_birth' => 'Душанбе',
                'current_location' => 'Душанбе',
                'skills' => json_encode(['React', 'TypeScript', 'Node.js']),
                'availability' => 100,
                'salary' => 15000
            ],
            [
                'name' => 'Елена',
                'last_name' => 'Смирнова',
                'patronymic' => 'Викторовна',
                'email' => 'elena.s@siizi.ru',
                'role' => 'Product Designer',
                'department' => 'Design',
                'status' => 'active',
                'joined_date' => '2022-01-10',
                'birth_date' => '1995-08-22',
                'gender' => 'female',
                'marital_status' => 'Оиладор',
                'nationality' => 'Рус',
                'region_of_birth' => 'Москва',
                'current_location' => 'Душанбе',
                'skills' => json_encode(['Figma', 'UI/UX', 'Prototyping']),
                'availability' => 100,
                'salary' => 12000
            ],
            [
                'name' => 'Дмитрий',
                'last_name' => 'Петров',
                'patronymic' => 'Игоревич',
                'email' => 'dmitry.p@siizi.ru',
                'role' => 'Backend Engineer',
                'department' => 'Engineering',
                'status' => 'on_leave',
                'joined_date' => '2020-11-20',
                'birth_date' => '1988-11-30',
                'gender' => 'male',
                'marital_status' => 'Оиладор',
                'nationality' => 'Рус',
                'region_of_birth' => 'СПб',
                'current_location' => 'Хуҷанд',
                'skills' => json_encode(['Python', 'PostgreSQL', 'Docker']),
                'availability' => 50,
                'salary' => 14000
            ],
            [
                'name' => 'Марина',
                'last_name' => 'Кузнецова',
                'patronymic' => 'Александровна',
                'email' => 'marina.k@siizi.ru',
                'role' => 'HR Manager',
                'department' => 'Human Resources',
                'status' => 'active',
                'joined_date' => '2023-05-12',
                'birth_date' => '1994-02-15',
                'gender' => 'female',
                'marital_status' => 'Оиладор',
                'nationality' => 'Тоҷик',
                'region_of_birth' => 'Кӯлоб',
                'current_location' => 'Душанбе',
                'skills' => json_encode(['Recruiting', 'Management', 'Legal']),
                'availability' => 100,
                'salary' => 10000
            ],
            [
                'name' => 'Иван',
                'last_name' => 'Сергеев',
                'patronymic' => 'Павлович',
                'email' => 'ivan.s@siizi.ru',
                'role' => 'Marketing Specialist',
                'department' => 'Marketing',
                'status' => 'active',
                'joined_date' => '2022-09-01',
                'birth_date' => '1997-12-05',
                'gender' => 'male',
                'marital_status' => 'Муҷаррад',
                'nationality' => 'Рус',
                'region_of_birth' => 'Новосибирск',
                'current_location' => 'Душанбе',
                'skills' => json_encode(['SEO', 'Content Writing', 'Ads']),
                'availability' => 100,
                'salary' => 9000
            ],
            [
                'name' => 'Низом',
                'last_name' => 'Қосимов',
                'patronymic' => 'Абдуллоевич',
                'email' => 'nizom.q@siizi.ru',
                'role' => 'Fullstack Dev',
                'department' => 'Engineering',
                'status' => 'retired',
                'joined_date' => '2019-01-15',
                'birth_date' => '1985-06-10',
                'gender' => 'male',
                'marital_status' => 'Оиладор',
                'nationality' => 'Тоҷик',
                'region_of_birth' => 'Душанбе',
                'current_location' => 'Душанбе',
                'skills' => json_encode(['PHP', 'Laravel', 'Vue.js']),
                'availability' => 100,
                'salary' => 13000
            ]
        ];

        foreach ($employees as $emp) {
            Employee::updateOrCreate(['email' => $emp['email']], $emp);
        }

        // 3. Seed Positions
        $positions = [
            [
                'title' => 'QA Engineer',
                'department' => 'Engineering',
                'status' => 'vacant',
                'salary' => 135000,
                'required_skills' => json_encode(['Selenium', 'Cypress', 'Testing'])
            ],
            [
                'title' => 'Content Creator',
                'department' => 'Marketing',
                'status' => 'filled',
                'salary' => 90000,
                'required_skills' => json_encode(['Copywriting', 'Canva'])
            ],
            [
                'title' => 'Sales Lead',
                'department' => 'Sales',
                'status' => 'on_hold',
                'salary' => 150000,
                'required_skills' => json_encode(['B2B Sales', 'CRM', 'Leadership'])
            ],
            [
                'title' => 'Data Analyst',
                'department' => 'Engineering',
                'status' => 'vacant',
                'salary' => 145000,
                'required_skills' => json_encode(['Python', 'SQL', 'Tableau'])
            ]
        ];

        foreach ($positions as $pos) {
            Position::updateOrCreate(['title' => $pos['title']], $pos);
        }
    }
}
