<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamConfig;
use App\Models\Topic;
use App\Services\Admin\ExamConfigService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExamConfigController extends Controller
{
    public function __construct(
        private ExamConfigService $configService
    ) {}

    public function index(): View
    {
        $configs = ExamConfig::with(['topicDistributions.topic', 'creator'])
            ->latest()
            ->paginate(10);

        return view('admin.exam-configs.index', compact('configs'));
    }

    public function create(): View
    {
        $topics = Topic::orderBy('order')->get();
        $availability = $this->getExerciseAvailabilityByTopic();

        return view('admin.exam-configs.create', compact('topics', 'availability'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->validateConfig($request);

        $this->configService->createConfig($request);

        return redirect()->route('admin.exam-configs.index')
            ->with('success', 'Configuración de simulacro creada correctamente.');
    }

    public function edit(int $id): View
    {
        $config = ExamConfig::with('topicDistributions')->findOrFail($id);
        $topics = Topic::orderBy('order')->get();
        $availability = $this->getExerciseAvailabilityByTopic();

        // Mapeamos las distribuciones actuales como [topic_id => count]
        $currentDistributions = $config->topicDistributions
            ->pluck('question_count', 'topic_id')
            ->toArray();

        return view('admin.exam-configs.edit', compact('config', 'topics', 'availability', 'currentDistributions'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $config = ExamConfig::findOrFail($id);

        $this->validateConfig($request);

        $this->configService->updateConfig($request, $config);

        return redirect()->route('admin.exam-configs.index')
            ->with('success', 'Configuración actualizada correctamente.');
    }

    public function activate(int $id): RedirectResponse
    {
        $config = ExamConfig::findOrFail($id);

        $this->configService->activate($config);

        return redirect()->route('admin.exam-configs.index')
            ->with('success', "Configuración \"{$id}\" activada. Las demás quedaron desactivadas.");
    }

    public function deactivate(int $id): RedirectResponse
    {
        $config = ExamConfig::findOrFail($id);

        $this->configService->deactivate($config);

        return redirect()->route('admin.exam-configs.index')
            ->with('success', 'Configuración desactivada.');
    }

    /**
     * Validaciones del request, incluyendo reglas cruzadas
     * (suma de distribución coincide con total_questions, no pedir más de lo disponible).
     */
    private function validateConfig(Request $request): void
    {
        $request->validate([
            'duration_minutes'        => 'required|integer|min:1|max:600',
            'total_questions'         => 'required|integer|min:1|max:200',
            'min_passing_score'       => 'required|numeric|min:0|max:100',
            'is_active'               => 'nullable|boolean',
            'distributions'           => 'required|array',
            'distributions.*'         => 'nullable|integer|min:0',
        ], [
            'duration_minutes.required'  => 'La duración es obligatoria.',
            'total_questions.required'   => 'El total de preguntas es obligatorio.',
            'min_passing_score.required' => 'El puntaje mínimo de aprobación es obligatorio.',
            'distributions.required'     => 'Debes definir la distribución por módulos.',
        ]);

        // Validación cruzada: la suma de la distribución debe coincidir con total_questions
        $sum = array_sum(array_map('intval', $request->input('distributions', [])));
        if ($sum !== (int) $request->total_questions) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'distributions' => "La suma de preguntas por módulo ({$sum}) no coincide con el total ({$request->total_questions}).",
            ]);
        }

        // Validación de disponibilidad: no pedir más ejercicios de los que hay en cada topic
        $availability = $this->getExerciseAvailabilityByTopic();
        foreach ($request->input('distributions', []) as $topicId => $count) {
            $count = (int) $count;
            $available = $availability[$topicId] ?? 0;
            if ($count > $available) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'distributions' => "El módulo ID {$topicId} solo tiene {$available} ejercicios aprobados disponibles, no puedes pedir {$count}.",
                ]);
            }
        }
    }

    /**
     * Devuelve un array [topic_id => cantidad de ejercicios aprobados disponibles].
     * Solo cuenta ejercicios con status_id IN (3, 4) -> Aprobado o Publicado.
     */
    private function getExerciseAvailabilityByTopic(): array
    {
        return DB::table('exercises')
            ->join('subtopics', 'exercises.subtopic_id', '=', 'subtopics.id')
            ->whereIn('exercises.status_id', [3, 4])
            ->selectRaw('subtopics.topic_id, count(exercises.id) as total')
            ->groupBy('subtopics.topic_id')
            ->pluck('total', 'topic_id')
            ->toArray();
    }
}
