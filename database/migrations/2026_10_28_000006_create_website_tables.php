<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Website Inquiries
        Schema::create('website_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('phone', 20)->index();
            $table->string('email', 100)->nullable();
            $table->string('inquiry_type', 30)->default('general'); // general, bulk_buyer, farmer
            $table->string('subject', 150)->nullable();
            $table->text('message');
            $table->string('status', 20)->default('new')->index(); // new, read, responded
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });

        // 2. Website Content CMS
        Schema::create('website_contents', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('section', 50)->index();
            $table->string('title', 255)->nullable();
            $table->text('content')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_contents');
        Schema::dropIfExists('website_inquiries');
    }
};
