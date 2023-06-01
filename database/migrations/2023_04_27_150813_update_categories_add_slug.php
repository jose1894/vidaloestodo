<?php
use Illuminate\Support\Str;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCategoriesAddSlug extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    

    $categories = DB::table('categories')->get();

    foreach ($categories as $category) {
        $slug = Str::slug($category->name, '-') . '-'. $category->id;
        DB::table('categories')
            ->where('id', $category->id)
            ->update(['slug' => $slug]);
    }
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
