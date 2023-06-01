<?php

use Illuminate\Support\Str;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTableProductsWithSlug extends Migration
{
    public function up()
    {
        $records = DB::table('products')->get();

        foreach ($records as $record) {
            $slug = Str::slug($record->name);
            DB::table('products')->where('id', $record->id)->update(['slug' => $slug]);
        }
    }

    public function down()
    {
        DB::table('products')->update(['slug' => null]);
    }
}
