<?php

use Illuminate\Database\Seeder;
use App\Fund_source;

class FundSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('fund_sources')->delete();
        $file = File::get("database/libraries/fund_sources.csv");
        $array = explode("\r\n",$file);
        unset($array[0]);
        foreach ($array as $obj) {
            if ($obj !== "") {
                $data = explode(",",$obj);
                Fund_source::create(array(
                'id' => $data[0],
                'parent_id' => $data[1],
                'type_id' => $data[2],
                'source_abbr' => $data[3],
                'source_name' => $data[4],
                ));
            }else{
                break;
            }
        }
    }
}
