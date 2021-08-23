<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call('FundSourceSeeder');
        $this->call('FundSourceTypeSeeder');
        $this->call('FundSourceParentSeeder');
        $this->command->info('============Fund Source Tables Seeded!============');
        $this->call('APPItemsSeeder');
        $this->command->info('============APP Items Tables Seeded!============');
        $this->call('ProcurementModesSeeder');
        $this->command->info('============Modes of Procurement Tables Seeded!============');
    }
}
