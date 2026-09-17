<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void { 
        Schema::create("chatbot_messages", function (Blueprint $table) { 
            $table->id(); 
            $table->foreignId("user_id")->constrained()->cascadeOnDelete(); 
            $table->text("message"); 
            $table->text("response"); 
            // Groups multiple exchanges into one conversation session 
            // Used in Module 16 to pass context to AI 
            $table->string("conversation_id")->nullable(); 
            // Token usage — populated in Module 16 when AI is integrated 
            $table->unsignedInteger("tokens_used")->nullable(); 
            $table->timestamps(); 
            $table->index("conversation_id"); 
            $table->index(["user_id","created_at"]); 
        }); 
    } 
    public function down(): void { Schema::dropIfExists("chatbot_messages"); } 
};
