<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_no', 200);
            $table->unsignedBigInteger('user_id');
            $table->decimal('loan_amount', 12, 2);
            $table->decimal('loan_balance', 12, 2)->nullable();
            $table->enum('term_period', ['weekly'])->default('weekly');
            $table->integer('term');
            $table->dateTime('loan_approved_date')->nullable();
            $table->enum('loan_status', ['PENDING', 'APPROVE', 'REJECTED', 'PAID'])->default('PENDING');
            $table->unsignedBigInteger('loan_approved_by')->nullable();
            $table->dateTime('loan_created_date');
            $table->timestamps();
            
            $table->foreign('loan_approved_by', 'loan_approver_fk')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id', 'loan_customer_fk')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loans');
    }
}
