 <div class="panel panel-default panel-post animated" id="post{{ $post->id }}">
  <div class="panel-heading no-bg">
    <div class="post-author" style="display: flex; align-items: center;">
      <div class="user-avatar">
        <a href="{{ url($post->user->username) }}"><img src="{{ $post->user->avatar }}" alt="{{ $post->user->name }}" title="{{ $post->user->name }}"></a>
      </div>
        <div class="user-post-details">
            <ul class="list-unstyled no-margin no-padding">
                <li>
                    <a href="{{ url($post->user->username) }}" title="{{ '@'.$post->user->username }}" data-toggle="tooltip" data-placement="top" class="user-name user">
                        {{ $post->user->name }}
                    </a>
                </li>
                <li></li>
            </ul>
        </div>
      </div>
    </div>
    <div class="panel-body">
      <div class="text-wrapper">

            <div class="post-image-holder post-locked  single-image">
                <a><img src="https://fansplatform.mjmdesign.co/user/gallery/locked.png"  title="{{ $post->user->name }}" alt="{{ $post->user->name }}"></a>

                <!-- Modal -->
                <div class="modal fade" id="myModal" role="dialog">
                    <div class="modal-dialog">

                        <!-- Modal content-->
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

    </div>

  </div>
 </div>
