@if(!$post->images->isEmpty() && $post->images->first()->type == 'image')
    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
        <div class="post-image-holder grid-item @if(count($post->images()->get()) == 1) single-image @else multiple-images @endif">
                @if($post->images->first()->type=='image')
                    <a href="{{ route('post.show', $post->id) }}">
                        <img src="{{ url('user/gallery/'.$post->images->first()->source) }}"  title="{{ $post->user->name }}" alt="{{ $post->user->name }}">
                    </a>
                @endif
        </div>
    </div>
@endif

@if(!$post->images->isEmpty() && $post->images->first()->type == 'video')
<div class="col-lg-4 col-md-6 col-sm-6 col-12">
    <div class="post-v-holder grid-item video-item">
        @foreach($post->images as $postImage)
            @if($postImage->type=='video')
                <div id="unmoved-fixture">
                    <video width="100%" height="auto" id="video-target" controls class="video-video-playe">
                        <source src="{{ url('user/gallery/video/'.$postImage->source) }}"></source>
                    </video>
                </div>
    
            @endif
        @endforeach
    </div>
</div>
@endif

<!-- Modal Ends here -->
@if(isset($next_page_url))
    <a class="jscroll-next hidden" href="{{ $next_page_url }}">{{ trans('messages.get_more_posts') }}</a>
@endif

