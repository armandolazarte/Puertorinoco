<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermisosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('permisos', function(Blueprint $table)
		{
			$table->increments('id');
			$table->boolean('esAgencia');
			$table->boolean('cuposExtra');
			$table->boolean('DisponibilidadTotalDeEmbarcaciones')->default(0);
			$table->boolean('DisponibilidadTotalDePaseos')->default(0);
			$table->boolean('accesoEdicionDePagina')->default(0);
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
		Schema::drop('permisos');
	}

}