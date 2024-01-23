<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Department::create(['name' => 'it']);
        Department::create(['name' => 'technic']);
        Department::create(['name' => 'commercial']);
        Department::create(['name' => 'operation']);
        Department::create(['name' => 'finance_administration']);
        Department::create(['name' => 'conformite']);
    
    }
}
