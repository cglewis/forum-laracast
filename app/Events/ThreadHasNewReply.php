<?php

namespace App\Events;

use App\Reply;
use App\Thread;
use Illuminate\Queue\SerializesModels;

/**
 * Class ThreadHasNewReply
 * @package App\Events
 */
class ThreadHasNewReply
{
    use SerializesModels;

    public $thread;
    public $reply;

    /**
     * Create a new event instance.
     *
     * @param Thread $thread
     * @param Reply $reply
     */
    public function __construct($thread, $reply)
    {
        $this->thread = $thread;
        $this->reply = $reply;
    }
}
