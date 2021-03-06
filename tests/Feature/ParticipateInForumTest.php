<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ParticipateInForumTest extends TestCase
{

    use DatabaseMigrations;

    /** @test */
    public function unthenticated_users_may_not_add_replies()
    {

        $this->withExceptionHandling()
            ->post(route('threads.replies.store', ['some-channel', 1]), [])
            ->assertRedirect('/login');

    }

    /** @test */
    public function an_authenticated_user_may_participated_in_forum_threads()
    {

        $this->signIn();

        $thread = create('App\Thread');
        $reply = make('App\Reply');

        $this->post($thread->path() . '/replies', $reply->toArray());

        $this->assertDatabaseHas('replies', ['body' => $reply->body]);
        $this->assertEquals(1, $thread->fresh()->replies_count);

    }

    /** @test */
    public function a_reply_requires_a_body()
    {

        $this->withExceptionHandling()->signIn();

        $thread = create('App\Thread');
        $reply = make('App\Reply', ['body' => null]);

        $this->post($thread->path() . '/replies', $reply->toArray())
            ->assertSessionHasErrors('body');;

    }

    /** @test */
    public function unauthorized_users_cannot_delete_replies()
    {

        $this->withExceptionHandling();

        $reply = create('App\Reply');

        $this->delete(route('replies.delete', $reply->id))
            ->assertRedirect('/login');

        $this->signIn()
            ->delete(route('replies.delete', $reply->id))
            ->assertStatus(403);

    }

    /** @test */
    public function authorized_users_can_delete_replies()
    {

        $this->signIn();

        $reply = create('App\Reply', ['user_id' => auth()->id()]);

        $this->delete(route('replies.delete', $reply->id))
            ->assertStatus(302);

        $this->assertDatabaseMissing('replies', ['id' => $reply->id]);
        $this->assertEquals(0, $reply->thread->fresh()->replies_count);
    }

    /** @test */
    public function authorized_users_can_update_replies()
    {

        $this->signIn();

        $reply = create('App\Reply', ['user_id' => auth()->id()]);

        $updatedReply = 'Youre changed fool.';

        $this->patch(route('replies.update', $reply->id), ['body' => $updatedReply]);

        $this->assertDatabaseHas('replies', ['id' => $reply->id, 'body' => $updatedReply]);

    }

    /** @test */
    public function unauthorized_users_cannot_update_replies()
    {

        $this->withExceptionHandling();

        $reply = create('App\Reply');

        $this->patch(route('replies.update', $reply->id))
            ->assertRedirect('/login');

        $this->signIn()
            ->patch(route('replies.delete', $reply->id))
            ->assertStatus(403);

    }

    /** @test */
    public function replies_than_contain_spam_may_not_be_created()
    {
        $this->withExceptionHandling();
        $this->signIn();

        $thread = create('App\Thread');
        $reply = make('App\Reply', [
            'body' => 'Yahoo Costumer Support'
        ]);

        $this->json('POST', $thread->path() . '/replies', $reply->toArray())
            ->assertStatus(422);

    }

    /** @test */
    public function users_may_only_reply_a_maximum_of_once_per_minute()
    {
        $this->withExceptionHandling();
        $this->signIn();

        $thread = create('App\Thread');
        $reply = make('App\Reply', [
            'body' => 'My simple reply'
        ]);

        $this->post($thread->path() . '/replies', $reply->toArray())
            ->assertStatus(200);

        $this->post($thread->path() . '/replies', $reply->toArray())
            ->assertStatus(429);
    }

}
