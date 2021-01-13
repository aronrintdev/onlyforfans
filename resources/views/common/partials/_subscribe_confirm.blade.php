<!-- %VIEW %PARTIAL(MODAL): views/common/partials/_subscribe_confirm -->
<div class="modal-dialog modal-dialog-centered modal-sm">
  <section class="modal-content" role="document">

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    @if ($is_cancel)
      <h3 class="modal-title">Unsubcribe</h3>
    @else
      <h3 class="modal-title">Subcribe</h3>
    @endif
    </div>

    {{ Form::open([ 'route'=>['timelines.follow',$timeline->id],'method'=>'POST','class'=>'' ]) }}

    {{ Form::hidden('is_subscribe', 1) }}

    <div class="modal-body no-padding">

      <div class="timeline-cover-section">

        <div class="timeline-cover">
          <img src=" @if($timeline->cover_id) {{ $timeline->cover->filepath }} @else {{ url('user/cover/default-cover-user.png') }} @endif" alt="{{ $timeline->name }}" title="{{ $timeline->name }}">
        </div>
        <div class="timeline-list box-avatar">
          <img class="user-avatar" src="{{ $timeline->user->avatar->filepath }}" alt="" title="{{ $timeline->name }}">
        </div>

        <div class="timeline-list box-userinfo">
          <div class="user-name">
            <h3 class="my-0"><strong>{{ $timeline->name }}</strong></h3>
            <p>{{ $timeline->user->renderLocation() }}</p>
          </div>
          <ul class="list-unstyled mt-3">
            <li><i class="fa fa-check"></i> Full access to this user's content</li>
            <li><i class="fa fa-check"></i> Direct message with this user</li>
            <li><i class="fa fa-check"></i> Cancel your subscription at any time</li>
          </ul>
        </div>

        <div class="timeline-list box-popinfo">
          <div class="list-wrap">
            <ul class="list-unstyled list-inline text-center tag-fit_content OFF-mt-3">
              <li>
                <h3 class="my-0"><strong>{{ $timeline->followers->count() }}</strong></h3>
                <div>Fans</div>
              </li>
              <li>
                <h3 class="my-0"><strong>{{ $timeline->user->renderPostCount() }}</strong></h3>
                <div>Posts</div>
              </li>
              <li>
                <h3 class="my-0"><strong>{{ $timeline->user->renderLikesCount() }}</strong></h3>
                <div>Likes</div>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <h3 class="text-center">$ {{ niceCurrency($timeline->user->price) }} / mo</h3>

      <div class="px-5 pb-5">
        @if ($is_cancel)
        <button class="btn btn-submit btn-warning w-100" data-timeline_id="{{ $timeline->id }}">Unsubscribe</button>
        @else
        <button class="btn btn-submit btn-success w-100" data-timeline_id="{{ $timeline->id }}"><i class="fa fa-heart"></i> Subscribe</button>
        @endif
      </div>

    </div>

  </section>
</div>
