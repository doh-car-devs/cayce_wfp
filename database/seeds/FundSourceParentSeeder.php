<?php

use Illuminate\Database\Seeder;
use App\Fund_source_parent;

class FundSourceParentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('fund_source_parents')->delete();
        $file = File::get("database/libraries/fund_source_parents.csv");
        $array = explode("\r\n",$file);
        unset($array[0]);
        foreach ($array as $obj) {
            if ($obj !== "") {
                $data = explode(",",$obj);
                Fund_source_parent::create(array(
                'id' => $data[0],
                'parent_type' => $data[1],
                'parent_type_abbr' => $data[2],
                ));
            }else{
                break;
            }
        }
    }
}
