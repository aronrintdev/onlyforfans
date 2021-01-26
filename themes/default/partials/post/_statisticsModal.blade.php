<!-- %VIEW: themes/default/partials/post/_statisticsModal -->
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
