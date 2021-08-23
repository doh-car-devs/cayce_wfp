<?php

use Illuminate\Database\Seeder;
use App\Procurement_mode;

class ProcurementModesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('procurement_modes')->delete();
        $file = File::get("database/libraries/procurement_modes.csv");
        $array = explode("\r\n",$file);
        unset($array[0]);
        foreach ($array as $obj) {
            if ($obj !== "") {
                $data = explode(",",$obj);
                Procurement_mode::create(array(
                'id' => $data[0],
                'parent_mode' => $data[1],
                'mode' => $data[2],
                ));
            }else{
                break;
            }
        }
    }
}
