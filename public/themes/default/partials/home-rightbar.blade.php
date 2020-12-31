<!-- %VIEW: themes/default/paritals/home-rightbar --> 
@php
$sessionUser = Auth::user();
//dd($sessionUser->toArray());
//dd($sessionUser->cover->toArray());
@endphp

<div class="right-side-section">
  <div class="panel panel-default">
    <div class="panel-body nopadding">
      <div class="mini-profile fans">
        <div class="background">
          <div class="widget-bg tag-cover-img">
            <a href="{{ url($sessionUser->username) }}">
              <img src=" @if($sessionUser->cover) {{ $sessionUser->cover->filepath }} @else {{ url('user/cover/default-cover-user.png') }} @endif" alt="{{ $sessionUser->name }}" title="{{ $sessionUser->name }}">
            </a>
          </div>
          <div class="avatar-img">
            <a href="{{ url($sessionUser->username) }}">	
              <img src="{{ $sessionUser->avatar->filepath }}" alt="{{ $sessionUser->name }}" title="{{ $sessionUser->name }}">
            </a>
          </div>
        </div>
        <div class="avatar-profile">
          <div class="avatar-details" style="width:280px;display:inline-block;">
            <h2 class="avatar-name">
              <a href="{{ url($sessionUser->username) }}">
                {{ $sessionUser->name }}
              </a>
              @if($sessionUser->verified)
                <svg width=".7em" height=".7em" viewBox="0 0 16 16" class="bi bi-patch-check-fll" fill="#298ad3" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" d="M10.067.87a2.89 2.89 0 0 0-4.134 0l-.622.638-.89-.011a2.89 2.89 0 0 0-2.924 2.924l.01.89-.636.622a2.89 2.89 0 0 0 0 4.134l.637.622-.011.89a2.89 2.89 0 0 0 2.924 2.924l.89-.01.622.636a2.89 2.89 0 0 0 4.134 0l.622-.637.89.011a2.89 2.89 0 0 0 2.924-2.924l-.01-.89.636-.622a2.89 2.89 0 0 0 0-4.134l-.637-.622.011-.89a2.89 2.89 0 0 0-2.924-2.924l-.89.01-.622-.636zm.287 5.984a.5.5 0 0 0-.708-.708L7 8.793 5.854 7.646a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0l3-3z"/>
                </svg>
              @endif
            </h2>
            <h4 class="avatar-mail">
              <a href="{{ url($sessionUser->username) }}">
                {{ '@'.$sessionUser->username }}
              </a>
            </h4>
          </div>
          <div class="go-live" style="width:30px;display:inline-block;">
            <a href="#"><svg data-toggle="tooltip" data-placement="top" title="Go Live" width="2em" height="2em" viewBox="0 0 16 16" class="bi bi-broadcast-pin" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M3.05 3.05a7 7 0 0 0 0 9.9.5.5 0 0 1-.707.707 8 8 0 0 1 0-11.314.5.5 0 0 1 .707.707zm2.122 2.122a4 4 0 0 0 0 5.656.5.5 0 0 1-.708.708 5 5 0 0 1 0-7.072.5.5 0 0 1 .708.708zm5.656-.708a.5.5 0 0 1 .708 0 5 5 0 0 1 0 7.072.5.5 0 1 1-.708-.708 4 4 0 0 0 0-5.656.5.5 0 0 1 0-.708zm2.122-2.12a.5.5 0 0 1 .707 0 8 8 0 0 1 0 11.313.5.5 0 0 1-.707-.707 7 7 0 0 0 0-9.9.5.5 0 0 1 0-.707zM6 8a2 2 0 1 1 2.5 1.937V15.5a.5.5 0 0 1-1 0V9.937A2 2 0 0 1 6 8z"/>
              </svg></a>
          </div>
        </div>
        <ul class="activity-list list-inline">
          <li>
            <a href="{{ url($sessionUser->username.'/posts') }}">
              <div class="activity-name">
                {{ trans('common.posts') }}
              </div>
              <div class="activity-count">
                {{ count($sessionUser->posts()->where('active', 1)->get()) }}
              </div>
            </a>
          </li>
          <li>
            <a href="{{ url($sessionUser->username.'/followers') }}">
              <div class="activity-name">
                {{ trans('common.followers') }} 
              </div>
              <div class="activity-count">
                {{ $sessionUser->followers->count() }}
              </div>
            </a>
          </li>
          <li>
            <a href="{{ url($sessionUser->username.'/following') }}">
              <div class="activity-name">
                {{ trans('common.following') }}
              </div>
              <div class="activity-count">
                {{ $sessionUser->following->count() }}
              </div>
            </a>
          </li>
          <li>
            <a href="{{ url($sessionUser->username.'/settings/earnings') }}">
              <div class="activity-name">
                Earnings
              </div>
              <div class="activity-count">
                ${{ number_format($totalTip + $subscriptionAmount) }}
              </div>
            </a>
          </li>
        </ul>
      </div>							
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-heading no-bg">
      <h3 class="panel-title">
        {{ trans('common.suggested_people') }}
      </h3>
    </div>
    <div class="panel-body" style="margin-top:-30px;">
      <div class="user-follow fans">
        @if($suggested_users != "")
          @foreach($suggested_users as $suggested_user)
            <div class="media">
              <div class="mini-profile fans user-list-item">
                <div class="background">
                  <div class="widget-bg">
                    <a href="{{ url($suggested_user->username) }}">
                      <img src=" @if($suggested_user->cover->filepath) {{ $suggested_user->cover->filepath }} @else {{ url('user/cover/default-cover-user.png') }} @endif" alt="{{ $suggested_user->name }}" title="{{ $suggested_user->name }}">
                    </a>
                  </div>
                  <div class="avatar-img">
                    <a href="{{ url($suggested_user->username) }}">
                      <img src="{{ $suggested_user->avatar->filepath }}" alt="{{ $suggested_user->name }}" title="{{ $suggested_user->name }}">
                    </a>
                  </div>
                </div>
                <div class="avatar-profile">
                  <div class="avatar-details">
                    <h2 class="avatar-name">
                      <a href="{{ url($suggested_user->username) }}">
                        {{ $suggested_user->name }}
                      </a>
                      @if($suggested_user->verified)
                        <span class="verified-badge bg-success">
                          <i class="fa fa-check"></i>
                        </span>
                      @endif
                    </h2>
                    <h4 class="avatar-mail">
                      <a href="{{ url($suggested_user->username) }}">
                        {{ '@'.$suggested_user->username }}
                      </a>
                    </h4>
                  </div>      
                </div>
              </div>
            </div>
          @endforeach
        @else
          <div class="mt-3 alert alert-warning">
            {{ trans('messages.no_suggested_users') }}
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
