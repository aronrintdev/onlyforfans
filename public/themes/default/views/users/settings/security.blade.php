<div class="container">
    <div class="row">
        <div class="col-md-4">
            <div class="post-filters">
                {!! Theme::partial('usermenu-settings') !!}
            </div>
        </div>
        <div class="col-md-8">

            <div class="panel panel-default">
                <div class="panel-heading no-bg panel-settings">
                    @include('flash::message')
                    <h3 class="panel-title">
                        {{ trans('common.update_password') }}
                    </h3>
                </div>
                <div class="panel-body nopadding">
                    <div class="fans-form">
                        <form method="POST" action="{{ url('/'.Auth::user()->username.'/settings/password/') }}">
                            {{ csrf_field() }}

                            <div class="row">
                                <div class="col-md-6">
                                    <fieldset class="form-group {{ $errors->has('current_password') ? ' has-error' : '' }}">
                                        {{ Form::label('current_password', trans('common.current_password')) }}
                                        <input type="password" class="form-control" id="current_password" name="current_password" value="{{ old('current_password') }}" placeholder= "{{ trans('messages.enter_old_password') }}">

                                        @if ($errors->has('current_password'))
                                            <span class="help-block">
													{{ $errors->first('current_password') }}
												</span>
                                        @endif
                                    </fieldset>
                                </div>
                                <div class="col-md-6">
                                    <fieldset class="form-group {{ $errors->has('new_password') ? ' has-error' : '' }}">
                                        {{ Form::label('new_password', trans('common.new_password')) }}
                                        <input type="password" class="form-control" id="new_password" name="new_password" value="{{ old('new_password') }}" placeholder="{{ trans('messages.enter_new_password') }}">

                                        @if($errors->has('new_password'))
                                            <span class="help-block">
													{{ $errors->first('new_password') }}
												</span>
                                        @endif
                                    </fieldset>
                                </div>
                            </div>

                            <div class="pull-right">
                                {{ Form::submit(trans('common.save_password'), ['class' => 'btn btn-success']) }}
                            </div>
                            <div class="clearfix"></div>
                        </form>
                    </div><!-- /fans-form -->
                </div>
            </div>
            <!-- End of second panel -->

        </div>
    </div><!-- /row -->
</div>
<!-- </div> --><!-- /main-content -->
