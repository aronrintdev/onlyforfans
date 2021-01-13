<!-- %VIEW %PARTIAL(MODAL): views/common/partials/_purchase_post_confirm -->
<div class="modal-dialog modal-dialog-centered modal-sm tag-purchase_post_confirm">
  <section class="modal-content" role="document">

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h3 class="modal-title">Purchase Post</h3>
    </div>

    {{ Form::open([ 'route'=>['posts.purchase',$post->id],'method'=>'POST','class'=>'' ]) }}

    <div class="modal-body">

      <input type="hidden" name="post_id" value="{{ $post->id }}">

      <div class="post-author">

        <article class="user-avatar">
          <a href="{{ url($post->user->username) }}"><img class="img-responsive" src="{{ $post->user->avatar->filepath }}" alt="{{ $post->user->name }}" title="{{ $post->user->name }}"></a>
        </article>

        <article class="user-post-details">
          <ul class="list-unstyled no-margin">
            <li>
              <a href="{{ url($post->user->username) }}" title="{{ '@'.$post->user->username }}" data-toggle="tooltip" data-placement="top" class="user-name user">{{ $post->user->name }}</a>
              @if($post->user->verified)
                <span class="verified-badge bg-success"><i class="fa fa-check"></i></span>
              @endif
            </li>
            <li>
              {{ '@'.$post->user->username }}
            </li>
          </ul>
        </article>

      </div>

      <p>Amount: ${{ $post->price }}</p>

    </div>

    <div class="modal-footer">
      <button type="button" class="clickme_to-purchase_post btn btn-primary w-100" data-post_id="{{ $post->id }}">Purchase Post</button>
    </div>
    {{ Form::close() }}

  </section>
</div>

