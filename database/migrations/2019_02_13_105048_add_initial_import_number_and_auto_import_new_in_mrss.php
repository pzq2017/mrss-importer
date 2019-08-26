<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInitialImportNumberAndAutoImportNewInMrss extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mrss', function (Blueprint $table) {
            $table->integer('initial_import_number')->default(0)->after('url')->comment('import number for the first time, -1 means import all');
            $table->integer('auto_import_new')->default(0)->after('initial_import_number')->comment('0-manual, 1-auto');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mrss', function (Blueprint $table) {
            $table->dropColumn(['auto_import_new', 'initial_import_number']);
        });
    }
}
