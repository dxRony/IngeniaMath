<?php

namespace App\Services\Forum;

use App\Models\Question;
use App\Models\QuestionReply;
use App\Models\Thread;
use Illuminate\Support\Facades\DB;

class ForumService
{
    public function createThread(array $data, int $creatorId): Thread
    {
        return DB::transaction(function () use ($data, $creatorId) {
            $thread = Thread::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'subtopic_id' => $data['subtopic_id'],
                'created_by' => $creatorId,
                'is_closed' => false,
            ]);

            $resourceUrl = null;

            if (!empty($data['resource_url'])) {
                $resourceUrl = $data['resource_url']->store('forum/questions', 'public');
            }

            Question::create([
                'thread_id' => $thread->id,
                'body' => $data['initial_question'],
                'resource_url' => $resourceUrl,
                'order' => 1,
            ]);

            return $thread;
        });
    }

    public function createQuestionReply(array $data, int $creatorId): QuestionReply
    {
        return DB::transaction(function () use ($data, $creatorId) {
            $resourceUrl = null;

            if (!empty($data['resource_url'])) {
                $resourceUrl = $data['resource_url']->store('forum/replies', 'public');
            }

            return QuestionReply::create([
                'question_id' => $data['question_id'],
                'body' => $data['body'],
                'resource_url' => $resourceUrl,
                'created_by' => $creatorId,
                'is_accepted' => false,
            ]);
        });
    }

    public function createThreadQuestion(array $data, int $creatorId): Question
    {
        return DB::transaction(function () use ($data, $creatorId) {
            $resourceUrl = null;

            if (!empty($data['resource_url'])) {
                $resourceUrl = $data['resource_url']->store('forum/questions', 'public');
            }

            $nextOrder = (int) Question::where('thread_id', $data['thread_id'])->max('order') + 1;

            return Question::create([
                'thread_id' => $data['thread_id'],
                'body' => $data['body'],
                'resource_url' => $resourceUrl,
                'order' => $nextOrder,
            ]);
        });
    }

    public function acceptReply(QuestionReply $reply): QuestionReply
	{
		$reply->update([
			'is_accepted' => true,
		]);
        // dd($reply);

		return $reply->refresh();
	}

}
