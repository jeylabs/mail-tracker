<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMessageIdToSentEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sent_emails', function(Blueprint $table) {
            $table->string('message_id')->nullable()->unique();
            $table->text('meta');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sent_emails', function(Blueprint $table) {
            $table->dropColumn('message_id');
            $table->dropColumn('meta');
        });
    }
}
