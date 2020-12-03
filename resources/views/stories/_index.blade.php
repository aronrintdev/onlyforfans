<section class="row mb-3 ml-0 mr-0">
  <article class="box-story tag-ctrl col-sm-1">
    <div class="v-wrap">
    <div class="v-box p-3"> 
      {{--
      <i class="fa fa-plus clickme_to-create_story"></i>
      --}}
      <a href="{{ route('stories.create', $sessionUser->username) }}" class="clickme_to-create_story"><i class="fa fa-plus OFF-clickme_to-create_story"></i></a>
    </div>
    </div>
  </article>
  @foreach($stories as $s) 
    @php
      $bgColor = array_key_exists('background-color', $s->cattrs) ? $s->cattrs['background-color'] : 'yellow';
    @endphp
    {{-- {{ $s->content}} --}}
    <article class="box-story col-sm-1" style="background-color: {{ $bgColor }};">
    </article>
  @endforeach
</section>


