<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class ProductionAccountSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('NC_ERP_INITIAL_PASSWORD');

        if (! is_string($password) || trim($password) === '') {
            throw new RuntimeException('NC_ERP_INITIAL_PASSWORD must be configured before seeding production accounts.');
        }

        foreach ([
            ['产品部', 'market_research', '745551014@qq.com'],
            ['运营部', 'website_operations', '1439501115@qq.com'],
            ['创意部', 'content_creative', '631247688@qq.com'],
            ['流量部', 'traffic_growth', '593937688@qq.com'],
        ] as [$name, $code, $email]) {
            $department = Department::query()->updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'is_active' => true],
            );

            User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'department_id' => $department->id,
                    'role' => 'manager',
                    'password' => Hash::make($password),
                ],
            );
        }

        User::query()->updateOrCreate(
            ['email' => 'szxshop00@gmail.com'],
            [
                'name' => '系统管理员',
                'department_id' => null,
                'role' => 'administrator',
                'password' => Hash::make($password),
            ],
        );
    }
}
