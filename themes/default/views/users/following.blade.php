<!-- main-section -->
@php
  $sessionUser = Auth::user();
$user = $creator;
@endphp

<!-- %VIEW: themes/default/views/users/following -->
<div class="container">

  <section class="row">

    <div class="col-md-12">
      {!! Theme::partial('user-header',compact('timeline', 'liked_post', 'user','followRequests','following_count', 'followers_count','follow_confirm','user_post','joined_groups_count','guest_events')) !!}				

      <section class="row">
        <div class="timeline">

          <div class="col-md-4">
            {!! Theme::partial('user-leftbar',compact('timeline','user','user_events')) !!}
          </div>

          <div class="col-md-8">

            <div class="panel panel-default">

              <div class="panel-heading no-bg panel-settings">
                <h3 class="panel-title">Following</h3>
              </div>

              <div class="panel-body">

                <ul class="list-group page-likes">
                  @forelse($followed_timelines as $t)
                    <li class="list-group-item holder">
                      <div class="connect-link side-left">
                        <a href="{{ url('/'.$t->username) }}">													
                          <img src="{{ $t->avatar->filepath }}" alt="{{ $t->name }}" class="img-icon img-30" title="{{ $t->name }}"> {{ $t->name }}
                        </a>
                        @if($t->verified)
                          <span class="verified-badge bg-success"><i class="fa fa-check"></i></span>
                        @endif
                        @if($t->pivot->referral)
                          <span class="btn btn-to-follow btn-default">{{ $t->pivot->referral }}</span>
                        @endif
                      </div>
                      @if ( $timeline->isOwnedByUser($sessionUser) )
                        <div class="side-right follow-links">
                          @if(!$creator->followedtimelines->contains($t->id))
                            <div class="left-col"><a href="#" class="btn btn-to-follow btn-default follow-user follow" data-price="{{ $t->price }}"  data-timeline-id="{{ $t->timeline_id }}"><i class="fa fa-heart"></i> {{ trans('common.follow') }} </a></div>
                            <div class="left-col hidden"><a href="#" class="btn btn-success unfollow " data-price="{{ $t->price }}" data-timeline-id="{{ $t->timeline_id }}"><i class="fa fa-check"></i>{{ trans('common.following') }}</a></div>
                          @else
                            <div class="left-col hidden"><a href="#" class="btn btn-to-follow btn-default follow-user follow " data-price="{{ $t->price }}"  data-timeline-id="{{ $t->timeline_id }}"><i class="fa fa-heart"></i> {{ trans('common.follow') }}</a></div>
                            <div class="left-col"><a href="#" class="btn btn-success unfollow" data-price="{{ $t->price }}"  data-timeline-id="{{ $t->timeline_id }}"><i class="fa fa-check"></i> {{ trans('common.following') }}</a></div>
                          @endif
                        </div>
                      @endif
                      <div class="clearfix"></div>
                    </li>
                  @empty
                    <li class="list-group-item holder">
                      <div class="alert alert-warning">{{ trans('messages.no_following') }}</div>
                    </li>
                  @endforelse
                </ul>
              </div>
            </div>

            <div class="panel panel-default">

              <div class="panel-heading no-bg panel-settings">
                <h3 class="panel-title">Subscribed</h3>
              </div>

              <div class="panel-body">

                <ul class="list-group page-likes">
                  @forelse($subscribed_timelines as $t)
                    <li class="list-group-item holder">
                      <div class="connect-link side-left">
                        <a href="{{ url('/'.$t->username) }}">													
                          <img src="{{ $t->avatar->filepath }}" alt="{{ $t->name }}" class="img-icon img-30" title="{{ $t->name }}"> {{ $t->name }}
                        </a>
                        @if($t->verified)
                          <span class="verified-badge bg-success"><i class="fa fa-check"></i></span>
                        @endif
                        @if($t->pivot->referral)
                          <span class="btn btn-to-follow btn-default">{{ $t->pivot->referral }}</span>
                        @endif
                      </div>
                      @if ( $timeline->isOwnedByUser($sessionUser) )
                        <div class="side-right follow-links">
                          @if(!$creator->followedtimelines->contains($t->id))
                            <div class="left-col"><a href="#" class="btn btn-to-follow btn-default follow-user follow" data-price="{{ $t->price }}"  data-timeline-id="{{ $t->timeline_id }}"><i class="fa fa-heart"></i> {{ trans('common.follow') }} </a></div>
                            <div class="left-col hidden"><a href="#" class="btn btn-success unfollow " data-price="{{ $t->price }}" data-timeline-id="{{ $t->timeline_id }}"><i class="fa fa-check"></i>{{ trans('common.following') }}</a></div>
                          @else
                            <div class="left-col hidden"><a href="#" class="btn btn-to-follow btn-default follow-user follow " data-price="{{ $t->price }}"  data-timeline-id="{{ $t->timeline_id }}"><i class="fa fa-heart"></i> {{ trans('common.follow') }}</a></div>
                            <div class="left-col"><a href="#" class="btn btn-success unfollow" data-price="{{ $t->price }}"  data-timeline-id="{{ $t->timeline_id }}"><i class="fa fa-check"></i> {{ trans('common.following') }}</a></div>
                          @endif
                        </div>
                      @endif
                      <div class="clearfix"></div>
                    </li>
                  @empty
                    <li class="list-group-item holder">
                      <div class="alert alert-warning">{{ trans('messages.no_following') }}</div>
                    </li>
                  @endforelse
                </ul>

              </div><!-- /panel-body -->

            </div>
          </div>

        </div>

      </section>

    </div>

    <div class="col-md-2">
      {!! Theme::partial('timeline-rightbar') !!}
    </div>

  </section>

</div>
