<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateColumnCategoryId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categories_not_found', function($table) {
            $table->integer('category_id')->unsigned()->nullable()->after('status');
            $table->foreign('category_id')
                ->references('id')
                ->on('badge_categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories_not_found', function($table) {
            $table->dropForeign('category_id');
            $table->dropColumn('category_id');
        });
    }
}
