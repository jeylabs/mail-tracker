<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSentEmailsUrlClickedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sent_emails_url_clicked', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sent_email_id')->unsigned();
            $table->foreign('sent_email_id')->references('id')->on('sent_emails')->onDelete('cascade');
            $table->string('url');
            $table->char('hash',32);
            $table->integer('clicks')->default('1');
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
        Schema::drop('sent_emails_url_clicked');
    }
}
