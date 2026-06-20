<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchoolsTableSeeder extends Seeder
{
    public function run()
    {
        // Отключаем проверку внешних ключей
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Очищаем таблицу
        DB::table('Schools')->truncate();

        $schools = [
            [
                'code' => 1,
                'name' => 'Инженерная школа цифровых технологий',
                'abbreviation' => 'ИШЦТ',
                'director' => 'Самарина Ольга Владимировна',
                'deputy_director' => 'Шевченко Алеся Сергеевна',
                'notes' => null,
            ],
            [
                'code' => 2,
                'name' => 'Высшая школа гуманитарных наук',
                'abbreviation' => 'ВШГН',
                'director' => null,
                'deputy_director' => null,
                'notes' => null,
            ],
            [
                'code' => 4,
                'name' => 'Высшая школа физической культуры и спорта',
                'abbreviation' => 'ВШФКС',
                'director' => null,
                'deputy_director' => null,
                'notes' => null,
            ],
            [
                'code' => 5,
                'name' => 'Высшая экологическая школа',
                'abbreviation' => 'ВЭШ',
                'director' => null,
                'deputy_director' => null,
                'notes' => null,
            ],
            [
                'code' => 6,
                'name' => 'Высшая школа цифровой экономики',
                'abbreviation' => 'ВШЦЭ',
                'director' => null,
                'deputy_director' => null,
                'notes' => null,
            ],
            [
                'code' => 7,
                'name' => 'Высшая нефтяная школа',
                'abbreviation' => 'ВНШ',
                'director' => null,
                'deputy_director' => null,
                'notes' => null,
            ],
            [
                'code' => 8,
                'name' => 'Высшая школа права',
                'abbreviation' => 'ВШП',
                'director' => null,
                'deputy_director' => null,
                'notes' => null,
            ],
            [
                'code' => 9,
                'name' => 'Политехническая школа',
                'abbreviation' => 'ПШ',
                'director' => null,
                'deputy_director' => null,
                'notes' => null,
            ],
        ];

        // Вставляем данные
        DB::table('Schools')->insert($schools);

        // Восстанавливаем автоинкремент (если таблица использует AUTO_INCREMENT)
        DB::statement('ALTER TABLE Schools AUTO_INCREMENT = ' . (count($schools) + 1));

        // Включаем проверку внешних ключей
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}