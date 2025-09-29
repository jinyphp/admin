<?php

namespace Jiny\Admin\Database\Seeders;

use Illuminate\Database\Seeder;
use Jiny\Admin\Models\AdminTemplate;

class AdminTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 50 admin templates
        AdminTemplate::factory()
            ->count(50)
            ->create();
    }
}
