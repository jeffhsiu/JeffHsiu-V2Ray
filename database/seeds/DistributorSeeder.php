<?php

use App\Models\Order\Distributor;
use Illuminate\Database\Seeder;

class DistributorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ( !Distributor::where('name', 'FastRabbit')->exists()) {
            Distributor::create(['name' => 'FastRabbit']);
        }
    }
}
