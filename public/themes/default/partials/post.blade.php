@if(isset($post->shared_post_id))
  <?php
    $sharedOwner = $post;
    $post = App\Post::where('id', $post->shared_post_id)->with('comments')->first();
  ?>
@endif

<?php
$main_description = $post->description;
?>
    
<!-- %VIEW: themes/default/paritals/post --> 
<div class="panel panel-default default-post panel-post animated post-wrapper-{{ $post->id }}" id="post{{ $post->id }}">
  <div class="panel-heading no-bg">
    <div class="post-author">

    @if(isset($user) == false || $user->followers->contains(Auth::user()->id) || $user->id == Auth::user()->id || $user->price == 0)
    <!-- %PSG: post options dropdown (upper-right) -->
      <div class="post-options">
        <ul class="list-inline no-margin">
          <li class="dropdown"><a href="#" class="dropdown-togle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="fa fa-angle-down"></i></a>
            <ul class="dropdown-menu">
              @if($post->notifications_user->contains(Auth::user()->id))
              <li class="main-link">
                <a href="#" data-post-id="{{ $post->id }}" class="notify-user unnotify">
                  <i class="fa  fa-bell-slash" aria-hidden="true"></i>{{ trans('common.stop_notifications') }}
                  <span class="small-text">{{ trans('messages.stop_notification_text') }}</span>
                </a>
              </li>
              <li class="main-link hidden">
                <a href="#" data-post-id="{{ $post->id }}" class="notify-user notify">
                  <i class="fa fa-bell" aria-hidden="true"></i>{{ trans('common.get_notifications') }}
                  <span class="small-text">{{ trans('messages.get_notification_text') }}</span>
                </a>
              </li>
              @else
              <li class="main-link hidden">
                <a href="#" data-post-id="{{ $post->id }}" class="notify-user unnotify">
                  <i class="fa  fa-bell-slash" aria-hidden="true"></i>{{ trans('common.stop_notifications') }}
                  <span class="small-text">{{ trans('messages.stop_notification_text') }}</span>
                </a>
              </li>
              <li class="main-link">
                <a href="#" data-post-id="{{ $post->id }}" class="notify-user notify">
                  <i class="fa fa-bell" aria-hidden="true"></i>{{ trans('common.get_notifications') }}
                  <span class="small-text">{{ trans('messages.get_notification_text') }}</span>
                </a>
              </li>
              @endif

              @if(Auth::user()->id == $post->user->id)
              <li class="main-link">
                <a href="#" data-post-id="{{ $post->id }}" class="edit-post">
                  <i class="fa fa-edit" aria-hidden="true"></i>{{ trans('common.edit') }}
                  <span class="small-text">{{ trans('messages.edit_text') }}</span>
                </a>
              </li>
              @endif

              @if((Auth::id() == $post->user->id) || ($post->timeline_id == Auth::user()->timeline_id))
              <li class="main-link">
                <a href="#" class="delete-post" data-post-id="{{ $post->id }}">
                  <i class="fa fa-trash" aria-hidden="true"></i>{{ trans('common.delete') }}
                  <span class="small-text">{{ trans('messages.delete_text') }}</span>
                </a>
              </li>
              @endif

              @if(Auth::user()->id != $post->user->id)
               <li class="main-link">
                <a href="#" class="hide-post" data-post-id="{{ $post->id }}">
                  <i class="fa fa-eye-slash" aria-hidden="true"></i>{{ trans('common.hide_notifications') }}
                  <span class="small-text">{{ trans('messages.hide_notification_text') }}</span>
                </a>
              </li>

              <li class="main-link">
                <a href="#" class="save-post" data-post-id="{{ $post->id }}">
                  <i class="fa fa-save" aria-hidden="true"></i>
                    @if(!Auth::user()->postsSaved->contains($post->id))
                      {{ trans('common.save_post') }}
                      <span class="small-text">{{ trans('messages.post_save_text') }}</span>
                    @else
                      {{ trans('common.unsave_post') }}
                      <span class="small-text">{{ trans('messages.post_unsave_text') }}</span>
                    @endif
                </a>
              </li>

              <li class="main-link">
                  <a href="#" class="pin-post" data-post-id="{{ $post->id }}">
                      <i class="fa fa-save" aria-hidden="true"></i>
                      @if(!Auth::user()->postsPinned->contains($post->id))
                          {{ trans('common.pin_to_your_profile_page') }}
                          <span class="small-text">{{ trans('messages.post_pin_to_profile_text') }}</span>
                      @else
                          {{ trans('common.unpin_from_your_profile_page') }}
                          <span class="small-text">{{ trans('messages.post_unpin_from_profile_text') }}</span>
                      @endif
                  </a>
              </li>

              <li class="main-link">
                <a href="#" class="manage-report report" data-post-id="{{ $post->id }}">
                  <i class="fa fa-flag" aria-hidden="true"></i>{{ trans('common.report') }}
                  <span class="small-text">{{ trans('messages.report_text') }}</span>
                </a>
              </li>
              @endif

              <li class="divider"></li>

              @if((Auth::id() == $post->user->id) || ($post->timeline_id == Auth::user()->timeline_id))
                  <li class="main-link">
                      <a href="#" data-toggle="modal" data-target="#statisticsModal{{ $post->id }}"><i class="fa fa-bar-chart"></i>{{ trans('common.post_statistics') }}</a>
                  </li>
              @endif

              <li class="main-link">
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url('/share-post/'.$post->id)) }}" class="fb-xfbml-parse-ignore" target="_blank"><i class="fa fa-facebook-square"></i>Facebook {{ trans('common.share') }}</a>
              </li>

              <li class="main-link">
                <a href="https://twitter.com/intent/tweet?text={{ url('/share-post/'.$post->id) }}" target="_blank"><i class="fa fa-twitter-square"></i>Twitter {{ trans('common.tweet') }}</a>
              </li>

              <li class="main-link">
                <a href="#" data-toggle="modal" data-target="#shareModal{{ $post->id }}"><i class="fa fa-share-alt"></i>Embed {{ trans('common.post') }}</a>
              </li>
              
              <li class="main-link">
                <a href="#" data-toggle="modal" data-target="#copyLinkModal{{ $post->id }}"><i class="fa fa-link"></i>Copy Link</a>
{{--                  <a id="copy-link"><i class="fa fa-link"></i>Copy Link</a> --}}
              </li>

            </ul>

          </li>

        </ul>
      </div>
    @endif

    <!-- %PSG: the post body itself -->
      <div class="user-avatar">
        <a href="{{ url($post->user->username) }}"><img src="{{ $post->user->avatar->filepath }}" alt="{{ $post->user->name }}" title="{{ $post->user->name }}"></a>
      </div>
      <div class="user-post-details">
        <ul class="list-unstyled no-margin">
          <li>
              @if(isset($sharedOwner))
                <a href="{{ url($sharedOwner->user->username) }}" title="{{ '@'.$sharedOwner->user->username }}" data-toggle="tooltip" data-placement="top" class="user-name user">
                {{ $sharedOwner->user->name }}
              </a>
              shared
              @endif

            <a href="{{ url($post->user->username) }}" title="{{ '@'.$post->user->username }}" data-toggle="tooltip" data-placement="top" class="user-name user">
              {{ $post->user->name }}
            </a>
            @if($post->user->verified)
              <span class="verified-badge bg-success">
                    <i class="fa fa-check"></i>
                </span>
            @endif

            @if(isset($sharedOwner))
               's post
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
                              @if(Auth::id() == $post->timeline_id)
                                  your
                              @else
                                  {{ $post->timeline->user->name }}'s
                              @endif                               
                               timline)
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
            @if(isset($sharedOwner))
               <time class="post-time timeago" datetime="{{ $sharedOwner->created_at }}+00:00" title="{{ $sharedOwner->created_at }}+00:00">
                {{ $sharedOwner->created_at }}+00:00
              </time>
            @else

              <!--<time class="post-time" datetime="{{ $post->created_at }}" title="{{ $post->created_at }}">-->
              <!--  {{ date('m/d/Y', strtotime($post->created_at)) }}-->
              <!--</time>-->
              <time class="post-time timeago" datetime="{{ $post->created_at }}+00:00" title="{{ $post->created_at }}+00:00" hidden>
                  {{ $post->created_at }}+00:00
                <!--{{ date('m/d/Y', strtotime($post->created_at)) }}-->
              </time>
            @endif

            @if($post->location != NULL && !isset($sharedOwner))
            {{ trans('common.at') }} <span class="post-place">
              <a target="_blank" href="{{ url('/get-location/'.$post->location) }}">
                <i class="fa fa-map-marker"></i> {{ $post->location }}
              </a>
              </span></li>
            @endif
          </ul>
        </div>
      </div>
    </div>
    @if($post->type == \App\Post::PRICE_TYPE)
        @if($post->user->id != Auth::user()->id && !Auth::user()->PurchasedPostsArr->contains($post->id))
            <div class="panel-body locked-panel-body">
                <div class="locked-content">
                    <div class="locked-content-wrapper">
                        <i class="fa fa-lock" aria-hidden="true"></i>
                        <button class="btn btn-success purchase-post" data-post-id="{{ $post->id }}">Buy</button>
                    </div>
                </div>
            </div>
        @else
              <div data-post_id="{{ $post->id }}" class="panel-body image-with-blur {{ clean($main_description) == ''  ? 'single-image-panel' : '' }}">
                <div class="text-wrapper">

                    <div id="statisticsModal{{ $post->id }}" class="modal fade" role="dialog" tabindex='-1'>
                        <div class="modal-dialog">

                            <div class="modal-content">
                                <div class="modal-body">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h3 class="modal-title">{{ trans('common.post_statistics') }}</h3>
                                    </div>

                                    <div class="b-stats-row__content">
                                        <div class="b-stats-row__label m-border-line m-purchases m-current">
                                            <span class="b-stats-row__name m-dots"> {{ trans('common.purchases') }} </span><span class="b-stats-row__val"> $0.00 </span>
                                        </div>
                                        <div class="b-stats-row__label m-border-line m-viewers m-current">
                                            <span class="b-stats-row__name m-dots"> {{ trans('common.viewers') }} </span><span class="b-stats-row__val"> 0 </span>
                                        </div>
                                        <div class="b-stats-row__label m-border-line m-likes m-current">
                                            <span class="b-stats-row__name m-dots"> {{ trans('common.likes') }} </span><span class="b-stats-row__val"> {{ $post->users_liked->count() }} </span>
                                        </div>
                                        <div class="b-stats-row__label m-border-line m-comments m-current">
                                            <span class="b-stats-row__name m-dots"> {{ trans('common.comments') }} </span><span class="b-stats-row__val"> {{ $post->comments->count() }} </span>
                                        </div>
                                        <div class="b-stats-row__label m-border-line m-tips m-current">
                                            <span class="b-stats-row__name m-dots"> {{ trans('common.tips') }} </span><span class="b-stats-row__val"> $0.00 <!----></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('common.close') }}</button>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div id="shareModal{{ $post->id }}" class="modal fade" role="dialog" tabindex='-1'>
                        <div class="modal-dialog">

                            <div class="modal-content">
                                <div class="modal-body">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h3 class="modal-title">{{ trans('common.copy_embed_post') }}</h3>
                                    </div>
                                    <textarea class="form-control" rows="3">
                                        <iframe src="{{ url('/share-post/'.$post->id) }}" width="600px" height="420px" frameborder="0"></iframe>
                                    </textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('common.close') }}</button>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div id="copyLinkModal{{ $post->id }}" class="modal fade" role="dialog" tabindex='-1'>
                        <div class="modal-dialog">

                            <div class="modal-content">
                                <div class="modal-body">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h3 class="modal-title">{{ trans('common.copy_link_post') }}</h3>
                                    </div>
                                    {{--          <div id="copy-link-content">{{ url('/share-post/'.$post->id) }}</div>--}}
                                    <a href="{{ url('/post/'.$post->id) }}">{{ url('/post/'.$post->id) }}</a>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('common.close') }}</button>
                                </div>
                            </div>

                        </div>
                    </div>
                    
                    <?php
                    $links = preg_match_all("/(?i)\b((?:[a-z][\w-]+:(?:\/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))/", $post->description, $matches);

                    $main_description = $post->description;
                    ?>
                    @foreach($matches[0] as $link)
                        <?php $linkPreview = new LinkPreview(/*$link*/ "www.google.com");
                        $parsed = $linkPreview->getParsed();
                        $data = $link;
                        foreach ($parsed as $parserName => $main_link) {
                            $data = '<div class="row link-preview">
                              <div class="col-md-3">
                                <a target="_blank" href="'.$link.'"><img src="'.$main_link->getImage().'"></a>
                              </div>
                              <div class="col-md-9">
                                <a target="_blank" href="'.$link.'">'.$main_link->getTitle().'</a><br>'.substr($main_link->getDescription(), 0, 500). '...'.'
                              </div>
                            </div>';
                            //echo substr($main_link->getDescription(), 0, 500);
                        }
                        $main_description = str_replace($link, $data, $main_description);
                        ?>
                    @endforeach

                    {{-- Check if subscribed--}}
                    {{--        @if(isset($user) == false || $user->followers->contains(Auth::user()->id) || $user->id == Auth::user()->id || $user->payment == NULL|| ($user->payment != NULL && $user->payment->price == 0))--}}
                    @if(isset($user) == false || $user->followers->contains(Auth::user()->id) || $user->id == Auth::user()->id || $user->price == 0)
                        <p class="post-description">
                            {!! clean($main_description) !!}
                        </p>
                    @endif

                    {{-- Check if subscribed--}}

                    {{--        @if(isset($user) && !$user->followers->contains(Auth::user()->id) && $user->id != Auth::user()->id && $user->payment != NULL && $user->payment->price > 0 )--}}
                    @if(isset($user) && !$user->followers->contains(Auth::user()->id) && $user->id != Auth::user()->id && $user->price > 0 )
                      <div class="post-image-holder post-locked  single-image tag-MARK-A"> 
                        <!-- %PSG: locked -->
                            {{--                <a><img src="{{ url('user/gallery/locked.jpg') }}"  title="{{ $post->user->name }}" alt="{{ $post->user->name }}" onclick="$('#myModal').modal('show')"></a>--}}
                            <a><img src="{{ url('user/gallery/locked.png') }}"  title="{{ $post->user->name }}" alt=""></a>

                            <div class="modal fade" id="myModal" role="dialog">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            <h4 class="modal-title">Subscribe {{$post->user->name}}'s posts</h4>
                                        </div>
                                        <div class="modal-body">
                                            <img src="{{ url('user/gallery/locked.png') }}"  title="{{ $post->user->name }}" alt="{{ $post->user->name }}" style="display: block; margin-left: auto; margin-right: auto">
                                            <p  style="margin-left: auto; margin-right: auto">Monthly Subscribe {{$post->user->price}} US$</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Subscribe</button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    @else
                        <!-- %PSG: UN-locked -->
                        <div class="post-image-holder {{ $post->images()->first() && $post->images()->first()->type == 'image' ? 'single-image' : ''}} tag-MARK-B">

                            {{-- %PSG: Post's images --}}
                            @foreach($post->mediafiles as $mf)
                                @if( $mf->isImage() )
                                  <span
                                    class="tag-post_mf_img {{ $loop->first?'first-image':'hidden' }}" 
                                    style="background-image: url( {{ $post->mediafiles()->first()->filepath }} );">
                                  </span>
                                  <a 
                                    href="{{ $mf->filepath }}" 
                                    class="{{ $loop->first?'first-image':'hidden' }}" 
                                    data-fancybox="gallery.{{$post->id}}">
                                    <img 
                                      src="{{ $mf->filepath }}"  
                                      title="{{ $post->user->name }}" 
                                      alt="">
                                  </a>
                                @endif
                            @endforeach
                            @foreach($post->images()->get() as $index => $postImage)
                                @if($postImage->type=='image')
                                    <span class="{{ $index == 0 ? 'first-image' : 'hidden' }}" style="background-image: url({{ url('user/gallery/'.$post->images()->get()->first()->source) }}); height: 100%; width: 100%; position: absolute; filter: blur(4px); background-size: cover; z-index: 0; background-position: center;"></span>
                                    <a href="{{ url('user/gallery/'.$postImage->source) }}" class="{{ $index == 0 ? 'first-image' : 'hidden' }}" data-fancybox="gallery.{{$post->id}}"><img src="{{ url('user/gallery/'.$postImage->source) }}"  title="{{ $post->user->name }}" alt=""></a>
                                @endif
                            @endforeach
                        </div>

                            {{-- %PSG: Post's videos --}}
                        <div class="post-v-holder">
                            @foreach($post->images()->get() as $index => $postImage)
                                @if($postImage->type=='video')
                                    <div id="unmoved-fixture" class="{{ $index == 0 && clean($main_description) == '' ? 'first-image' : 'hidden' }}">
                                        <video width="100%" height="auto" id="video-target" controls class="video-video-playe">
                                            <source src="{{ url('uploads/user/video/'.$postImage->source) }}"></source>
                                        </video>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                            {{-- %PSG: Post's audio --}}
                        <div class="post-audio-holder">
                            @foreach($post->images()->get() as $postImage)
                                @if($postImage->type=='audio')
                                    <audio controls src="{{ url('uploads/user/audio/'.$postImage->source) }}">
                                        <source src="{{ url('uploads/user/audio/'.$postImage->source) }}"></source>
                                    </audio>
                                @endif
                            @endforeach
                        </div>
                    @endif

                </div>
            <!--@if($post->youtube_video_id)-->
            <!--<iframe  src="https://www.youtube.com/embed/{{ $post->youtube_video_id }}" frameborder="0" allowfullscreen></iframe>-->
                <!--@endif-->
            <!--@if($post->soundcloud_id)-->
                <!--<div class="soundcloud-wrapper">-->
            <!--  <iframe width="100%" height="166" scrolling="no" frameborder="no" src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/{{ $post->soundcloud_id }}&amp;color=ff5500&amp;auto_play=false&amp;hide_related=false&amp;show_comments=true&amp;show_user=true&amp;show_reposts=false"></iframe>-->
                <!--</div>-->
                <!--@endif-->

                @if(isset($user) == false || $user->followers->contains(Auth::user()->id) || $user->id == Auth::user()->id || $user->price == 0)
                    <ul class="actions-count list-inline">

                        <li>
                            @if($post->users_liked()->count() > 0)
                                <?php
                                $liked_ids = $post->users_liked->pluck('id')->toArray();
                                $liked_names = $post->users_liked->pluck('name')->toArray();
                                ?>
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
                            <?php
                            $shared_ids = $post->shares->pluck('id')->toArray();
                            $shared_names = $post->shares->pluck('name')->toArray(); ?>
                            <li>
                                <a href="#" class="show-users-modal" data-html="true" data-heading="{{ trans('common.shares') }}"  data-users="{{ implode(',', $shared_ids) }}" data-original-title="{{ implode('<br />', $shared_names) }}"><span class="count-circle"><i class="fa fa-share"></i></span> {{ $post->shares->count() }} {{ trans('common.shares') }}</a>
                            </li>
                        @endif

                    </ul>
                @endif
            </div>                
        @endif
      
    @else
        <div data-post_id="{{ $post->id }}" class="panel-body image-with-blur {{ clean($main_description) == '' ? 'single-image-panel' : '' }}">
            <div class="text-wrapper">

                <div id="statisticsModal{{ $post->id }}" class="modal fade" role="dialog" tabindex='-1'>
                    <div class="modal-dialog">

                        <div class="modal-content">
                            <div class="modal-body">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h3 class="modal-title">{{ trans('common.post_statistics') }}</h3>
                                </div>
                                <div class="b-stats-row__content">
                                    <div class="b-stats-row__label m-border-line m-purchases m-current">
                                        <span class="b-stats-row__name m-dots"> {{ trans('common.purchases') }} </span><span class="b-stats-row__val"> $0.00 </span>
                                    </div>
                                    <div class="b-stats-row__label m-border-line m-viewers m-current">
                                        <span class="b-stats-row__name m-dots"> {{ trans('common.viewers') }} </span><span class="b-stats-row__val"> 0 </span>
                                    </div>
                                    <div class="b-stats-row__label m-border-line m-likes m-current">
                                        <span class="b-stats-row__name m-dots"> {{ trans('common.likes') }} </span><span class="b-stats-row__val"> {{ $post->users_liked->count() }} </span>
                                    </div>
                                    <div class="b-stats-row__label m-border-line m-comments m-current">
                                        <span class="b-stats-row__name m-dots"> {{ trans('common.comments') }} </span><span class="b-stats-row__val"> {{ $post->comments->count() }} </span>
                                    </div>
                                    <div class="b-stats-row__label m-border-line m-tips m-current">
                                        <span class="b-stats-row__name m-dots"> {{ trans('common.tips') }} </span><span class="b-stats-row__val"> $0.00 <!----></span>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('common.close') }}</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="shareModal{{ $post->id }}" class="modal fade" role="dialog" tabindex='-1'>
                    <div class="modal-dialog">

                        <div class="modal-content">
                            <div class="modal-body">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h3 class="modal-title">{{ trans('common.copy_embed_post') }}</h3>
                                </div>
                                <textarea class="form-control" rows="3">
                                    <iframe src="{{ url('/share-post/'.$post->id) }}" width="600px" height="420px" frameborder="0"></iframe>
                                </textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('common.close') }}</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="copyLinkModal{{ $post->id }}" class="modal fade" role="dialog" tabindex='-1'>
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-body">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h3 class="modal-title">{{ trans('common.copy_link_post') }}</h3>
                                </div>
                                {{--          <div id="copy-link-content">{{ url('/share-post/'.$post->id) }}</div>--}}
                                <a href="{{ url('/post/'.$post->id) }}">{{ url('/post/'.$post->id) }}</a>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('common.close') }}</button>
                            </div>
                        </div>

                    </div>
                </div>

                <?php
                $links = preg_match_all("/(?i)\b((?:[a-z][\w-]+:(?:\/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))/", $post->description, $matches);

                $main_description = $post->description;
                ?>
                @foreach($matches[0] as $link)
                    <?php $linkPreview = new LinkPreview(/*$link*/ "www.google.com");
                    $parsed = $linkPreview->getParsed();
                    $data = $link;
                    foreach ($parsed as $parserName => $main_link) {
                        $data = '<div class="row link-preview">
                              <div class="col-md-3">
                                <a target="_blank" href="'.$link.'"><img src="'.$main_link->getImage().'"></a>
                              </div>
                              <div class="col-md-9">
                                <a target="_blank" href="'.$link.'">'.$main_link->getTitle().'</a><br>'.substr($main_link->getDescription(), 0, 500). '...'.'
                              </div>
                            </div>';
                        //echo substr($main_link->getDescription(), 0, 500);
                    }
                    $main_description = str_replace($link, $data, $main_description);
                    ?>
                @endforeach

                {{-- Check if subscribed--}}
                {{--        @if(isset($user) == false || $user->followers->contains(Auth::user()->id) || $user->id == Auth::user()->id || $user->payment == NULL|| ($user->payment != NULL && $user->payment->price == 0))--}}
                @if(isset($user) == false || $user->followers->contains(Auth::user()->id) || $user->id == Auth::user()->id || $user->price == 0)
                    <p class="post-description">
                        {!! clean($main_description) !!}
                    </p>
                @endif

                {{-- Check if subscribed--}}

                {{--        @if(isset($user) && !$user->followers->contains(Auth::user()->id) && $user->id != Auth::user()->id && $user->payment != NULL && $user->payment->price > 0 )--}}
                @if(isset($user) && !$user->followers->contains(Auth::user()->id) && $user->id != Auth::user()->id && $user->price > 0 )
                    <div class="post-image-holder post-locked  single-image tag-MARK-C">
                        {{--                <a><img src="{{ url('user/gallery/locked.jpg') }}"  title="{{ $post->user->name }}" alt="{{ $post->user->name }}" onclick="$('#myModal').modal('show')"></a>--}}
                        <a><img src="{{ url('user/gallery/locked.png') }}"  title="{{ $post->user->name }}" alt="{{ $post->user->name }}"></a>

                        
                        <div class="modal fade" id="myModal" role="dialog">
                            <div class="modal-dialog">

                                
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title">Subscribe {{$post->user->name}}'s posts</h4>
                                    </div>
                                    <div class="modal-body">
                                        <img src="{{ url('user/gallery/locked.png') }}"  title="{{ $post->user->name }}" alt="{{ $post->user->name }}" style="display: block; margin-left: auto; margin-right: auto">
                                        <p  style="margin-left: auto; margin-right: auto">Monthly Subscribe {{$post->user->price}} US$</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Subscribe</button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                @else
                      @php
                      if ($post->id === 245) {
                        //dd('MARK-D', $post->mediafiles->toArray());
                      }
                      @endphp

                    <div class="post-image-holder {{  $post->mediafiles()->first() && $post->mediafiles()->first()->isImage()?'single-image':''}} tag-MARK-D">
                        {{-- %PSG: Post's images --}}
                        @foreach($post->mediafiles as $mf)
                            @if( $mf->isImage() )
                              <span
                                class="tag-post_mf_img {{ $loop->first?'first-image':'hidden' }}" 
                                style="background-image: url( {{ $post->mediafiles()->first()->filepath }} );">
                              </span>
                              <a 
                                href="{{ $mf->filepath }}" 
                                class="{{ $loop->first?'first-image':'hidden' }}" 
                                data-fancybox="gallery.{{$post->id}}">
                                <img 
                                  src="{{ $mf->filepath }}"  
                                  title="{{ $post->user->name }}" 
                                  alt="">
                              </a>
                            @endif
                        @endforeach

                        @foreach($post->images()->get() as $index => $postImage)
                            @if($postImage->type=='image')
                                <span class="{{ $index == 0 ? 'first-image' : 'hidden' }}" style="background-image: url({{ url('user/gallery/'.$post->images()->get()->first()->source) }}); height: 100%; width: 100%; position: absolute; filter: blur(4px); background-size: cover; z-index: 0; background-position: center;"></span>
                                <a href="{{ url('user/gallery/'.$postImage->source) }}" class="{{ $index == 0 ? 'first-image' : 'hidden' }}" data-fancybox="gallery.{{$post->id}}"><img src="{{ url('user/gallery/'.$postImage->source) }}"  title="{{ $post->user->name }}" alt=""></a>
                            @endif
                        @endforeach
                    </div>

                    <div class="post-v-holder">
                        @foreach($post->images()->get() as $index => $postImage)
                            @if(isset($postImage->type) && $postImage->type=='video')
                                <div id="unmoved-fixture" class="{{ $index == 0 && clean($main_description) == '' ? 'first-image' : 'hidden' }}">
                                    <video width="100%" height="auto" id="video-target" controls class="video-video-playe">
                                        <source src="{{ url('uploads/user/video/'.$postImage->source) }}"></source>
                                    </video>
                                </div>

                            @endif
                        @endforeach
                    </div>
                
                    <div class="post-audio-holder">
                        @foreach($post->images()->get() as $postImage)
                            @if(isset($postImage->type) && $postImage->type=='audio')
                                <audio controls src="{{ url('uploads/user/audio/'.$postImage->source) }}">
                                    <source src="{{ url('uploads/user/audio/'.$postImage->source) }}"></source>
                                </audio>
                            @endif
                        @endforeach
                    </div>
                @endif

            </div>
        <!--@if($post->youtube_video_id)-->
        <!--<iframe  src="https://www.youtube.com/embed/{{ $post->youtube_video_id }}" frameborder="0" allowfullscreen></iframe>-->
            <!--@endif-->
        <!--@if($post->soundcloud_id)-->
            <!--<div class="soundcloud-wrapper">-->
        <!--  <iframe width="100%" height="166" scrolling="no" frameborder="no" src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/{{ $post->soundcloud_id }}&amp;color=ff5500&amp;auto_play=false&amp;hide_related=false&amp;show_comments=true&amp;show_user=true&amp;show_reposts=false"></iframe>-->
            <!--</div>-->
            <!--@endif-->

            @if(isset($user) == false || $user->followers->contains(Auth::user()->id) || $user->id == Auth::user()->id || $user->price == 0)
                <ul class="actions-count list-inline">

                    <li>
                        @if($post->users_liked()->count() > 0)
                            <?php
                            $liked_ids = $post->users_liked->pluck('id')->toArray();
                            $liked_names = $post->users_liked->pluck('name')->toArray();
                            ?>
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
                        <?php
                        $shared_ids = $post->shares->pluck('id')->toArray();
                        $shared_names = $post->shares->pluck('name')->toArray(); ?>
                        <li>
                            <a href="#" class="show-users-modal" data-html="true" data-heading="{{ trans('common.shares') }}"  data-users="{{ implode(',', $shared_ids) }}" data-original-title="{{ implode('<br />', $shared_names) }}"><span class="count-circle"><i class="fa fa-share"></i></span> {{ $post->shares->count() }} {{ trans('common.shares') }}</a>
                        </li>
                    @endif

                </ul>
            @endif
        </div>
    @endif

    <?php
    $display_comment = false;
    $user_follower = $post->chkUserFollower(Auth::user()->id,$post->user_id);
    $user_setting = $post->chkUserSettings($post->user_id);

    if($user_follower != NULL)
    {
      if($user_follower == "only_follow") {
        $display_comment = true;
      }elseif ($user_follower == "everyone") {
        $display_comment = true;
      }
    }
    else{
      if($user_setting){
        if($user_setting == "everyone"){
          $display_comment = true;
        }
      }
    }

    ?>

     {{-- Check if subscribed--}}
{{--     @if(isset($user) == false || $user->followers->contains(Auth::user()->id) || $user->id == Auth::user()->id  || $user->payment == NULL|| ($user->payment != NULL && $user->payment->price == 0))--}}
    @if(isset($user) == false || $user->followers->contains(Auth::user()->id) || $user->id == Auth::user()->id  || $user->price == 0)
    @if($post->type == \App\Post::PRICE_TYPE)
        @if($post->user->id == Auth::user()->id || Auth::user()->PurchasedPostsArr->contains($post->id))
            <div class="panel-footer fans">
                    <ul class="list-inline footer-list">
                        @if(!$post->users_liked->contains(Auth::user()->id))
                            <li>
                                <a href="#" class="like-post like-{{ $post->id }}" data-post-id="{{ $post->id }}"><i class="fa fa-heart-o"></i></a>
                            </li>
                            <li class="hidden"><a href="#" class="like-post unlike-{{ $post->id }}" data-post-id="{{ $post->id }}"><i class="fa fa-heart"></i></a></li>
                        @else
                            <li class="hidden"><a href="#" class="like-post like-{{ $post->id }}" data-post-id="{{ $post->id }}"><i class="fa fa-heart-o"></i></a></li>
                            <li><a href="#" class="like-post unlike-{{ $post->id }}" data-post-id="{{ $post->id }}"><i class="fa fa-heart"></i></i></a></li>
                        @endif
                        @if($display_comment)
                        <li><a href="#" class="show-comments"><i class="fa fa-comment-o"></i></a></li>
                        @endif
                        @if(Auth::user()->id != $post->user_id)
                            @if(!$post->users_shared->contains(Auth::user()->id))
                                <li><a href="#" class="share-post share" data-post-id="{{ $post->id }}"><i class="fa fa-share-square-o"></i></a></li>
                                <li class="hidden"><a href="#" class="share-post shared" data-post-id="{{ $post->id }}"><i class="fa fa fa-share-square"></i></a></li>
                            @else
                                <li class="hidden"><a href="#" class="share-post share" data-post-id="{{ $post->id }}"><i class="fa fa-share-square-o"></i></a></li>
                                <li><a href="#" class="share-post shared" data-post-id="{{ $post->id }}"><i class="fa fa fa-share-square"></i></a></li>
                            @endif
                            <li>
                                <a href="#" class="send-tip-post" data-toggle="modal" data-target="#sendTipModal{{ $post->id }}"><i class="fa fa-dollar"></i></a>
                            </li>
                         @endif
                     </ul>
                 </div>       
        @endif
    @else
    <div class="panel-footer fans">
        <ul class="list-inline footer-list">
            @if(!$post->users_liked->contains(Auth::user()->id))
                <li>
                     <a href="#" class="like-post like-{{ $post->id }}" data-post-id="{{ $post->id }}"><i class="fa fa-heart-o"></i></a>
                </li>
                <li class="hidden">
                    <a href="#" class="like-post unlike-{{ $post->id }}" data-post-id="{{ $post->id }}"><i class="fa fa-heart"></i></a>
                </li>
            @else
                <li class="hidden">
                    <a href="#" class="like-post like-{{ $post->id }}" data-post-id="{{ $post->id }}"><i class="fa fa-heart-o"></i></a>
                </li>
                <li>
                    <a href="#" class="like-post unlike-{{ $post->id }}" data-post-id="{{ $post->id }}"><i class="fa fa-heart"></i></a>
                </li>
            @endif
            @if($display_comment)
                <li>
                    <a href="#" class="show-comments"><i class="fa fa-comment-o"></i></a>
                </li>
            @endif
            @if(Auth::user()->id != $post->user_id)
                @if(!$post->users_shared->contains(Auth::user()->id))
                    <li>
                         <a href="#" class="share-post share" data-post-id="{{ $post->id }}"><i class="fa fa-share-square-o"></i></a>
                    </li>
                    <li class="hidden">
                        <a href="#" class="share-post shared" data-post-id="{{ $post->id }}"><i class="fa fa fa-share-square"></i></a>
                    </li>
                @else
                    <li class="hidden">
                        <a href="#" class="share-post share" data-post-id="{{ $post->id }}"><i class="fa fa-share-square-o"></i></a>
                    </li>
                    <li>
                        <a href="#" class="share-post shared" data-post-id="{{ $post->id }}"><i class="fa fa fa-share-square"></i></a>
                    </li>
                @endif
                <li>
                     <a href="#" class="send-tip-post" data-toggle="modal" data-target="#sendTipModal{{ $post->id }}"><i class="fa fa-dollar"></i></a>
                </li>
            @endif
        </ul>
    </div>
    @endif

    @if($post->comments->count() > 0 || $post->user_id == Auth::user()->id || $display_comment == "everyone" || $display_comment == true)
      <div class="comments-section all_comments" style="display:none">
        <div class="comments-wrapper">
          <div class="to-comment">
            @if($display_comment == "only_follow" || $display_comment == "everyone" || $user_setting == "everyone" || $post->user_id == Auth::user()->id)
            <div class="commenter-avatar">
              <a href="#"><img src="{{ Auth::user()->avatar->filepath }}" alt="{{ Auth::user()->name }}" title="{{ Auth::user()->name }}"></a>
            </div>
            <div class="comment-textfield">
              <form action="#" class="comment-form" method="post" files="true" enctype="multipart/form-data" id="comment-form">
                <div class="comment-holder">
                  <input class="form-control post-comment" autocomplete="off" data-post-id="{{ $post->id }}" name="post_comment" placeholder="{{ trans('messages.comment_placeholder') }}" >

                    <input type="file" class="comment-images-upload hidden" accept="image/jpeg,image/png,image/gif" name="comment_images_upload">
                     <ul class="list-inline meme-reply hidden">
                      <li><a href="#" id="imageComment"><i class="fa fa-camera" aria-hidden="true"></i></a></li>
                      {{-- <li><a href="#"><i class="fa fa-smile-o" aria-hidden="true"></i></a></li> --}}
                    </ul>
                </div>
                  <div id="comment-image-holder"></div>
              </form>
            </div>
            <div class="clearfix"></div>
            @endif
          </div>

          <div class="comments post-comments-list"> 
            @if($post->comments->count() > 0)
            @foreach($post->comments as $comment)
            {!! Theme::partial('comment',compact('comment','post')) !!}
            @endforeach
            @endif
          </div>
        </div>
      </div>
    @endif
    @endif
  </div>

<div id="sendTipModal{{ $post->id }}" class="tip-modal modal fade subscriberFilterModal" role="dialog" tabindex='1'>

    <input type="hidden" value="{{$post->id}}" id="post-id">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 style="margin: 0;">Send a Tip</h3>
                <button type="button" style="" class="close close-post-modal" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body no-padding">
                <div class="panel panel-default panel-post animated" style="margin-bottom: 0">
                    <div class="panel-heading no-bg">
                        <div class="post-author">
                            <div class="user-avatar">
                              <a href="{{ url($post->user->username) }}"><img src="{{ $post->user->avatar->filepath }}" alt="{{ $post->user->name }}" title="{{ $post->user->name }}"></a>
                            </div>
                            <div class="user-post-details">
                                <ul class="list-unstyled no-margin">
                                    <li>
                                        <a href="{{ url($post->user->username) }}" title="{{ '@'.$post->user->username }}" data-toggle="tooltip" data-placement="top" class="user-name user">
                                            {{ $post->user->name }}
                                        </a>
                                        @if($post->user->verified)
                                            <span class="verified-badge bg-success">
                                                <i class="fa fa-check"></i>
                                            </span>
                                        @endif
                                    </li>
                                    <li>
                                        {{ '@'.$post->user->username }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <ul>
                            <li>
                                <p>Tip Amount:</p>
                                <div class="total-tipped">
                                    <span class="decrement"><i class="fa fa-minus" aria-hidden="true"></i></span>
                                    <span class="increment"><i class="fa fa-plus" aria-hidden="true"></i></span>
                                    <input min="0" type="number" id="etTipAmount" value="10" data-id="2"  name="total_tipped" class="filter-input total-tipped-input  form-control etTipAmount">
                                    <div class="text-wrapper"></div>
                                </div>
                            </li>
                        </ul>
                        <textarea name="tip_note" id="tipNote" cols="60" rows="5" style="width: 100%" placeholder="Write a message..."></textarea>
                    </div>
                    <div class="panel-footer">
                      {{-- <a href="#" id="cancelSendTip" class="text-primary btn" data-dismiss="modal">{{ trans('common.cancel') }}</a> --}}
                        @if(Auth::user()->is_payment_set)
                            <button type="button" id="sendTip" class="btn btn-primary sendTip" >{{ trans('common.send_tip') }}</button>
                        @else
                            <a href="{{url(Auth::user()->username).'/settings/addpayment' }}" style="width: auto" id="addPayment" class="btn btn-warning">{{ trans('common.add_payment') }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

  @if(isset($next_page_url))
  <a class="jscroll-next hidden" href="{{ $next_page_url }}">{{ trans('messages.get_more_posts') }}</a>
  @endif

<script type="text/javascript">

    // function getPoster() {
    //   var pop = Popcorn( "#video-target" ),
    //     poster;
    //     pop.listen( "canplayall", function() {
    //         this.currentTime( 1 ).capture();
    //     })
    // }

    // getPoster();
    
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
