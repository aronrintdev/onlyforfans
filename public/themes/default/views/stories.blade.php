<!-- %VIEW: public/themes/views/stories -->
<div id="example-vue" class="container">
  <div class="row">

    <aside class="col-md-4 sidebar-wrapper">
        <h1>Sidebar</h1>
      {{--
      {!! Theme::partial('home-rightbar',compact('suggested_users', 'suggested_groups', 'suggested_pages', 'totalTip', 'subscriptionAmount')) !!}
      --}}
    </aside>

    <main class="col-sm-8 content-wrapper">
      @if (Session::has('message'))
        <div class="alert alert-{{ Session::get('status') }}" role="alert">
          {!! Session::get('message') !!}
        </div>
      @endif
      @if(isset($create))
        <h1>Create</h1>
      @endif
      <section>
        <div>EX Begin</div>
        <example></example>
        <div>EX End</div>
      </section>
    </main>

  </div>
</div>

{{--
<script src="{{ asset('js/app.js') }}"></script>
--}}
<script type="text/javascript">
</script>
