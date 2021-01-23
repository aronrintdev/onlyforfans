<!-- %VIEW: themes/default/partials/post/_copylinkModal -->
<section id="copyLinkModal{{ $post->id }}" class="modal fade" role="dialog" tabindex='-1'>
  <div class="modal-dialog">

    <div class="modal-content">

      <article class="modal-body">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h3 class="modal-title">{{ trans('common.copy_link_post') }}</h3>
        </div>
        {{--          <div id="copy-link-content">{{ url('/share-post/'.$post->id) }}</div>--}}
        <a href="{{ url('/post/'.$post->id) }}">{{ url('/post/'.$post->id) }}</a>
      </article>

      <article class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('common.close') }}</button>
      </article>

    </div>

  </div>
</section>
