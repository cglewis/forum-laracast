<div class="panel panel-default">

    <div class="panel-heading">
        <div class="level">
            <h5 class="flex">
                <a href="{{ route('profiles.show', $reply->owner->name) }}">
                    {{ $reply->owner->name }}
                </a> said {{ $reply->created_at->diffForHumans() }}
            </h5>

            <div>
                <form method="post" action="{{ route('replies.favorites', $reply->id) }}">
                    {{ csrf_field() }}
                        <button type="submit" class="btn btn-default" {{ $reply->isFavorited() ? 'disabled' : '' }}>
                            {{ $reply->favorites_count }} {{ str_plural('Favorite', $reply->favorites_count) }}
                        </button>
                </form>
            </div>

        </div>
    </div>

    <div class="panel-body">
        {{ $reply->body }}
    </div>
</div>