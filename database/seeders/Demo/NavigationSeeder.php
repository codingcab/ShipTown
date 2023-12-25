<?php

namespace Database\Seeders\Demo;

use App\Models\NavigationMenu;
use Illuminate\Database\Seeder;

class NavigationSeeder extends Seeder
{
    public function run()
    {
        NavigationMenu::query()->create([
            'name' => 'Status: packing',
            'url' => '/autopilot/packlist?status=packing',
            'group' => 'packlist'
        ]);
    }
}
