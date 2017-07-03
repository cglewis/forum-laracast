<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CreateThreadsTest extends TestCase
{

    use DatabaseMigrations;

    /** @test */
    public function guests_may_not_create_thread() {

        $this->withExceptionHandling();

        $this->get(route('threads.create'))
        ->assertRedirect('/login');

        $this->post(route('threads'))
        ->assertRedirect('/login');

    }

    /** @test */
    public function an_autenticated_user_can_create_new_forum_threads()
    {

        $this->signIn();

        $thread = make('App\Thread');

        $response = $this->post(route('threads.store'), $thread->toArray());

        $this->get($response->headers->get('Location'))
        ->assertSee($thread->title)
        ->assertSee($thread->body);

    }

    /** @test */
    public function a_thread_requires_a_title()
    {

        $this->publishThread(['title' => null])
        ->assertSessionHasErrors('title');

    }

    /** @test */
    public function a_thread_requires_a_body()
    {

        $this->publishThread(['body' => null])
        ->assertSessionHasErrors('body');

    }

    /** @test */
    public function a_thread_requires_a_valid_channel()
    {

        factory('App\Channel', 2)->create();

        $this->publishThread(['channel_id' => null])
        ->assertSessionHasErrors('channel_id');

        $this->publishThread(['channel_id' => 999])
        ->assertSessionHasErrors('channel_id');

    }

    public function publishThread($overrides = []) {
        $this->withExceptionHandling()->signIn();

        $thread = make('App\Thread', $overrides);

        return $this->post(route('threads.store'), $thread->toArray());
    }

    /** @test */
    public function unauthorized_users_may_not_delete_threads() {

        $this->withExceptionHandling();
        $thread = create('App\Thread');
        $response = $this->delete($thread->path());

        $response->assertRedirect('/login');

        $this->signIn();
        $this->delete($thread->path())->assertStatus(403);

    }

    /** @test */
    public function authorized_users_can_delete_threads() {

        $this->signIn();

        $thread = create('App\Thread', ['user_id' => auth()->id()]);
        $reply = create('App\Reply', ['thread_id' => $thread->id]);

        $response = $this->json('DELETE', $thread->path());
        $response->assertStatus(204);

        $this->assertDatabaseMissing('threads', ['id' => $thread->id]);
        $this->assertDatabaseMissing('replies', ['id' => $reply->id]);

    }

}
