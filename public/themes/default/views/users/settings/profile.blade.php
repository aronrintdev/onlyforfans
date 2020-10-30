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
							Edit {{ trans('common.personal') }}
						</h3>
					</div>
				<div class="panel-body nopadding">
                        <div class="fans-form">
							<form method="POST" action="{{ url('/'.$username.'/settings/profile/') }}">
								{{ csrf_field() }}
    								<fieldset class="form-group">
    									{{ Form::label('about', trans('common.about')) }}
    									{{ Form::textarea('about', Auth::user()->timeline->about, ['class' => 'form-control', 'placeholder' => trans('messages.about_user_placeholder')]) }}
    								</fieldset>
    								<div class="row">
    									<div class="col-md-6">
    										<fieldset class="form-group">
    											{{ Form::label('country', trans('common.from')) }}
    											{{ Form::text('country', Auth::user()->country, array('class' => 'form-control', 'placeholder' => trans('common.country'))) }}
    										</fieldset>
    									</div>
    									<div class="col-md-6">
    										<fieldset class="form-group">
    											{{ Form::label('city', trans('common.current_city')) }}
    											{{ Form::text('city', Auth::user()->city, ['class' => 'form-control', 'placeholder' => trans('common.current_city')]) }}
    										</fieldset>
    									</div>
    								</div>
									<div class="row">
                                        <div class="col-md-6">
                                            <fieldset class="form-group required">
                                                {{ Form::label('gender', trans('common.gender')) }}
                                                {{ Form::select('gender', array('male' => trans('common.male'), 'female' => trans('common.female'), 'other' => trans('common.none')), Auth::user()->gender, array('class' => 'form-control')) }}
                                            </fieldset>
                                        </div>
										<div class="col-md-6">
											<fieldset class="form-group">
												{{ Form::label('birthday', trans('common.birthday')) }}
												<div class="input-group date datepicker">
													<span class="input-group-addon addon-left calendar-addon">
														<span class="fa fa-calendar"></span>
													</span>
													{{ Form::text('birthday', Auth::user()->birthday, ['class' => 'form-control', 'id' => 'datepicker1']) }}
													<span class="input-group-addon addon-right angle-addon">
														<span class="fa fa-angle-down"></span>
													</span>
												</div>
											</fieldset>
										</div>
										<div class="col-md-6">
											<fieldset class="form-group">
												{{ Form::label('wishlist', trans('common.designation')) }}
												{{ Form::url('wishlist', Auth::user()->wishlist, ['class' => 'form-control', 'placeholder' => trans('common.your_qualification')]) }}
											</fieldset>
										</div>
										<div class="col-md-6">
											<fieldset class="form-group">
												{{ Form::label('website', trans('common.hobbies')) }}
												{{ Form::url('website', Auth::user()->website, ['class' => 'form-control', 'placeholder' => trans('common.mention_your_hobbies')]) }}
											</fieldset>
										</div>
										<div class="col-md-6">
											<fieldset class="form-group">
												{{ Form::label('instagram', trans('common.interests')) }}
												{{ Form::url('instagram', Auth::user()->instagram, ['class' => 'form-control', 'placeholder' => trans('common.add_your_interests')]) }}
											</fieldset>
										</div>
									</div>
									@if(Setting::get('custom_option1') != NULL || Setting::get('custom_option2') != NULL)
										<div class="row">
											@if(Setting::get('custom_option1') != NULL)
											<div class="col-md-6">
												<fieldset class="form-group">
													{{ Form::label('custom_option1', Setting::get('custom_option1')) }}
													{{ Form::text('custom_option1', Auth::user()->custom_option1, ['class' => 'form-control']) }}
												</fieldset>
											</div>
											@endif
											@if(Setting::get('custom_option2') != NULL)
											<div class="col-md-6">
												<fieldset class="form-group">
													{{ Form::label('custom_option2', Setting::get('custom_option2')) }}
													{{ Form::text('custom_option2', Auth::user()->custom_option2, ['class' => 'form-control']) }}
												</fieldset>
											</div>
											@endif
										</div>
									@endif
									@if(Setting::get('custom_option3') != NULL || Setting::get('custom_option4') != NULL)
										<div class="row">
											@if(Setting::get('custom_option3') != NULL)
											<div class="col-md-6">
												<fieldset class="form-group">
													{{ Form::label('custom_option3', Setting::get('custom_option3')) }}
													{{ Form::text('custom_option3', Auth::user()->custom_option3, ['class' => 'form-control']) }}
												</fieldset>
											</div>
											@endif
											@if(Setting::get('custom_option4') != NULL)
											<div class="col-md-6">
												<fieldset class="form-group">
													{{ Form::label('custom_option4', Setting::get('custom_option4')) }}
													{{ Form::text('custom_option4', Auth::user()->custom_option4, ['class' => 'form-control']) }}
												</fieldset>
											</div>
											@endif
										</div>
									@endif
									<div class="pull-right">
										{{ Form::submit(trans('common.save_changes'), ['class' => 'btn btn-success']) }}
									</div>
									<div class="clearfix"></div>
								</form>
							</div>
						</div>
					</div>
{{--					<div class="panel panel-default">--}}
{{--						<div class="panel-heading no-bg panel-settings">--}}
{{--							<h3 class="panel-title">--}}
{{--								{{ trans('common.update_password') }}--}}
{{--							</h3>--}}
{{--						</div>--}}
{{--						<div class="panel-body nopadding">--}}
{{--							<div class="fans-form">								--}}
{{--								<form method="POST" action="{{ url('/'.Auth::user()->username.'/settings/password/') }}">--}}
{{--									{{ csrf_field() }}--}}
{{--									<div class="row">--}}
{{--										<div class="col-md-6">--}}
{{--											<fieldset class="form-group {{ $errors->has('current_password') ? ' has-error' : '' }}">--}}
{{--												{{ Form::label('current_password', trans('common.current_password')) }}--}}
{{--												<input type="password" class="form-control" id="current_password" name="current_password" value="{{ old('current_password') }}" placeholder= "{{ trans('messages.enter_old_password') }}">--}}
{{--												@if ($errors->has('current_password'))--}}
{{--												<span class="help-block">--}}
{{--													{{ $errors->first('current_password') }}--}}
{{--												</span>--}}
{{--												@endif--}}
{{--											</fieldset>--}}
{{--										</div>--}}
{{--										<div class="col-md-6">--}}
{{--											<fieldset class="form-group {{ $errors->has('new_password') ? ' has-error' : '' }}">--}}
{{--												{{ Form::label('new_password', trans('common.new_password')) }}--}}
{{--												<input type="password" class="form-control" id="new_password" name="new_password" value="{{ old('new_password') }}" placeholder="{{ trans('messages.enter_new_password') }}">--}}
{{--												@if($errors->has('new_password'))--}}
{{--												<span class="help-block">--}}
{{--													{{ $errors->first('new_password') }}--}}
{{--												</span>--}}
{{--												@endif--}}
{{--											</fieldset>--}}
{{--										</div>--}}
{{--									</div>--}}
{{--									<div class="pull-right">--}}
{{--										{{ Form::submit(trans('common.save_password'), ['class' => 'btn btn-success']) }}--}}
{{--									</div>--}}
{{--									<div class="clearfix"></div>--}}
{{--								</form>--}}
{{--							</div>--}}
{{--						</div>--}}
{{--					</div>--}}
				</div>
			</div>
		</div>