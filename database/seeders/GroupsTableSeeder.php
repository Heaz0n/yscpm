<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GroupsTableSeeder extends Seeder
{
    public function run()
    {
        // Отключаем проверку внешних ключей
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Очищаем таблицу
        DB::table('Groups')->truncate();

        $groups = [
            [
                'id' => 1,
                'direction_id' => 2,
                'group_name' => '1121б',
                'notes' => '',
                'updated_at' => '2026-01-25 18:51:32',
            ],
            [
                'id' => 2,
                'direction_id' => 2,
                'group_name' => 'ИВТ31б',
                'notes' => '',
                'updated_at' => '2026-01-25 18:51:41',
            ],
            [
                'id' => 3,
                'direction_id' => 2,
                'group_name' => 'ИВТ41б',
                'notes' => '',
                'updated_at' => '2026-01-25 18:51:49',
            ],
            [
                'id' => 4,
                'direction_id' => 2,
                'group_name' => 'ИВТ51б',
                'notes' => '',
                'updated_at' => '2026-06-01 07:58:54',
            ],
            [
                'id' => 5,
                'direction_id' => 3,
                'group_name' => '1521б',
                'notes' => '',
                'updated_at' => '2026-01-25 18:52:02',
            ],
            [
                'id' => 6,
                'direction_id' => 3,
                'group_name' => 'ПИ31б',
                'notes' => '',
                'updated_at' => '2026-01-25 18:52:08',
            ],
            [
                'id' => 7,
                'direction_id' => 3,
                'group_name' => 'ПИ41б',
                'notes' => '',
                'updated_at' => '2026-01-25 18:52:14',
            ],
            [
                'id' => 8,
                'direction_id' => 3,
                'group_name' => 'ПИ51б',
                'notes' => '',
                'updated_at' => '2026-01-25 18:52:20',
            ],
            [
                'id' => 9,
                'direction_id' => 4,
                'group_name' => 'ИБ31б',
                'notes' => '',
                'updated_at' => '2026-01-25 18:52:27',
            ],
            [
                'id' => 10,
                'direction_id' => 4,
                'group_name' => 'ИБ41б',
                'notes' => '',
                'updated_at' => '2026-01-25 18:52:31',
            ],
            [
                'id' => 11,
                'direction_id' => 4,
                'group_name' => 'ИБ51б',
                'notes' => '',
                'updated_at' => '2026-01-25 18:52:40',
            ],
            [
                'id' => 12,
                'direction_id' => 5,
                'group_name' => 'ПМИ41м',
                'notes' => '',
                'updated_at' => '2026-01-25 18:52:59',
            ],
            [
                'id' => 13,
                'direction_id' => 5,
                'group_name' => 'ПМИ51м',
                'notes' => '',
                'updated_at' => '2026-01-25 18:53:04',
            ],
        ];

        // Вставляем данные
        DB::table('Groups')->insert($groups);

        // Восстанавливаем автоинкремент
        DB::statement('ALTER TABLE Groups AUTO_INCREMENT = ' . (count($groups) + 1));

        // Включаем проверку внешних ключей
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}