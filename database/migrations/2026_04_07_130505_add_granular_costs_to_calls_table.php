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
        Schema::table('calls', function (Blueprint $table) {
            $table->decimal('stt_cost_usd', 10, 6)->nullable()->after('cost');
            $table->decimal('llm_cost_usd', 10, 6)->nullable()->after('stt_cost_usd');
            $table->decimal('tts_cost_usd', 10, 6)->nullable()->after('llm_cost_usd');
            $table->decimal('telephony_cost_usd', 10, 6)->nullable()->after('tts_cost_usd');
            $table->decimal('total_cost_kes', 10, 2)->nullable()->after('telephony_cost_usd');
            $table->integer('tokens_used')->nullable()->after('total_cost_kes');
            $table->integer('characters_used')->nullable()->after('tokens_used');
            
            // Rename generic 'cost' to 'total_cost_usd' if needed, or just leave it for backward compatibility
            // Let's decide to keep 'cost' as the primary USD total for legacy support but standardise UI to total_cost_usd
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->dropColumn([
                'stt_cost_usd', 
                'llm_cost_usd', 
                'tts_cost_usd', 
                'telephony_cost_usd', 
                'total_cost_kes', 
                'tokens_used', 
                'characters_used'
            ]);
        });
    }
};
