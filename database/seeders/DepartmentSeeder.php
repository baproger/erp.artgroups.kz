<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Финансы',     'slug' => 'finance',    'sort_order' => 1],
            ['name' => 'Продажи',     'slug' => 'sales',      'sort_order' => 2],
            ['name' => 'Маркетинг',   'slug' => 'marketing',  'sort_order' => 3],
            ['name' => 'Производство','slug' => 'production', 'sort_order' => 4],
            ['name' => 'Замерщики',   'slug' => 'surveyors',  'sort_order' => 5],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(['slug' => $dept['slug']], $dept);
        }
    }
}
