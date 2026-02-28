<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registro_pontos_portal', function (Blueprint $table) {
            $table->string('entrada_foto_path')->nullable()->after('saida_em');
            $table->string('saida_foto_path')->nullable()->after('entrada_foto_path');

            $table->decimal('entrada_latitude', 10, 7)->nullable()->after('saida_foto_path');
            $table->decimal('entrada_longitude', 10, 7)->nullable()->after('entrada_latitude');

            $table->decimal('intervalo_inicio_latitude', 10, 7)->nullable()->after('entrada_longitude');
            $table->decimal('intervalo_inicio_longitude', 10, 7)->nullable()->after('intervalo_inicio_latitude');

            $table->decimal('intervalo_fim_latitude', 10, 7)->nullable()->after('intervalo_inicio_longitude');
            $table->decimal('intervalo_fim_longitude', 10, 7)->nullable()->after('intervalo_fim_latitude');

            $table->decimal('saida_latitude', 10, 7)->nullable()->after('intervalo_fim_longitude');
            $table->decimal('saida_longitude', 10, 7)->nullable()->after('saida_latitude');

            $table->boolean('registrado_fora_atendimento')->default(false)->after('saida_longitude');
            $table->integer('distancia_atendimento_metros')->nullable()->after('registrado_fora_atendimento');
            $table->text('justificativa_fora_atendimento')->nullable()->after('distancia_atendimento_metros');
        });
    }

    public function down(): void
    {
        Schema::table('registro_pontos_portal', function (Blueprint $table) {
            $table->dropColumn([
                'entrada_foto_path',
                'saida_foto_path',
                'entrada_latitude',
                'entrada_longitude',
                'intervalo_inicio_latitude',
                'intervalo_inicio_longitude',
                'intervalo_fim_latitude',
                'intervalo_fim_longitude',
                'saida_latitude',
                'saida_longitude',
                'registrado_fora_atendimento',
                'distancia_atendimento_metros',
                'justificativa_fora_atendimento',
            ]);
        });
    }
};
