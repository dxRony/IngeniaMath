<?php

namespace App\Http\Controllers\Forum;

use App\Http\Controllers\Controller;
use App\Http\Requests\Forum\StoreQuestionReplyRequest;
use App\Models\Question;
use App\Models\QuestionReply;
use App\Services\Forum\ForumService;
use Illuminate\Http\Request;

class QuestionReplyController extends Controller
{
    public function __construct(private readonly ForumService $forumService) {}

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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreQuestionReplyRequest $request)
    {
        Question::findOrFail($request->validated()['question_id']);

        $this->forumService->createQuestionReply($request->validated(), (int) $request->user()->id);

        return back()->with('success', 'Respuesta publicada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        $reply = QuestionReply::findOrFail($id);
        $reply->delete();
        return back()->with('success', 'Respuesta eliminada exitosamente.');
    }

    public function acceptReply(int $replyId)
    {
        $reply = QuestionReply::findOrFail($replyId);

        $this->forumService->acceptReply($reply);

        return back()->with('success', 'Respuesta aceptada exitosamente.');
    }
}
