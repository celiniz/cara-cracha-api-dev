<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons_history', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('coupon_id')->unsigned()->nullable();
            $table->foreign('coupon_id')
                ->references('id')
                ->on('coupons');
            
            $table->integer('badge_id')->unsigned()->nullable();
            $table->foreign('badge_id')
                ->references('id')
                ->on('badges');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coupons_history');
    }
}
