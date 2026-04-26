<?php

namespace App\Services\Student;

use App\Models\AssessmentModuleResult;
use App\Models\InitialAssessment;
use App\Models\LearningPath;
use App\Models\MasteryLevel;
use App\Models\PathItem;
use App\Models\Subtopic;
use App\Models\SubtopicPrerequisite;
use Illuminate\Support\Facades\DB;

class LearningPathService
{
    public function generate(InitialAssessment $assessment): LearningPath
    {
        // Paso 1 — Desactivar ruta anterior si existe
        LearningPath::where('student_id', $assessment->student_id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        // Paso 2 — Crear nueva ruta
        $path = LearningPath::create([
            'student_id' => $assessment->student_id,
            'is_active'  => true,
        ]);

        // Paso 3 — Obtener módulos priorizados (Deficiente primero, luego En desarrollo)
        $dominatedId = MasteryLevel::where('name', 'Dominado')->value('id');

        $moduleResults = AssessmentModuleResult::where('assessment_id', $assessment->id)
            ->where('mastery_level_id', '!=', $dominatedId)
            ->with('masteryLevel')
            ->get()
            ->sortBy(fn($r) => $r->masteryLevel->min_threshold);

        // Paso 4 y 5 — Recopilar items respetando prerrequisitos
        $itemsToInsert  = [];  // subtopic_id => mastery_level_id
        $addedSubtopics = [];  // para evitar duplicados

        foreach ($moduleResults as $result) {
            $subtopics = Subtopic::where('topic_id', $result->topic_id)
                ->orderBy('complexity_id')
                ->get();

            foreach ($subtopics as $subtopic) {
                $prerequisite = SubtopicPrerequisite::where('subtopic_id', $subtopic->id)
                    ->first();

                if ($prerequisite && !in_array($prerequisite->prerequisite_id, $addedSubtopics)) {
                    $prereqSubtopic = Subtopic::find($prerequisite->prerequisite_id);

                    // Verificar si el módulo del prerrequisito fue Dominado
                    $prereqModuleResult = AssessmentModuleResult::where('assessment_id', $assessment->id)
                        ->where('topic_id', $prereqSubtopic->topic_id)
                        ->first();

                    $prereqEsDominado = $prereqModuleResult
                        && $prereqModuleResult->mastery_level_id === $dominatedId;

                    // Solo agregar el prerrequisito si su módulo NO fue dominado
                    if (!$prereqEsDominado) {
                        $prereqMasteryLevelId = $prereqModuleResult
                            ? $prereqModuleResult->mastery_level_id
                            : MasteryLevel::orderBy('min_threshold')->value('id');

                        $itemsToInsert[]  = [
                            'subtopic_id'      => $prereqSubtopic->id,
                            'mastery_level_id' => $prereqMasteryLevelId,
                        ];
                        $addedSubtopics[] = $prereqSubtopic->id;
                    }
                }

                if (!in_array($subtopic->id, $addedSubtopics)) {
                    $itemsToInsert[]  = [
                        'subtopic_id'      => $subtopic->id,
                        'mastery_level_id' => $result->mastery_level_id,
                    ];
                    $addedSubtopics[] = $subtopic->id;
                }
            }
        }

        // Paso 6 — Insertar Path_Items
        $rows  = [];
        $order = 1;

        foreach ($itemsToInsert as $item) {
            $rows[] = [
                'path_id'          => $path->id,
                'subtopic_id'      => $item['subtopic_id'],
                'mastery_level_id' => $item['mastery_level_id'],
                'order'            => $order++,
                'is_completed'     => false,
            ];
        }

        DB::table('path_items')->insert($rows);

        return $path;
    }

    private function getMasteryLevelForTopic(
        int $topicId,
        int $assessmentId,
        int $dominatedId
    ): int {
        $result = AssessmentModuleResult::where('assessment_id', $assessmentId)
            ->where('topic_id', $topicId)
            ->first();

        // Si el módulo del prerrequisito fue dominado o no tiene resultado,
        // usar el mastery_level más bajo (Deficiente) como referencia
        if (!$result || $result->mastery_level_id === $dominatedId) {
            return MasteryLevel::orderBy('min_threshold')->value('id');
        }

        return $result->mastery_level_id;
    }
}
