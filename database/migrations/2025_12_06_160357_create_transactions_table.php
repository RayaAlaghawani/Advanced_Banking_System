<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            // الحساب المرسل (قد يكون NULL في حالة Deposit)
            $table->unsignedBigInteger('from_account_id')->nullable();
            // الحساب المستلم (قد يكون NULL في حالة Withdraw)
            $table->unsignedBigInteger('to_account_id')->nullable();
            // المستخدم الذي قام بالعملية (اختياري لكنه مهم)
            $table->unsignedBigInteger('user_id')->nullable();
            // قيمة العملية
            $table->decimal('amount', 12, 2);

            $table->enum('type', ['deposit', 'withdraw', 'transfer']);
            // حالة العملية
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',

                'failed',
                'scheduled',
                'completed',
            ])->default('pending');

            // إذا كانت العملية مجدولة
            $table->timestamp('scheduled_at')->nullable();

            // ملاحظات إضافية مثل IP, user_agent, logs
           // $table->json('metadata')->nullable();

            // العلاقات (اختياري حالياً)
            // ستُفعلينها بعد أن تنهي زميلتك جدول accounts
            // $table->foreign('from_account_id')->references('id')->on('accounts');
            // $table->foreign('to_account_id')->references('id')->on('accounts');
             $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
