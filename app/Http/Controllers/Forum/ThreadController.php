<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forum\StoreThreadRequest;
use App\Models\Topic;
use App\Models\Thread;
use App\Services\Forum\ForumService;
use Illuminate\Http\Request;

class ThreadController extends Controller
{
    public function __construct(private readonly ForumService $forumService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $topics = Topic::with('subtopics')->get();

        return view('forum.threads.create', compact('topics'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreThreadRequest $request)
    {
        $this->forumService->createThread($request->validated(), (int) $request->user()->id);

        return redirect()
            ->route('student.forum.index')
            ->with('success', 'Hilo creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $thread = Thread::with([
            'creator',
            'subtopic.topic',
            'questions' => function ($query) {
                $query->withTrashed()->orderBy('order');
            },
            'questions.replies' => function ($query) {
                $query->withTrashed()->orderBy('created_at');
            },
            'questions.replies.creator',
        ])->findOrFail($id);

        return view('forum.threads.show', compact('thread'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function closeThread(string $id)
    {
        $thread = Thread::findOrFail($id);
        $thread->is_closed = true;
        $thread->save();

        return back()->with('success', 'Hilo cerrado exitosamente.');
    }
}
