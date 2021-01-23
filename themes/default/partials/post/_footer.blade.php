<!-- %VIEW: themes/default/partials/post/_footer -->
@if($post->type == \App\Post::PRICE_TYPE)
  @if ( $post->isViewableByUser($sessionUser) ) 
    <div class="panel-footer fans">
      <ul class="list-inline footer-list">
        @if(!$post->users_liked->contains($sessionUser->id))
          <li>
            <a href="#" class="like-post like-{{ $post->id }}" data-post-id="{{ $post->id }}"><i class="fa fa-heart-o"></i></a>
          </li>
          <li class="hidden"><a href="#" class="like-post unlike-{{ $post->id }}" data-post-id="{{ $post->id }}"><i class="fa fa-heart"></i></a></li>
        @else
          <li class="hidden"><a href="#" class="like-post like-{{ $post->id }}" data-post-id="{{ $post->id }}"><i class="fa fa-heart-o"></i></a></li>
          <li><a href="#" class="like-post unlike-{{ $post->id }}" data-post-id="{{ $post->id }}"><i class="fa fa-heart"></i></i></a></li>
        @endif
        @if( $post->isCommentSectionShown($sessionUser) )
          <li><a href="#" class="show-comments"><i class="fa fa-comment-o"></i></a></li> ({{ $post->comments->count() }})
        @endif
        @if($sessionUser->id != $post->user_id)
          @if(!$post->sharees->contains($sessionUser->id))
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
      @if(!$post->users_liked->contains($sessionUser->id))
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
      @if ( $post->isCommentSectionShown($sessionUser) )
        <li>
          <a href="#" class="show-comments"><i class="fa fa-comment-o"></i></a> ({{ $post->comments->count() }})
        </li>
      @endif
      @if($sessionUser->id != $post->user_id)
        @if(!$post->sharees->contains($sessionUser->id))
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
