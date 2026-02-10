<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\StaffRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = [
            'Admin',
            'Project Manager',
            'Developer',
            'Designer',
            'QA',
            'Support',
        ];

        $names = [
            'Ahmad Faiz',
            'Nur Aisyah',
            'Muhammad Aiman',
            'Siti Nurhaliza',
            'Amirul Hakim',
            'Nurin Syafiqah',
            'Haziq Imran',
            'Nurul Izzah',
            'Syafiq Haziq',
            'Aina Sofea',
            'Hakim Firdaus',
            'Farah Nabila',
            'Irfan Hakimi',
            'Nabila Huda',
            'Afiq Azman',
            'Nur Syuhada',
            'Ammar Zulkifli',
            'Siti Aishah',
            'Danial Ashraf',
            'Nurin Aqilah',
        ];

        for ($i = 1; $i <= 20; $i++) {
            $name = $names[$i - 1];
            $emailLocal = strtolower(str_replace(' ', '.', $name));
            $phone = sprintf('01%d-%04d%04d', rand(0, 9), rand(0, 9999), rand(0, 9999));

            $staff = Staff::create([
                'StaffNAME' => $name,
                'StaffPHONE' => $phone,
                'StaffEMAIL' => "{$emailLocal}{$i}@example.com",
                'StaffPASSWORD' => Hash::make('password'),
            ]);

            $roleType = $roles[($i - 1) % count($roles)];

            StaffRole::create([
                'StaffID' => $staff->id,
                'RoleTYPE' => $roleType,
                'RoleDESC' => "{$roleType} role",
                'RolePRO' => null,
            ]);
        }
    }
}
