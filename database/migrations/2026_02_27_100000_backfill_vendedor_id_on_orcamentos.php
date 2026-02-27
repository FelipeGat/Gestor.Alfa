<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $limiteData = Carbon::today();

        DB::table('orcamentos')
            ->whereNull('vendedor_id')
            ->whereNotNull('created_by')
            ->whereDate('created_at', '<', $limiteData)
            ->orderBy('id')
            ->chunkById(500, function ($orcamentos) {
                foreach ($orcamentos as $orcamento) {
                    DB::table('orcamentos')
                        ->where('id', $orcamento->id)
                        ->whereNull('vendedor_id')
                        ->update([
                            'vendedor_id' => $orcamento->created_by,
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        $limiteData = Carbon::today();

        DB::table('orcamentos')
            ->whereDate('created_at', '<', $limiteData)
            ->whereColumn('vendedor_id', 'created_by')
            ->update([
                'vendedor_id' => null,
                'updated_at' => now(),
            ]);
    }
};
