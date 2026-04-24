<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use App\Models\QuestionReply;
use App\Models\Thread;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    public function index(Request $request)
    {
        $selectedTopicId = $request->integer('topic_id') ?: null;
        $showMyThreads = $request->boolean('my_threads');

        $threads = Thread::query()
            ->with(['creator', 'subtopic.topic'])
            ->withCount('questions')
            ->addSelect([
                'total_replies_count' => QuestionReply::query()
                    ->selectRaw('COUNT(*)')
                    ->join('questions', 'questions.id', '=', 'question_replies.question_id')
                    ->whereColumn('questions.thread_id', 'threads.id')
                    ->whereNull('questions.deleted_at'),
                'accepted_replies_count' => QuestionReply::query()
                    ->selectRaw('COUNT(*)')
                    ->join('questions', 'questions.id', '=', 'question_replies.question_id')
                    ->whereColumn('questions.thread_id', 'threads.id')
                    ->whereNull('questions.deleted_at')
                    ->where('question_replies.is_accepted', true),
            ]);

        // Filtrar por módulo (topic)
        if ($selectedTopicId) {
            $threads->whereHas('subtopic', function ($query) use ($selectedTopicId) {
                $query->where('topic_id', $selectedTopicId);
            });
        }

        // Filtrar solo mis hilos
        if ($showMyThreads) {
            $threads->where('created_by', Auth::user()->id);
        }

        $threads = $threads
            ->latest()
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'threadsHtml' => view('forum.partials.thread-cards', compact('threads'))->render(),
                'paginationHtml' => view('forum.partials.thread-pagination', compact('threads'))->render(),
            ]);
        }

        $topics = Topic::query()->orderBy('name')->get(['id', 'name']);

        return view('forum.index', compact('threads', 'topics', 'selectedTopicId', 'showMyThreads'));
    }
}
