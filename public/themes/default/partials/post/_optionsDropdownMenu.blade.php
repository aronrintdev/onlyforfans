<!-- %VIEW: themes/default/partials/post/_optionsDropdownMenu -->
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

        @if($sessionUser->id == $post->user->id)
          <li class="main-link">
            <a href="#" data-post-id="{{ $post->id }}" class="edit-post">
              <i class="fa fa-edit" aria-hidden="true"></i>{{ trans('common.edit') }}
              <span class="small-text">{{ trans('messages.edit_text') }}</span>
            </a>
          </li>
        @endif

        @if(($sessionUser->id == $post->user->id) || ($post->timeline_id == $sessionUser->timeline_id))
          <li class="main-link">
            <a href="#" class="delete-post" data-post-id="{{ $post->id }}">
              <i class="fa fa-trash" aria-hidden="true"></i>{{ trans('common.delete') }}
              <span class="small-text">{{ trans('messages.delete_text') }}</span>
            </a>
          </li>
        @endif

        @if($sessionUser->id != $post->user->id)
          <li class="main-link">
            <a href="#" class="hide-post" data-post-id="{{ $post->id }}">
              <i class="fa fa-eye-slash" aria-hidden="true"></i>{{ trans('common.hide_notifications') }}
              <span class="small-text">{{ trans('messages.hide_notification_text') }}</span>
            </a>
          </li>

          <li class="main-link">
            <a href="#" class="save-post" data-post-id="{{ $post->id }}">
              <i class="fa fa-save" aria-hidden="true"></i>
              @if(!$sessionUser->postsSaved->contains($post->id))
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
              @if(!$sessionUser->postsPinned->contains($post->id))
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

        @if(($sessionUser->id == $post->user->id) || ($post->timeline_id == $sessionUser->timeline_id))
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
