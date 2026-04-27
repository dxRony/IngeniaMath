<?php

namespace App\Services\Admin;

use App\Models\ExamConfig;
use App\Models\ExamTopicDistribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExamConfigService
{
    /**
     * Crea una nueva configuración de simulacro con su distribución temática.
     * Si se marca como activa, desactiva las demás.
     */
    public function createConfig(Request $request): ExamConfig
    {
        return DB::transaction(function () use ($request) {
            $isActive = (bool) $request->boolean('is_active');

            // Si la nueva config será activa, desactivar las demás
            if ($isActive) {
                ExamConfig::where('is_active', true)->update(['is_active' => false]);
            }

            $config = ExamConfig::create([
                'duration_minutes'  => $request->duration_minutes,
                'total_questions'   => $request->total_questions,
                'min_passing_score' => $request->min_passing_score,
                'is_active'         => $isActive,
                'created_by'        => Auth::id(),
            ]);

            $this->syncTopicDistributions($config, $request->input('distributions', []));

            return $config;
        });
    }

    /**
     * Actualiza una configuración existente y su distribución.
     */
    public function updateConfig(Request $request, ExamConfig $config): ExamConfig
    {
        return DB::transaction(function () use ($request, $config) {
            $isActive = (bool) $request->boolean('is_active');

            // Si se está activando esta config, desactivar las demás
            if ($isActive && !$config->is_active) {
                ExamConfig::where('id', '!=', $config->id)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }

            $config->update([
                'duration_minutes'  => $request->duration_minutes,
                'total_questions'   => $request->total_questions,
                'min_passing_score' => $request->min_passing_score,
                'is_active'         => $isActive,
            ]);

            $this->syncTopicDistributions($config, $request->input('distributions', []));

            return $config->fresh('topicDistributions');
        });
    }

    /**
     * Activa una configuración (y desactiva las demás).
     */
    public function activate(ExamConfig $config): void
    {
        DB::transaction(function () use ($config) {
            ExamConfig::where('id', '!=', $config->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $config->update(['is_active' => true]);
        });
    }

    /**
     * Desactiva una configuración (no toca las demás).
     */
    public function deactivate(ExamConfig $config): void
    {
        $config->update(['is_active' => false]);
    }

    /**
     * Sincroniza las filas de exam_topic_distributions con los datos del request.
     * Estrategia: borrar todas y recrear (más simple que diff).
     */
    private function syncTopicDistributions(ExamConfig $config, array $distributions): void
    {
        $config->topicDistributions()->delete();

        foreach ($distributions as $topicId => $count) {
            $count = (int) $count;
            if ($count > 0) {
                ExamTopicDistribution::create([
                    'config_id'      => $config->id,
                    'topic_id'       => $topicId,
                    'question_count' => $count,
                ]);
            }
        }
    }
}
