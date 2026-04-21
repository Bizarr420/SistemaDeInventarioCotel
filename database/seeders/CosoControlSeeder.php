<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CosoControlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $controls = [
            // Ambiente de Control
            ['component' => 'environment_control', 'control_objective' => 'Integridad y Valores Éticos', 'description' => 'El personal demuestra integridad y valores éticos en sus acciones', 'control_type' => 'preventive'],
            ['component' => 'environment_control', 'control_objective' => 'Compromiso con Competencia', 'description' => 'El personal posee competencia para ejecutar sus responsabilidades', 'control_type' => 'preventive'],
            ['component' => 'environment_control', 'control_objective' => 'Junta Directiva - Supervisión', 'description' => 'La junta directiva supervisa el sistema de control interno', 'control_type' => 'detective'],

            // Evaluación de Riesgos
            ['component' => 'risk_assessment', 'control_objective' => 'Identificación de Riesgos', 'description' => 'Identificar riesgos que afecten el logro de objetivos', 'control_type' => 'preventive'],
            ['component' => 'risk_assessment', 'control_objective' => 'Análisis de Riesgos', 'description' => 'Analizar riesgos identificados para determinar impacto', 'control_type' => 'detective'],

            // Actividades de Control
            ['component' => 'control_activities', 'control_objective' => 'Generación de Alertas', 'description' => 'Se generan alertas automáticas de obsolescencia', 'control_type' => 'preventive'],
            ['component' => 'control_activities', 'control_objective' => 'Dictámenes Técnicos', 'description' => 'Se requieren dictámenes para validación de obsolescencia', 'control_type' => 'detective'],
            ['component' => 'control_activities', 'control_objective' => 'Aprobación Contable', 'description' => 'Se requiere aprobación contable antes de descartes', 'control_type' => 'preventive'],

            // Información y Comunicación
            ['component' => 'information_communication', 'control_objective' => 'Reportes de Obsolescencia', 'description' => 'Se comunican reportes periódicos de activos obsoletos', 'control_type' => 'detective'],
            ['component' => 'information_communication', 'control_objective' => 'Notificaciones a Responsables', 'description' => 'Se notifica a responsables sobre riesgos identificados', 'control_type' => 'preventive'],

            // Supervisión
            ['component' => 'monitoring', 'control_objective' => 'Auditoría de Cambios', 'description' => 'Se auditan todos los cambios en activos y criterios', 'control_type' => 'detective'],
            ['component' => 'monitoring', 'control_objective' => 'Evaluación de Efectividad', 'description' => 'Se evalúa periódicamente la efectividad de controles', 'control_type' => 'detective'],
        ];

        foreach($controls as $control) {
            \App\Models\CosoControl::create([
                'component' => $control['component'],
                'control_objective' => $control['control_objective'],
                'description' => $control['description'],
                'control_type' => $control['control_type'],
                'status' => 'active',
                'effectiveness' => 'high',
            ]);
        }
    }
}
