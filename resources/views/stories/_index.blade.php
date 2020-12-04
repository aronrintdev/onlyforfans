<section class="row mb-3 ml-0 mr-0">
  <article class="OFF-box-story OFF-tag-ctrl col-sm-1">
    <div class="tag-ctrl box-story">
      <div class="v-wrap">
        <div class="v-box p-3"> 
          <a href="{{ route('stories.create', $sessionUser->username) }}" class="clickme_to-create_story"><i class="fa fa-plus OFF-clickme_to-create_story"></i></a>
        </div>
      </div>
    </div>
  </article>
  @foreach($stories as $s) 
    @php
      $bgColor = array_key_exists('background-color', $s->cattrs) ? $s->cattrs['background-color'] : 'yellow';
    @endphp
    @if ($s->stype==='text')
      <article class="col-sm-1">
        <a class="box-story" href="{{ route('stories.player', $sessionUser->username) }}" style="background-color: {{ $bgColor }};">&nbsp;</a>
      </article>
    @elseif ($s->stype==='image')
      <article class="col-sm-1">
        <a class="box-story" style="background-color: {{ $bgColor }};" href="{{ route('stories.player', $sessionUser->username) }}">
          <img src="{{ Storage::disk('s3')->url($s->mediafiles->first()->filename) }}" alt="" />
        </a>
      </article>
    @endif
  @endforeach
</section>
