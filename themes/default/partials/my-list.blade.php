<!-- %VIEW: themes/default/paritals/my-list --> 
<div class="user-follow fans row">
    <!-- Each user is represented with media block -->

    @if($saved_users != "")

        @foreach($saved_users as $suggested_user)

            <div class="col-md-6 col-lg-4">
                <div class="media user-list-item">

                    <div class="mini-profile fans">
                        <div class="background">
                            <div class="widget-bg">
                                <a href="{{ url($suggested_user->username) }}">
                                  <img src=" @if($suggested_user->cover) {{ $suggested_user->cover->filepath }} @else {{ url('user/cover/default-cover-user.png') }} @endif" alt="{{ $suggested_user->name }}" title="{{ $suggested_user->name }}">
                                </a>
                            </div>
                            <div class="avatar-img">
                                <a href="{{ url($suggested_user->username) }}">
                                    <img src="{{ $suggested_user->avatar }}" alt="{{ $suggested_user->name }}" title="{{ $suggested_user->name }}">
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
            </div>
        @endforeach
    @else
        <div class="alert alert-warning">
            {{ trans('messages.no_suggested_users') }}
        </div>
    @endif

</div>
