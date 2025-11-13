<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Database\Seeder;

class UserGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // |KB Группы пользователей для управления банковскими платежами
        $groups = [
            [
                'name' => 'VIP Клиенты',
                'code' => 'VIP',
                'description' => 'Пользователи с повышенными лимитами и приоритетной поддержкой.',
                'is_active' => true,
            ],
            [
                'name' => 'Тестовая группа',
                'code' => 'TEST',
                'description' => 'Группа для ручного тестирования сценариев платежей в банке.',
                'is_active' => true,
            ],
            [
                'name' => 'Риск-контроль',
                'code' => 'RISK',
                'description' => 'Пользователи, требующие дополнительной проверки при платежах.',
                'is_active' => true,
            ],
        ];

        $groupInstances = [];

        foreach ($groups as $groupData) {
            $groupInstances[$groupData['code']] = UserGroup::updateOrCreate(
                ['code' => $groupData['code']],
                $groupData
            );
        }

        $admin = User::where('role', 'admin')->first();
        $broker = User::where('role', 'broker')->first();
        $clients = User::where('role', 'client')->get();

        // |KB Назначаем пользователей по группам
        if ($admin && isset($groupInstances['RISK'])) {
            $groupInstances['RISK']->users()->syncWithoutDetaching([
                $admin->id => [
                    'assigned_by' => $admin->id,
                    'comment' => 'Назначено автоматически (администратор контролирует риски)',
                ],
            ]);
        }

        if ($broker && isset($groupInstances['TEST'])) {
            $groupInstances['TEST']->users()->syncWithoutDetaching([
                $broker->id => [
                    'assigned_by' => $admin?->id,
                    'comment' => 'Назначено автоматически (брокер участвует в тестах)',
                ],
            ]);
        }

        if ($clients->isNotEmpty() && isset($groupInstances['VIP'])) {
            $syncPayload = [];
            foreach ($clients as $client) {
                $syncPayload[$client->id] = [
                    'assigned_by' => $admin?->id,
                    'comment' => 'Назначено автоматически (seed)',
                ];
            }

            $groupInstances['VIP']->users()->syncWithoutDetaching($syncPayload);
        }
    }
}


