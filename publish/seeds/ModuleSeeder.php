<?php

use Illuminate\Database\Seeder;
use Symfony\Component\Console\Output\ConsoleOutput as Output;

class ModuleSeeder extends Seeder
{
    public function run() {
        $output = new Output;
        DB::table('module')->truncate();

        $module = [
                    ["section" =>"authorization","name" =>"user", "url" =>"admin/users"],
                    ["section" =>"authorization","name" =>"role", "url" =>"admin/roles"],
                    ["section" =>"authorization","name" =>"permission", "url" =>"admin/permissions"],
                    ["section" =>"authorization","name" =>"assign permission", "url" =>"admin/give-role-permissions"],
                    ["section" =>"tools", "name" =>"crud generator", "url" =>"generator/transaction"]    
                ];

        foreach ($module as $key=>$value) {
            DB::table('module')->insert([
                'name' => $value['name'],
                'section' => $value['section'],
                'url' => $value['url']
            ]);
        }
        
        $output->writeln('<info>Seeds Module Finish</info>');
    }
}

