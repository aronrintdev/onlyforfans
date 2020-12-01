<section class="row mb-3 ml-0 mr-0">
  @foreach($stories as $s) 
    @php
      $bgColor = array_key_exists('background-color', $s->cattrs) ? $s->cattrs['background-color'] : 'yellow';
    @endphp
    {{-- {{ $s->content}} --}}
    <article class="box-story col-sm-1" style="background-color: {{ $bgColor }};">
    </article>
  @endforeach
</section>

