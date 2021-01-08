@php
$sessionUser = Auth::user();
$isSharedPost = false; // %TODO %FIXME
if ( empty($user) ) {
  throw new Exception('user is not defined for this view');
}
@endphp

<!-- %VIEW: themes/default/paritals/post --> 
<div class="panel panel-default default-post panel-post animated post-wrapper-{{ $post->id }}" id="post{{ $post->id }}">

  <div class="panel-heading no-bg">

    <div class="post-author">

    @if ( !$post->isViewableByUser($sessionUser) ) 
      {!! Theme::partial('post/_optionsDropdownMenu',compact('post','sessionUser')) !!}
    @endif

    <!-- %PSG: the post body itself -->
      <section class="user-avatar">
        <a href="{{ url($post->user->username) }}"><img src="{{ $post->user->avatar->filepath }}" alt="{{ $post->user->name }}" title="{{ $post->user->name }}"></a>
      </section>

      <section class="user-post-details">
        <ul class="list-unstyled no-margin">
          <li>
            @if ($isSharedPost)
            TODO shared
            @endif
            <a href="{{ url($post->user->username) }}" title="{{ '@'.$post->user->username }}" data-toggle="tooltip" data-placement="top" class="user-name user">{{ $post->user->name }}</a>
            @if($isSharedPost)
               's post
            @endif
            @if($post->user->verified)
              <span class="verified-badge bg-success"><i class="fa fa-check"></i></span>
            @endif

            @if($post->users_tagged->count() > 0)
              {{ trans('common.with') }}
              <?php $post_tags = $post->users_tagged->pluck('name')->toArray(); ?>
              <?php $post_tags_ids = $post->users_tagged->pluck('id')->toArray(); ?>
              @foreach($post->users_tagged as $key => $user)
                @if($key==1)
                  {{ trans('common.and') }}
                    @if(count($post_tags)==1)
                      <a href="{{ url($user->username) }}"> {{ $user->name }}</a>
                    @else
                      <a href="#" data-toggle="tooltip" title="" data-placement="top" class="show-users-modal" data-html="true" data-heading="{{ trans('common.with_people') }}"  data-users="{{ implode(',', $post_tags_ids) }}" data-original-title="{{ implode('<br />', $post_tags) }}"> {{ count($post_tags).' '.trans('common.others') }}</a>
                    @endif
                  @break
                @endif
                @if($post_tags != null)
                  <a href="{{ url($user->username) }}" class="user"> {{ array_shift($post_tags) }} </a>
                @endif
              @endforeach
            @endif

              @if($post->timeline_id != $post->user_id)
                      <em>
                          <small>(From 
                              @if($sessionUser->id == $post->timeline_id)
                                  your
                              @else
                                  {{ $post->timeline->user->name }}'s
                              @endif                               
                               timeline)
                          </small>
                      </em>
             @endif

            <div class="small-text">
              @if(isset($timeline))
                @if($timeline->type != 'event' && $timeline->type != 'page' && $timeline->type != 'group')
                  @if($post->timeline->type == 'page' || $post->timeline->type == 'group' || $post->timeline->type == 'event')
                    (posted on
                    <a href="{{ url($post->timeline->username) }}">{{ $post->timeline->name }}</a>
                    {{ $post->timeline->type }})
                  @endif
                @endif
              @endif
            </div>
          </li>

          <li>
              <time class="post-time timeago" datetime="{{ $post->created_at }}+00:00" title="{{ $post->created_at }}+00:00">{{ $post->created_at }}+00:00</time>
              @if ( $post->location != NULL && !$isSharedPost )
              {{ trans('common.at') }} 
              <span class="post-place"><a target="_blank" href="{{ url('/get-location/'.$post->location) }}"><i class="fa fa-map-marker"></i> {{ $post->location }}</a></span>
              @endif
          </li>
          </ul>
        </section>

      </div>
    </div>

    <!-- +++++ -->

      @if ( !$post->isViewableByUser($sessionUser) ) 
          {{-- %PSG hide post if viewer doesn't have access --}}
            <div class="panel-body locked-panel-body tag-price_type">
                <div class="locked-content">
                    <div class="locked-content-wrapper">
                        <i class="fa fa-lock" aria-hidden="true"></i>
                        <button class="btn btn-success clickme_to-show_purchase_post_confirm OFF-purchase-post" data-post-id="{{ $post->id }}">{{ $post->renderCallToAction() }}</button>
                    </div>
                </div>
            </div>
      @else
        {!! Theme::partial('post/_statisticsModal',compact('post','sessionUser')) !!}
        {!! Theme::partial('post/_copylinkModal',compact('post','sessionUser')) !!}
        {!! Theme::partial('post/_shareModal',compact('post','sessionUser')) !!}
        {!! Theme::partial('post/_myModal',compact('post')) !!}

        <section data-post_id="{{ $post->id }}" class="panel-body tag-not_price_type image-with-blur {{ clean($post->description)==''?'single-image-panel':'' }}">
                <div class="text-wrapper">
                    <p class="post-description">{{ $post->description }}</p>

                    <!-- %PSG: UN-locked -->
                    <article class="post-image-holder {{ $post->mediafiles()->first() && $post->mediafiles()->first()->isImage() ?'single-image':''}} tag-MARK-B">
                        @foreach($post->mediafiles as $mf)
                            @if( $mf->isImage() )
                              <span class="tag-post_mf_img {{ $loop->first?'first-image':'hidden' }}" style="background-image: url( {{ $post->mediafiles()->first()->filepath }} );"></span>
                              <a href="{{ $mf->filepath }}" class="{{ $loop->first?'first-image':'hidden' }}" data-fancybox="gallery.{{$post->id}}">
                                <img src="{{ $mf->filepath }}"  title="{{ $post->user->name }}" alt="">
                              </a>
                            @elseif( $mf->isVideo() )
                              <div class="post-v-holder {{ $loop->first?'first-image':'hidden' }}">
                                <div id="unmoved-fixture">
                                    <video width="100%" height="auto" id="video-target" controls class="video-video-playe">
                                        <source src="{{ $mf->filepath }}"></source>
                                    </video>
                                </div>
                              </div>
                            @elseif( $mf->isAudio() )
                              <div class="post-audio-holder {{ $loop->first?'first-image':'hidden' }}">
                                <audio controls src="{{ $mf->filepath }}">
                                    <source src="{{ $mf->filepath }}"></source>
                                </audio>
                              </div>
                            @endif
                        @endforeach
                    </article>
                </div>

                    <ul class="actions-count list-inline">
                        <li>
                        @if($post->users_liked()->count() > 0)
                          @php
                          $liked_ids = $post->users_liked->pluck('id')->toArray();
                          $liked_names = $post->users_liked->pluck('name')->toArray();
                          @endphp
                          <a href="#" class="show-users-modal tag-like-{{$post->id}}" data-html="true" data-heading="{{ trans('common.likes') }}"  data-users="{{ implode(',', $liked_ids) }}" data-original-title="{{ implode('<br />', $liked_names) }}"><span class="count-circle"><i class="fa fa-thumbs-up"></i></span> <span class="circle-like-count circle-like-count-{{$post->id}}">{{ $post->users_liked->count() }}</span> {{ trans('common.likes') }}</a>
                        @else
                          <a href="#" class="show-users-modal tag-like-{{$post->id}} hidden" data-html="true" data-heading="{{ trans('common.likes') }}" data-original-title=""><span class="count-circle"><i class="fa fa-thumbs-up"></i></span> <span class="circle-like-count circle-like-count-{{$post->id}}"></span> {{ trans('common.likes') }}</a>
                        @endif
                        </li>

                        @if($post->comments->count() > 0)
                        <li>
                          <a href="#" class="show-all-comments"><span class="count-circle"><i class="fa fa-comment"></i></span>{{ $post->comments->count() }} {{ trans('common.comments') }}</a>
                        </li>
                        @endif

                        @if($post->shares->count() > 0)
                          @php
                          $shared_ids = $post->shares->pluck('id')->toArray();
                          $shared_names = $post->shares->pluck('name')->toArray();
                          @endphp
                          <li>
                            <a href="#" class="show-users-modal" data-html="true" data-heading="{{ trans('common.shares') }}"  data-users="{{ implode(',', $shared_ids) }}" data-original-title="{{ implode('<br />', $shared_names) }}"><span class="count-circle"><i class="fa fa-share"></i></span> {{ $post->shares->count() }} {{ trans('common.shares') }}</a>
                          </li>
                        @endif
                    </ul>
          </section>                
      @endif


     {{-- Check if subscribed--}}
@if(true || $user->followers->contains($sessionUser->id) || $user->id == $sessionUser->id  || $user->price == 0)

    {!! Theme::partial('post/_footer',compact('post','sessionUser')) !!}

    @if( $post->comments->count() > 0 || $post->user_id == $sessionUser->id || $post->isCommentSectionShown($sessionUser) )
    {!! Theme::partial('post/_comments',compact('post','sessionUser')) !!}
    @endif

@endif
  </div>

{!! Theme::partial('post/_sendTipModal',compact('post','sessionUser')) !!}

@if(isset($next_page_url))
<a class="jscroll-next hidden" href="{{ $next_page_url }}">{{ trans('messages.get_more_posts') }}</a>
@endif

<script type="text/javascript">

  function share(){
        FB.ui(
          {
            method: 'feed',
            name: 'Put name',
            link: 'put link',
            picture: 'image url',
            description: 'descrition'
          },
          function(response) {
            if (response) {
               alert ('success');
            } else {
               alert ('Failed');
            }
          }
        );
    }
</script>

<style>
  .link-preview
  {
    border: 1px solid #EEE;
    margin: 7px 0px;
    padding: 5px;
  }
  .link-preview img
  {
    width: 100%;
    height: auto;
  }
  
  .locked-content {
      font-size: 120px;
      color: #fff;
      text-align: center;
      height: 300px;
      background: rgba(0, 0, 0, 0.8);
      display: flex;
      justify-content: center;
      align-items: center;
      margin: -15px;
      margin-bottom: -7px;
  }

  .locked-content .locked-content-wrapper {
      display: flex;
      flex-direction: column;
  }

  input[type="number"]::-webkit-outer-spin-button,
  input[type="number"]::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
  }

  input[type="number"] {
      appearance: none;
      -moz-appearance: textfield !important;
  }
</style>
