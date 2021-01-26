<!-- %VIEW: themes/default/partials/post/_shareModal -->
<div id="shareModal{{ $post->id }}" class="modal fade" role="dialog" tabindex='-1'>
  <div class="modal-dialog">

    <div class="modal-content">
      <div class="modal-body">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h3 class="modal-title">{{ trans('common.copy_embed_post') }}</h3>
        </div>
        <textarea class="form-control" rows="3">
          <iframe src="{{ url('/share-post/'.$post->id) }}" width="600px" height="420px" frameborder="0"></iframe>
        </textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('common.close') }}</button>
      </div>
    </div>
  </div>
</div>
