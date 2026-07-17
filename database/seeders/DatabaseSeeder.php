<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Superadmin;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Superadmin::firstOrCreate(
            ['email' => 'admin@labmanager.com'],
            ['name' => 'Super Admin', 'password' => Hash::make('password')]
        );

        $basic = Plan::firstOrCreate(['name' => 'Basic'], [
            'max_staff'    => 5,
            'max_patients' => 200,
            'max_branches' => 0,
            'pdf_branding' => false,
            'custom_smtp'  => false,
            'analytics'    => false,
            'status'       => 'active',
        ]);

        $pro = Plan::firstOrCreate(['name' => 'Pro'], [
            'max_staff'    => 20,
            'max_patients' => 2000,
            'max_branches' => 0,
            'pdf_branding' => true,
            'custom_smtp'  => true,
            'analytics'    => true,
            'status'       => 'active',
        ]);

        Plan::firstOrCreate(['name' => 'Enterprise'], [
            'max_staff'    => 9999,
            'max_patients' => 9999,
            'max_branches' => 9999,
            'pdf_branding' => true,
            'custom_smtp'  => true,
            'analytics'    => true,
            'status'       => 'active',
        ]);

        // firstOrCreate won't touch pre-existing rows — make sure an already
        // seeded Enterprise plan gets the branches feature.
        Plan::where('name', 'Enterprise')->where('max_branches', 0)->update(['max_branches' => 9999]);

        Tenant::firstOrCreate(['slug' => 'demo-lab'], [
            'plan_id' => $pro->id,
            'name'    => 'Demo Laboratory',
            'email'   => 'demo@lab.com',
            'phone'   => '+1-555-0100',
            'address' => '123 Health Street, Medical City, CA 90210',
            'status'  => 'active',
        ]);
    }
}
