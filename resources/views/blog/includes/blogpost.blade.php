<div>
    <a href="{{ route('blog.show', ['slug' => $blogpost->slug]) }}">
        <h2 class="font-weight-bold" style="font-size: 1.5em;">{{$blogpost->title}}</h2>
    </a>

    @if($showFull)
        {!! Markdown::parse($blogpost->body) !!}
    @else
        <div class="row">
            <div class="col-md-10">{!! Markdown::parse($blogpost->preview) !!}</div>
            <div class="col-md-2">
                <a class="mt-0 mb-5"
                   href="{{ route('blog.show', ['slug' => $blogpost->slug]) }}">
                    {{ __('menu.readmore') }} &raquo;
                </a>
            </div>
        </div>
    @endif

    <div class="text-muted">
        <p class="float-end">
            <samp class="pe-2">{{ $blogpost->published_at->format('d.m.Y') }}</samp>
        </p>
        <ul class="list-inline mb-0">
            <li class="list-inline-item">
                @if(!empty($blogpost->twitter_handle))
                    <a href="https://twitter.com/{{ $blogpost->twitter_handle }}">
                        <i class="fab fa-twitter"></i>
                    </a>
                @endif
                {{ $blogpost->author_name }}
            </li>
            <li class="list-inline-item">
                <a href="{{ route('blog.category', ['category' => $blogpost->category]) }}">
                    <i class="fa fa-tag"></i>
                    {{$blogpost->category}}
                </a>
            </li>
            <li class="list-inline-item">
                <a href="https://twitter.com/intent/tweet?text={{ urlencode($blogpost->title . ' ' .
                                route('blog.show', ['slug' => $blogpost->slug]) . ' via @traewelling' ) }}">
                    Tweet
                </a>
            </li>
        </ul>
    </div>
</div>
