<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            [
                'name'           => 'Vapour Garage - Peñaranda',
                'address'        => 'Padilla St., in front of New Public Market',
                'city'           => 'Peñaranda Nueva Ecija',
                'contact_number' => '09393394394',
                'is_active'      => true,
            ],
            [
                'name'           => 'Vapour Garage - Main (General Tinio)',
                'address'        => 'Purok Camia B, Brgy Rio Chico, beside Petron Gasoline Station',
                'city'           => 'General Tinio Nueva Ecija',
                'contact_number' => '09393394394',
                'is_active'      => true,
            ],
            [
                'name'           => 'Vapour Garage - San Leonardo',
                'address'        => 'Poblacion, 2nd Floor beside Pot-Pot Pandesal',
                'city'           => 'San Leonardo Nueva Ecija',
                'contact_number' => '09393394394',
                'is_active'      => true,
            ],
            [
                'name'           => 'Vapour Garage - Fort Magsaysay',
                'address'        => 'Purok 5, Mapalad, Sta. Rosa, beside Lho-Rhen Hot Malunggay Pandesal',
                'city'           => 'Fort Magsaysay Nueva Ecija',
                'contact_number' => '09393394394',
                'is_active'      => true,
            ],
        ];

        foreach ($branches as $branch) {
            Branch::updateOrCreate(['name' => $branch['name']], $branch);
        }

        $this->command->info('✔ 4 branches seeded.');
    }
}
