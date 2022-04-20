<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('appointments', function (Blueprint $table) {
			$table->id();
			$table->integer('id_user');
			$table->integer('id_doctor');
			$table->integer('id_city');
			$table->integer('id_clinic');
			$table->integer('id_field');
			$table->timestamp('start_date')->nullable();
			$table->timestamp('end_date')->nullable();
			$table->smallInteger('active')->default(1);
			$table->float('amount', 8, 2);
			$table->boolean('review_done')->default(false);
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
		Schema::dropIfExists('appointments');
	}
};
