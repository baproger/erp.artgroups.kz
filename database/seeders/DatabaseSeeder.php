<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DepartmentSeeder::class);
        $this->call(KpiSeeder::class);

        // CEO
        User::firstOrCreate(['email' => 'ceo@artgroups.kz'], [
            'name'      => 'Генеральный Директор',
            'password'  => Hash::make('password123'),
            'role'      => 'ceo',
            'is_active' => true,
        ]);

        // Commercial Director
        User::firstOrCreate(['email' => 'director@artgroups.kz'], [
            'name'      => 'Коммерческий Директор',
            'password'  => Hash::make('password123'),
            'role'      => 'commercial_director',
            'is_active' => true,
        ]);

        // Department managers
        $deptRoles = [
            'finance'    => ['Руководитель финансов', 'finance@artgroups.kz'],
            'sales'      => ['Руководитель продаж',   'sales@artgroups.kz'],
            'marketing'  => ['Руководитель маркетинга','marketing@artgroups.kz'],
            'production' => ['Начальник производства', 'production@artgroups.kz'],
            'surveyors'  => ['Старший замерщик',       'surveyors@artgroups.kz'],
        ];

        foreach ($deptRoles as $slug => [$name, $email]) {
            $dept = Department::where('slug', $slug)->first();
            User::firstOrCreate(['email' => $email], [
                'name'          => $name,
                'password'      => Hash::make('password123'),
                'role'          => $slug,
                'department_id' => $dept?->id,
                'is_active'     => true,
            ]);
        }

        $this->call(KpiPlanSeeder::class);
        $this->call(KpiFactSeeder::class);
        $this->call(BranchSeeder::class);
    }
}
