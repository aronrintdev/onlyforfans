@extends('layouts.app2')

@section('content')
  <div class="container" id="view-home_timeline">

    <section class="row">
      <article class="col-sm-12">
        <story-bar></story-bar>
      </article>
    </section>

    <section class="row">

      <main class="col-md-7 col-lg-8">
        <create-post></create-post>
        <post-feed></post-feed>
      </main>

      <aside class="col-md-5 col-lg-4">
        <session-widget></session-widget>
        <suggested-feed></suggested-feed>
      </aside>

    </section>

  </div>
@endsection
