<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProviderDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('provider_id');
            $table->unsignedBigInteger('document_id');
            $table->string('url');
            $table->string('unique_id')->nullable();
            $table->enum('status', ['ASSESSING', 'ACTIVE']);
            $table->timestamp('expires_at')->nullable();
            
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('provider_id')
                ->references('id')
                ->on('providers');

            $table->foreign('document_id')
                ->references('id')
                ->on('documents');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provider_documents');
    }
}
