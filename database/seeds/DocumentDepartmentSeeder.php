<?php

use Illuminate\Database\Seeder;

class DocumentDepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('document_department')->insert([
            'document_id' => 1,
            'department_id' => 1,
            'is_approved' => 1,
        ]);
    }
}
