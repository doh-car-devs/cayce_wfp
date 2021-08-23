<?php

use Illuminate\Database\Seeder;
use App\Fund_source_type;

class FundSourceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('fund_source_types')->delete();
        $file = File::get("database/libraries/fund_source_types.csv");
        $array = explode("\r\n",$file);
        unset($array[0]);
        foreach ($array as $obj) {
            if ($obj !== "") {
                $data = explode(",",$obj);
                Fund_source_type::create(array(
                'id' => $data[0],
                'type' => $data[1],
                'type_abbr' => $data[2],
                ));
            }else{
                break;
            }
        }
    }
}
