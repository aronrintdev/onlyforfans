@applyif(canUserSeePost(Auth::id(), $post->user->id) || Auth::user()->PurchasedPostsArr->contains($post->id))
@if($post->type != \App\Post::PRICE_TYPE)
    @if(!$post->images->isEmpty() && $post->images->first()->type == 'image')
        <div class="grid-item">
            <div class="post-image-holder @if(count($post->images()->get()) == 1) single-image @else multiple-images @endif">
                @if($post->images->first()->type=='image')
                    <a href="#" data-toggle="modal" data-target="#postDetail{{ $post->id }}">
                        <img src="{{ url('user/gallery/'.$post->images->first()->source) }}"  title="{{ $post->user->name }}" alt="{{ $post->user->name }}">
                    </a>
                @endif
            </div>
            {!! Theme::partial('explore_posts_detail',compact('post','timeline','next_page_url')) !!}

            @if(isset($next_page_url))
                <a class="jscroll-next next-link hidden" href="{{ $next_page_url }}">{{ trans('messages.get_more_posts') }}</a>
            @endif
        </div>
    @endif

    @if(!$post->images->isEmpty() && $post->images->first()->type == 'video')
        <div class="grid-item">
            <div class="post-v-holder video-item">
                @foreach($post->images as $postImage)
                    @if($postImage->type=='video')
                        <div id="unmoved-fixture">
                            <a href="#" data-toggle="modal" data-target="#postDetail{{ $post->id }}">
                                <video width="100%" height="auto" id="video-target" controls class="video-video-playe">
                                    <source src="{{ url('uploads/user/video/'.$postImage->source) }}"></source>
                                </video>
                            </a>
                        </div>

                    @endif
                @endforeach
            </div>
            {!! Theme::partial('explore_posts_detail',compact('post','timeline','next_page_url')) !!}

        
            @if(isset($next_page_url))
                <a class="jscroll-next next-link hidden" href="{{ $next_page_url }}">{{ trans('messages.get_more_posts') }}</a>
            @endif
        </div>
    @endif

    @if(!$post->images->isEmpty() && $post->images->first()->type == 'audio' && isset($audio))
        <div class="grid-item">
            <div class="post-v-holder video-item">
                @foreach($post->images as $postImage)
                    @if($postImage->type=='audio')
                        <div id="unmoved-fixture">
                            <a href="#" data-toggle="modal" data-target="#postDetail{{ $post->id }}">
                                <audio width="100%" height="auto" id="video-target" controls class="video-video-playe" src="{{ url('uploads/user/audio/'.$postImage->source) }}">
                                    <source src="{{ url('uploads/user/audio/'.$postImage->source) }}"></source>
                                </audio>
                            </a>
                        </div>

                    @endif
                @endforeach
            </div>
            {!! Theme::partial('explore_posts_detail',compact('post','timeline','next_page_url', 'audio')) !!}

        
            @if(isset($next_page_url))
                <a class="jscroll-next next-link hidden" href="{{ $next_page_url }}">{{ trans('messages.get_more_posts') }}</a>
            @endif
        </div>
    @endif

    @if(isset($next_page_url))
        <a class="jscroll-next next-link hidden" href="{{ $next_page_url }}">{{ trans('messages.get_more_posts') }}</a>
    @endif

@elseif($post->user->id == Auth::user()->id || Auth::user()->PurchasedPostsArr->contains($post->id))
    @if(!$post->images->isEmpty() && $post->images->first()->type == 'image')
        <div class="grid-item">
            <div class="post-image-holder @if(count($post->images()->get()) == 1) single-image @else multiple-images @endif">
                @if($post->images->first()->type=='image')
                    <a href="#" data-toggle="modal" data-target="#postDetail{{ $post->id }}">
                      <img src="{{ url('user/gallery/'.$post->images->first()->source) }}"  title="{{ $post->user->name }}" alt="">
                    </a>
                @endif
            </div>
            {!! Theme::partial('explore_posts_detail',compact('post','timeline','next_page_url')) !!}

        <!-- Modal Ends here -->
            @if(isset($next_page_url))
                <a class="jscroll-next next-link hidden" href="{{ $next_page_url }}">{{ trans('messages.get_more_posts') }}</a>
            @endif
        </div>            
    @endif

    @if(!$post->images->isEmpty() && $post->images->first()->type == 'video')
        <div class="grid-item">
            <div class="post-v-holder video-item">
                @foreach($post->images as $postImage)
                    @if($postImage->type=='video')
                        <div id="unmoved-fixture">
                            <a href="#" data-toggle="modal" data-target="#postDetail{{ $post->id }}">
                                <video width="100%" height="auto" id="video-target" controls class="video-video-playe">
                                    <source src="{{ url('uploads/user/video/'.$postImage->source) }}"></source>
                                </video>
                            </a>
                        </div>

                    @endif
                @endforeach
            </div>
            {!! Theme::partial('explore_posts_detail',compact('post','timeline','next_page_url')) !!}

        <!-- Modal Ends here -->
            @if(isset($next_page_url))
                <a class="jscroll-next next-link hidden" href="{{ $next_page_url }}">{{ trans('messages.get_more_posts') }}</a>
            @endif
        </div>            
    @endif


    @if(!$post->images->isEmpty() && $post->images->first()->type == 'audio' && isset($audio))
        <div class="grid-item">
            <div class="post-v-holder video-item">
                @foreach($post->images as $postImage)
                    @if($postImage->type=='audio')
                        <div id="unmoved-fixture">
                            <a href="#" data-toggle="modal" data-target="#postDetail{{ $post->id }}">
                                <audio width="100%" height="auto" id="video-target" controls class="video-video-playe" src="{{ url('uploads/user/audio/'.$postImage->source) }}">
                                    <source src="{{ url('uploads/user/audio/'.$postImage->source) }}"></source>
                                </audio>
                            </a>
                        </div>

                    @endif
                @endforeach
            </div>
            {!! Theme::partial('explore_posts_detail',compact('post','timeline','next_page_url', 'audio')) !!}


            @if(isset($next_page_url))
                <a class="jscroll-next next-link hidden" href="{{ $next_page_url }}">{{ trans('messages.get_more_posts') }}</a>
            @endif
        </div>
    @endif
@elseif($post->type == \App\Post::PRICE_TYPE && !isset($hideLocked))
    <div class="grid-item">
        <div class="locked-content">
            <div class="locked-content-wrapper">
                <i class="fa fa-lock" aria-hidden="true"></i>
                <button class="btn btn-success purchase-post" data-post-id="{{ $post->id }}">Buy</button>
            </div>
        </div>

        <!-- Modal Ends here -->
        @if(isset($next_page_url))
            <a class="jscroll-next next-link hidden" href="{{ $next_page_url }}">{{ trans('messages.get_more_posts') }}</a>
        @endif
    </div>        
@endif    

@endif
<style>
    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .post-audio-holder {
        text-align: center;
    }
    .post-image-holder a {
        max-height: inherit;
    }

    .modal-body .post-image-holder:not(.single-image)>a {
        width: 50%;
        height: 250px;
        float: left;
        display: inline-block;
        padding: 0 15px;
    }

    .modal-body .post-image-holder:not(.single-image)>a img {
        width: 100%;
        object-fit: cover;
    }

    .modal-body .post-image-holder:not(.single-image) {
        overflow: auto !important;
    }
    
</style>
