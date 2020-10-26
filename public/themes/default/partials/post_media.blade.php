@if(canUserSeePost(Auth::id(), $post->user->id) || Auth::user()->PurchasedPostsArr->contains($post->id))
@if($post->type != \App\Post::PRICE_TYPE)
    @if(!$post->images->isEmpty() && $post->images->first()->type == 'image')
        @foreach($post->images()->get() as $postImage)            
            @if($post->images->first()->type=='image')
                <div class="post-image-holder col-xl-3 col-lg-4 col-md-6">
                    <a href="{{ url('user/gallery/'.$postImage->source) }}" data-fancybox="galleryMedia">
                        <img src="{{ url('user/gallery/'.$postImage->source) }}" title="{{ $post->user->name }}"
                             alt="{{ $post->user->name }}">
                    </a>
                </div>
            @endif         
        @endforeach
    @endif

    @if(!$post->images->isEmpty() && $post->images->first()->type == 'video')            
        @foreach($post->images as $postImage)
            @if($postImage->type=='video')
                <div class="post-v-holder col-xl-3 col-lg-4 col-md-6  video-item">
                    <div id="unmoved-fixture">
                        <video width="100%" height="auto" id="video-target" controls
                               class="video-video-playe">
                            <source src="{{ url('user/gallery/video/'.$postImage->source) }}"></source>
                        </video>
                    </div>
                </div>
            @endif
        @endforeach            
    @endif

@elseif($post->user->id == Auth::user()->id || Auth::user()->PurchasedPostsArr->contains($post->id))
    @if(!$post->images->isEmpty() && $post->images->first()->type == 'image')
        @foreach($post->images()->get() as $postImage)
            @if($post->images->first()->type=='image')
                <div class="post-image-holder col-xl-3 col-lg-4 col-md-6">
                    <a href="{{ url('user/gallery/'.$postImage->source) }}" data-fancybox="galleryMedia">
                        <img src="{{ url('user/gallery/'.$postImage->source) }}" title="{{ $post->user->name }}"
                             alt="{{ $post->user->name }}">
                    </a>
                </div>
            @endif
        @endforeach
    @endif

    @if(!$post->images->isEmpty() && $post->images->first()->type == 'video')
        @if(!$post->images->isEmpty() && $post->images->first()->type == 'video')
            @foreach($post->images as $postImage)
                @if($postImage->type=='video')
                    <div class="post-v-holder col-xl-3 col-lg-4 col-md-6 video-item">
                        <div id="unmoved-fixture">
                            <video width="100%" height="auto" id="video-target" controls
                                   class="video-video-playe">
                                <source src="{{ url('user/gallery/video/'.$postImage->source) }}"></source>
                            </video>
                        </div>
                    </div>
                @endif
            @endforeach
        @endif          
    @endif

@else
    <div class="grid-item">
        <div class="locked-content">
            <div class="locked-content-wrapper">
                <i class="fa fa-lock" aria-hidden="true"></i>
                <button class="btn btn-success purchase-post" data-post-id="{{ $post->id }}">Buy</button>
            </div>
        </div>
    </div>        
@endif

@endif
<style>
    input[type="number"]::-webkit-outer-spin-button,
    input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
</style>
