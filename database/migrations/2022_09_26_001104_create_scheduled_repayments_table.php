<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduledRepaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scheduled_repayments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loan_id');
            $table->date('schedule_date');
            $table->decimal('schedule_amount', 12, 2);
            $table->enum('status', ['PENDING', 'PAID'])->default('PENDING');
            $table->timestamps();
            
            $table->foreign('loan_id', 'schedule_repayment_loan_fk')->references('id')->on('loans')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scheduled_repayments');
    }
}
