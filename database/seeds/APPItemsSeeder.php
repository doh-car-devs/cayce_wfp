<?php

use Illuminate\Database\Seeder;
use App\APP_Item;

class APPItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('app_items')->delete();
        $json = File::get("database/libraries/APP_Fixed.json");
        $data = json_decode($json);

        foreach ($data as $obj) {
            APP_Item::create(array(
            'id' => $obj->ID,
            'APP_DBM_ID' => $obj->APP_DBM_ID,
            'Slug' => $obj->Slug,
            'Item' => $obj->Item,
            'Unit_of_measure' => $obj->unit_of_measure,
            'Type' => $obj->Type,
            'Part' => $obj->Part,
            'Price' => $obj->Price,
          ));
        }
    }
}
