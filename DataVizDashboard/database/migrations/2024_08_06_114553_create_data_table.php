<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataTable extends Migration
{
    public function up()
    {
        Schema::create('data', function (Blueprint $table) {
            $table->id();
            $table->integer('end_year')->nullable();
            $table->decimal('citylng', 10, 8)->nullable();
            $table->decimal('citylat', 10, 8)->nullable();
            $table->integer('intensity')->nullable();
            $table->string('sector')->nullable();
            $table->text('topic')->nullable();
            $table->text('insight')->nullable();
            $table->string('swot')->nullable();
            $table->text('url')->nullable();
            $table->string('region')->nullable();
            $table->integer('start_year')->nullable();
            $table->string('impact')->nullable();
            $table->dateTime('added')->nullable();
            $table->dateTime('published')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->integer('relevance')->nullable();
            $table->string('pestle')->nullable();
            $table->string('source')->nullable();
            $table->text('title')->nullable();
            $table->integer('likelihood')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('data');
    }
}