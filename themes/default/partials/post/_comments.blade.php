<!-- %VIEW: themes/default/partials/post/_comments -->
<div class="comments-section all_comments" style="display:none">
  <div class="comments-wrapper">
    <div class="to-comment">
      @if($post->isCommentSectionShown($sessionUser) || $user_setting == "everyone" || $post->user_id == $sessionUser->id)
        <div class="commenter-avatar">
          <a href="#"><img src="{{ $sessionUser->avatar->filepath }}" alt="{{ $sessionUser->name }}" title="{{ $sessionUser->name }}"></a>
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
