<style>
    input[type=checkbox]{
        height: 0;
        width: 0;
        visibility: hidden;
    }
    .toggle > label {
        cursor: pointer;
        text-indent: -9999px;
        width: 50px !important;
        height: 25px !important;;
        background: grey;
        display: block;
        border-radius: 100px;
        position: relative;
    }
    .toggle > label:after {
        content: '';
        position: absolute;
        top: 5px;
        left: 5px;
        width: 15px !important;
        height: 15px !important;
        background: #fff;
        border-radius: 90px;
        transition: 0.3s;
    }
    input:checked +  label {
        background: #38a169;
    }
    input:checked +  label:after {
        left: calc(100% - 5px);
        transform: translateX(-100%);
    }
     label:active:after {
        width: 130px;
    }
</style>
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
							{{ trans('common.general_settings') }}
						</h3>
					</div>
					<div class="panel-body nopadding">
						<div class="fans-form">
							<form method="POST" action="{{ url('/'.$username.'/settings/general/') }}">
								{{ csrf_field() }}
								
								<div class="row">
									<div class="col-md-6">
										<fieldset class="form-group required {{ $errors->has('username') ? ' has-error' : '' }}">
											{{ Form::label('username', trans('common.username')) }}
											{{ Form::text('new_username', Auth::user()->username, ['class' => 'form-control', 'placeholder' => trans('common.username')]) }}
											@if ($errors->has('username'))
											<span class="help-block">
												{{ $errors->first('username') }}
											</span>
											@endif
											<small class="text-muted"><a href="{{ url($username) }}">{{ url('/') }}/{{$username}}</a></small>
										</fieldset>
									</div>
									<div class="col-md-6">
										<fieldset class="form-group required {{ $errors->has('name') ? ' has-error' : '' }}">
											{{ Form::label('name', trans('common.fullname')) }}
											{{ Form::text('name', Auth::user()->name, ['class' => 'form-control', 'placeholder' => trans('common.fullname')]) }}
											@if ($errors->has('name'))
												<span class="help-block">
												{{ $errors->first('name') }}
											</span>
											@endif
										</fieldset>
									</div>
								</div>
                                
								<div class="row">
									<div class="col-md-6">
										<fieldset class="form-group required {{ $errors->has('email') ? ' has-error' : '' }}">
											{{ Form::label('email', trans('auth.email_address')) }}
											{{ Form::email('email', Auth::user()->email, ['class' => 'form-control', 'placeholder' => trans('auth.email_address')]) }}
											@if ($errors->has('email'))
											<span class="help-block">
												{{ $errors->first('email') }}
											</span>
											@endif
										</fieldset>
									</div>
{{--									<div class="col-md-6">--}}
{{--										<fieldset class="form-group required {{ $errors->has('password') ? ' has-error' : '' }}">--}}
{{--											{{ Form::label('password', trans('auth.password')) }}--}}
{{--											{{ Form::password('password', ['class' => 'form-control', 'id' => 'password', 'placeholder'=> trans('auth.password')]) }}--}}
{{--										</fieldset>--}}
{{--									</div>--}}
								</div>
								{{--End of Subscribe price--}}

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

					<div class="panel panel-default">
						<div class="panel-heading no-bg panel-settings">
							<h3 class="panel-title">
								{{ trans('common.subscriptions') }}
							</h3>
						</div>
						<div class="panel-body nopadding">
							<div class="fans-form">
								<form method="POST" action="{{ url('/'.Auth::user()->username.'/settings/subscription/') }}">
									{{ csrf_field() }}

                                    <div class="row">
                                        <div class="col-md-6">
                                            <fieldset class="form-group {{ $errors->has('subscribe_price') ? ' has-error' : '' }}">
                                                {{ Form::label('subscribe_price', trans('auth.subscribe_price')) }}
                                                {{ Form::text('subscribe_price', Auth::user()->price, ['class' => 'form-control', 'placeholder' => '$0.00']) }}
                                                @if ($errors->has('subscribe_price'))
                                                    <span class="help-block">
												{{ $errors->first('subscribe_price') }}
											</span>
                                                @endif
                                            </fieldset>
                                        </div>
                                        <div class="col-md-6">
                                            <fieldset class="form-group">
                                                {{ Form::label('follow_for_free', trans('auth.follow_for_free')) }}
                                                <div class="toggle">
                                                    <input type="checkbox" {{ \Illuminate\Support\Facades\Auth::user()->is_follow_for_free ? 'checked' : ''  }} id="followForFree" name="is_follow_for_free" class="form-control"/><label for="followForFree">follow for free</label>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <fieldset class="form-group">
                                                <label>Subscription Bundles</label>
                                                <p><input style="text-transform: unset !important;" type="button" class="btn btn-success" value="Add a Bundle"></p>
                                            </fieldset>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <fieldset class="form-group">
                                                <label for="referral-rewards">Referral Rewards</label>
                                                <select class="form-control" name="referral-rewards" id="referral-rewards">
                                                  <option value="disabled" selected>Disabled</option>
                                                  <option value="1-free-month">1 Free Month for a Referrer</option>
                                                </select>
                                            </fieldset>
                                        </div>
                                    </div>
									<div class="pull-right">
										{{ Form::submit(trans('common.save_changes'), ['class' => 'btn btn-success']) }}
									</div>
									<div class="clearfix"></div>
								</form>
							</div>
						</div>
					</div>

					<div class="panel panel-default">
						<div class="panel-heading no-bg panel-settings">
							<h3 class="panel-title">
								{{ trans('common.localization') }}
							</h3>
						</div>
						<div class="panel-body nopadding">
							<div class="fans-form">
								<form method="POST" action="{{ url('/'.Auth::user()->username.'/settings/localization/') }}">
									{{ csrf_field() }}

                                    <div class="row">
                                        <div class="col-md-6">
                                            <p>{{ Form::label('language', trans('common.language')) }}</p>

                                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
										<span class="language">
											@if(Auth::user()->language != null)
                                                <?php $key = Auth::user()->language; ?>
                                            @else
                                                <?php $key = App\Setting::get('language'); ?>
                                            @endif
                                            @if($key == 'gr')
                                                <span class="flag-icon flag-icon-gr"></span>
                                            @elseif($key == 'en')
                                                <span class="flag-icon flag-icon-us"></span>
                                            @elseif($key == 'zh')
                                                <span class="flag-icon flag-icon-cn"></span>
                                            @else
                                                <span class="flag-icon flag-icon-{{ $key }}"></span>
                                            @endif
                                        </span> <i class="fa fa-angle-down" aria-hidden="true"></i></a>
                                            <ul class="dropdown-menu">
                                                @foreach( Config::get('app.locales') as $key => $value)
                                                    <li class=""><a href="#" class="switch-language" data-language="{{ $key }}">
                                                            @if($key == 'gr')
                                                                <span class="flag-icon flag-icon-gr"></span>
                                                            @elseif($key == 'en')
                                                                <span class="flag-icon flag-icon-us"></span>
                                                            @elseif($key == 'zh')
                                                                <span class="flag-icon flag-icon-cn"></span>
                                                            @else
                                                                <span class="flag-icon flag-icon-{{ $key }}"></span>
                                                            @endif

                                                            {{ $value }}</a></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <fieldset class="form-group {{ $errors->has('country') ? ' has-error' : '' }}">
                                                {{ Form::label('country', trans('common.country')) }}
                                                {{ Form::text('country', Auth::user()->country, ['class' => 'form-control', 'placeholder' => trans('common.country')]) }}
                                                @if ($errors->has('country'))
                                                    <span class="help-block">
												{{ $errors->first('country') }}
											</span>
                                                @endif
                                            </fieldset>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <fieldset class="form-group">
                                                <label for="timezone">Timezone</label>
                                                <select class="form-control" name="timezone" id="timezone">
                                                	<option timeZoneId="1" gmtAdjustment="GMT-12:00" useDaylightTime="0" value="-12">(GMT-12:00) International Date Line West</option>
                                                	<option timeZoneId="2" gmtAdjustment="GMT-11:00" useDaylightTime="0" value="-11">(GMT-11:00) Midway Island, Samoa</option>
                                                	<option timeZoneId="3" gmtAdjustment="GMT-10:00" useDaylightTime="0" value="-10">(GMT-10:00) Hawaii</option>
                                                	<option timeZoneId="4" gmtAdjustment="GMT-09:00" useDaylightTime="1" value="-9">(GMT-09:00) Alaska</option>
                                                	<option timeZoneId="5" gmtAdjustment="GMT-08:00" useDaylightTime="1" value="-8" selected>(GMT-08:00) Pacific Time (US & Canada)</option>
                                                	<option timeZoneId="6" gmtAdjustment="GMT-08:00" useDaylightTime="1" value="-8">(GMT-08:00) Tijuana, Baja California</option>
                                                	<option timeZoneId="7" gmtAdjustment="GMT-07:00" useDaylightTime="0" value="-7">(GMT-07:00) Arizona</option>
                                                	<option timeZoneId="8" gmtAdjustment="GMT-07:00" useDaylightTime="1" value="-7">(GMT-07:00) Chihuahua, La Paz, Mazatlan</option>
                                                	<option timeZoneId="9" gmtAdjustment="GMT-07:00" useDaylightTime="1" value="-7">(GMT-07:00) Mountain Time (US & Canada)</option>
                                                	<option timeZoneId="10" gmtAdjustment="GMT-06:00" useDaylightTime="0" value="-6">(GMT-06:00) Central America</option>
                                                	<option timeZoneId="11" gmtAdjustment="GMT-06:00" useDaylightTime="1" value="-6">(GMT-06:00) Central Time (US & Canada)</option>
                                                	<option timeZoneId="12" gmtAdjustment="GMT-06:00" useDaylightTime="1" value="-6">(GMT-06:00) Guadalajara, Mexico City, Monterrey</option>
                                                	<option timeZoneId="13" gmtAdjustment="GMT-06:00" useDaylightTime="0" value="-6">(GMT-06:00) Saskatchewan</option>
                                                	<option timeZoneId="14" gmtAdjustment="GMT-05:00" useDaylightTime="0" value="-5">(GMT-05:00) Bogota, Lima, Quito, Rio Branco</option>
                                                	<option timeZoneId="15" gmtAdjustment="GMT-05:00" useDaylightTime="1" value="-5">(GMT-05:00) Eastern Time (US & Canada)</option>
                                                	<option timeZoneId="16" gmtAdjustment="GMT-05:00" useDaylightTime="1" value="-5">(GMT-05:00) Indiana (East)</option>
                                                	<option timeZoneId="17" gmtAdjustment="GMT-04:00" useDaylightTime="1" value="-4">(GMT-04:00) Atlantic Time (Canada)</option>
                                                	<option timeZoneId="18" gmtAdjustment="GMT-04:00" useDaylightTime="0" value="-4">(GMT-04:00) Caracas, La Paz</option>
                                                	<option timeZoneId="19" gmtAdjustment="GMT-04:00" useDaylightTime="0" value="-4">(GMT-04:00) Manaus</option>
                                                	<option timeZoneId="20" gmtAdjustment="GMT-04:00" useDaylightTime="1" value="-4">(GMT-04:00) Santiago</option>
                                                	<option timeZoneId="21" gmtAdjustment="GMT-03:30" useDaylightTime="1" value="-3.5">(GMT-03:30) Newfoundland</option>
                                                	<option timeZoneId="22" gmtAdjustment="GMT-03:00" useDaylightTime="1" value="-3">(GMT-03:00) Brasilia</option>
                                                	<option timeZoneId="23" gmtAdjustment="GMT-03:00" useDaylightTime="0" value="-3">(GMT-03:00) Buenos Aires, Georgetown</option>
                                                	<option timeZoneId="24" gmtAdjustment="GMT-03:00" useDaylightTime="1" value="-3">(GMT-03:00) Greenland</option>
                                                	<option timeZoneId="25" gmtAdjustment="GMT-03:00" useDaylightTime="1" value="-3">(GMT-03:00) Montevideo</option>
                                                	<option timeZoneId="26" gmtAdjustment="GMT-02:00" useDaylightTime="1" value="-2">(GMT-02:00) Mid-Atlantic</option>
                                                	<option timeZoneId="27" gmtAdjustment="GMT-01:00" useDaylightTime="0" value="-1">(GMT-01:00) Cape Verde Is.</option>
                                                	<option timeZoneId="28" gmtAdjustment="GMT-01:00" useDaylightTime="1" value="-1">(GMT-01:00) Azores</option>
                                                	<option timeZoneId="29" gmtAdjustment="GMT+00:00" useDaylightTime="0" value="0">(GMT+00:00) Casablanca, Monrovia, Reykjavik</option>
                                                	<option timeZoneId="30" gmtAdjustment="GMT+00:00" useDaylightTime="1" value="0">(GMT+00:00) Greenwich Mean Time : Dublin, Edinburgh, Lisbon, London</option>
                                                	<option timeZoneId="31" gmtAdjustment="GMT+01:00" useDaylightTime="1" value="1">(GMT+01:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna</option>
                                                	<option timeZoneId="32" gmtAdjustment="GMT+01:00" useDaylightTime="1" value="1">(GMT+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague</option>
                                                	<option timeZoneId="33" gmtAdjustment="GMT+01:00" useDaylightTime="1" value="1">(GMT+01:00) Brussels, Copenhagen, Madrid, Paris</option>
                                                	<option timeZoneId="34" gmtAdjustment="GMT+01:00" useDaylightTime="1" value="1">(GMT+01:00) Sarajevo, Skopje, Warsaw, Zagreb</option>
                                                	<option timeZoneId="35" gmtAdjustment="GMT+01:00" useDaylightTime="1" value="1">(GMT+01:00) West Central Africa</option>
                                                	<option timeZoneId="36" gmtAdjustment="GMT+02:00" useDaylightTime="1" value="2">(GMT+02:00) Amman</option>
                                                	<option timeZoneId="37" gmtAdjustment="GMT+02:00" useDaylightTime="1" value="2">(GMT+02:00) Athens, Bucharest, Istanbul</option>
                                                	<option timeZoneId="38" gmtAdjustment="GMT+02:00" useDaylightTime="1" value="2">(GMT+02:00) Beirut</option>
                                                	<option timeZoneId="39" gmtAdjustment="GMT+02:00" useDaylightTime="1" value="2">(GMT+02:00) Cairo</option>
                                                	<option timeZoneId="40" gmtAdjustment="GMT+02:00" useDaylightTime="0" value="2">(GMT+02:00) Harare, Pretoria</option>
                                                	<option timeZoneId="41" gmtAdjustment="GMT+02:00" useDaylightTime="1" value="2">(GMT+02:00) Helsinki, Kyiv, Riga, Sofia, Tallinn, Vilnius</option>
                                                	<option timeZoneId="42" gmtAdjustment="GMT+02:00" useDaylightTime="1" value="2">(GMT+02:00) Jerusalem</option>
                                                	<option timeZoneId="43" gmtAdjustment="GMT+02:00" useDaylightTime="1" value="2">(GMT+02:00) Minsk</option>
                                                	<option timeZoneId="44" gmtAdjustment="GMT+02:00" useDaylightTime="1" value="2">(GMT+02:00) Windhoek</option>
                                                	<option timeZoneId="45" gmtAdjustment="GMT+03:00" useDaylightTime="0" value="3">(GMT+03:00) Kuwait, Riyadh, Baghdad</option>
                                                	<option timeZoneId="46" gmtAdjustment="GMT+03:00" useDaylightTime="1" value="3">(GMT+03:00) Moscow, St. Petersburg, Volgograd</option>
                                                	<option timeZoneId="47" gmtAdjustment="GMT+03:00" useDaylightTime="0" value="3">(GMT+03:00) Nairobi</option>
                                                	<option timeZoneId="48" gmtAdjustment="GMT+03:00" useDaylightTime="0" value="3">(GMT+03:00) Tbilisi</option>
                                                	<option timeZoneId="49" gmtAdjustment="GMT+03:30" useDaylightTime="1" value="3.5">(GMT+03:30) Tehran</option>
                                                	<option timeZoneId="50" gmtAdjustment="GMT+04:00" useDaylightTime="0" value="4">(GMT+04:00) Abu Dhabi, Muscat</option>
                                                	<option timeZoneId="51" gmtAdjustment="GMT+04:00" useDaylightTime="1" value="4">(GMT+04:00) Baku</option>
                                                	<option timeZoneId="52" gmtAdjustment="GMT+04:00" useDaylightTime="1" value="4">(GMT+04:00) Yerevan</option>
                                                	<option timeZoneId="53" gmtAdjustment="GMT+04:30" useDaylightTime="0" value="4.5">(GMT+04:30) Kabul</option>
                                                	<option timeZoneId="54" gmtAdjustment="GMT+05:00" useDaylightTime="1" value="5">(GMT+05:00) Yekaterinburg</option>
                                                	<option timeZoneId="55" gmtAdjustment="GMT+05:00" useDaylightTime="0" value="5">(GMT+05:00) Islamabad, Karachi, Tashkent</option>
                                                	<option timeZoneId="56" gmtAdjustment="GMT+05:30" useDaylightTime="0" value="5.5">(GMT+05:30) Sri Jayawardenapura</option>
                                                	<option timeZoneId="57" gmtAdjustment="GMT+05:30" useDaylightTime="0" value="5.5">(GMT+05:30) Chennai, Kolkata, Mumbai, New Delhi</option>
                                                	<option timeZoneId="58" gmtAdjustment="GMT+05:45" useDaylightTime="0" value="5.75">(GMT+05:45) Kathmandu</option>
                                                	<option timeZoneId="59" gmtAdjustment="GMT+06:00" useDaylightTime="1" value="6">(GMT+06:00) Almaty, Novosibirsk</option>
                                                	<option timeZoneId="60" gmtAdjustment="GMT+06:00" useDaylightTime="0" value="6">(GMT+06:00) Astana, Dhaka</option>
                                                	<option timeZoneId="61" gmtAdjustment="GMT+06:30" useDaylightTime="0" value="6.5">(GMT+06:30) Yangon (Rangoon)</option>
                                                	<option timeZoneId="62" gmtAdjustment="GMT+07:00" useDaylightTime="0" value="7">(GMT+07:00) Bangkok, Hanoi, Jakarta</option>
                                                	<option timeZoneId="63" gmtAdjustment="GMT+07:00" useDaylightTime="1" value="7">(GMT+07:00) Krasnoyarsk</option>
                                                	<option timeZoneId="64" gmtAdjustment="GMT+08:00" useDaylightTime="0" value="8">(GMT+08:00) Beijing, Chongqing, Hong Kong, Urumqi</option>
                                                	<option timeZoneId="65" gmtAdjustment="GMT+08:00" useDaylightTime="0" value="8">(GMT+08:00) Kuala Lumpur, Singapore</option>
                                                	<option timeZoneId="66" gmtAdjustment="GMT+08:00" useDaylightTime="0" value="8">(GMT+08:00) Irkutsk, Ulaan Bataar</option>
                                                	<option timeZoneId="67" gmtAdjustment="GMT+08:00" useDaylightTime="0" value="8">(GMT+08:00) Perth</option>
                                                	<option timeZoneId="68" gmtAdjustment="GMT+08:00" useDaylightTime="0" value="8">(GMT+08:00) Taipei</option>
                                                	<option timeZoneId="69" gmtAdjustment="GMT+09:00" useDaylightTime="0" value="9">(GMT+09:00) Osaka, Sapporo, Tokyo</option>
                                                	<option timeZoneId="70" gmtAdjustment="GMT+09:00" useDaylightTime="0" value="9">(GMT+09:00) Seoul</option>
                                                	<option timeZoneId="71" gmtAdjustment="GMT+09:00" useDaylightTime="1" value="9">(GMT+09:00) Yakutsk</option>
                                                	<option timeZoneId="72" gmtAdjustment="GMT+09:30" useDaylightTime="0" value="9.5">(GMT+09:30) Adelaide</option>
                                                	<option timeZoneId="73" gmtAdjustment="GMT+09:30" useDaylightTime="0" value="9.5">(GMT+09:30) Darwin</option>
                                                	<option timeZoneId="74" gmtAdjustment="GMT+10:00" useDaylightTime="0" value="10">(GMT+10:00) Brisbane</option>
                                                	<option timeZoneId="75" gmtAdjustment="GMT+10:00" useDaylightTime="1" value="10">(GMT+10:00) Canberra, Melbourne, Sydney</option>
                                                	<option timeZoneId="76" gmtAdjustment="GMT+10:00" useDaylightTime="1" value="10">(GMT+10:00) Hobart</option>
                                                	<option timeZoneId="77" gmtAdjustment="GMT+10:00" useDaylightTime="0" value="10">(GMT+10:00) Guam, Port Moresby</option>
                                                	<option timeZoneId="78" gmtAdjustment="GMT+10:00" useDaylightTime="1" value="10">(GMT+10:00) Vladivostok</option>
                                                	<option timeZoneId="79" gmtAdjustment="GMT+11:00" useDaylightTime="1" value="11">(GMT+11:00) Magadan, Solomon Is., New Caledonia</option>
                                                	<option timeZoneId="80" gmtAdjustment="GMT+12:00" useDaylightTime="1" value="12">(GMT+12:00) Auckland, Wellington</option>
                                                	<option timeZoneId="81" gmtAdjustment="GMT+12:00" useDaylightTime="0" value="12">(GMT+12:00) Fiji, Kamchatka, Marshall Is.</option>
                                                	<option timeZoneId="82" gmtAdjustment="GMT+13:00" useDaylightTime="0" value="13">(GMT+13:00) Nuku'alofa</option>
                                                </select>
                                            </fieldset>
                                        </div>
                                        <div class="col-md-6">
                                            <fieldset class="form-group">
                                                <label for="timezone">Currency</label>
                                                <select class="form-control" name="currency" id="currency">
                                                	<option value="USD" selected="selected">United States Dollars (USD)</option>
                                                	<option value="EUR">Euro</option>
                                                	<option value="GBP">United Kingdom Pounds</option>
                                                	<option value="DZD">Algeria Dinars</option>
                                                	<option value="ARP">Argentina Pesos</option>
                                                	<option value="AUD">Australia Dollars</option>
                                                	<option value="ATS">Austria Schillings</option>
                                                	<option value="BSD">Bahamas Dollars</option>
                                                	<option value="BBD">Barbados Dollars</option>
                                                	<option value="BEF">Belgium Francs</option>
                                                	<option value="BMD">Bermuda Dollars</option>
                                                	<option value="BRR">Brazil Real</option>
                                                	<option value="BGL">Bulgaria Lev</option>
                                                	<option value="CAD">Canada Dollars</option>
                                                	<option value="CLP">Chile Pesos</option>
                                                	<option value="CNY">China Yuan Renmimbi</option>
                                                	<option value="CYP">Cyprus Pounds</option>
                                                	<option value="CSK">Czech Republic Koruna</option>
                                                	<option value="DKK">Denmark Kroner</option>
                                                	<option value="NLG">Dutch Guilders</option>
                                                	<option value="XCD">Eastern Caribbean Dollars</option>
                                                	<option value="EGP">Egypt Pounds</option>
                                                	<option value="FJD">Fiji Dollars</option>
                                                	<option value="FIM">Finland Markka</option>
                                                	<option value="FRF">France Francs</option>
                                                	<option value="DEM">Germany Deutsche Marks</option>
                                                	<option value="XAU">Gold Ounces</option>
                                                	<option value="GRD">Greece Drachmas</option>
                                                	<option value="HKD">Hong Kong Dollars</option>
                                                	<option value="HUF">Hungary Forint</option>
                                                	<option value="ISK">Iceland Krona</option>
                                                	<option value="INR">India Rupees</option>
                                                	<option value="IDR">Indonesia Rupiah</option>
                                                	<option value="IEP">Ireland Punt</option>
                                                	<option value="ILS">Israel New Shekels</option>
                                                	<option value="ITL">Italy Lira</option>
                                                	<option value="JMD">Jamaica Dollars</option>
                                                	<option value="JPY">Japan Yen</option>
                                                	<option value="JOD">Jordan Dinar</option>
                                                	<option value="KRW">Korea (South) Won</option>
                                                	<option value="LBP">Lebanon Pounds</option>
                                                	<option value="LUF">Luxembourg Francs</option>
                                                	<option value="MYR">Malaysia Ringgit</option>
                                                	<option value="MXP">Mexico Pesos</option>
                                                	<option value="NLG">Netherlands Guilders</option>
                                                	<option value="NZD">New Zealand Dollars</option>
                                                	<option value="NOK">Norway Kroner</option>
                                                	<option value="PKR">Pakistan Rupees</option>
                                                	<option value="XPD">Palladium Ounces</option>
                                                	<option value="PHP">Philippines Pesos</option>
                                                	<option value="XPT">Platinum Ounces</option>
                                                	<option value="PLZ">Poland Zloty</option>
                                                	<option value="PTE">Portugal Escudo</option>
                                                	<option value="ROL">Romania Leu</option>
                                                	<option value="RUR">Russia Rubles</option>
                                                	<option value="SAR">Saudi Arabia Riyal</option>
                                                	<option value="XAG">Silver Ounces</option>
                                                	<option value="SGD">Singapore Dollars</option>
                                                	<option value="SKK">Slovakia Koruna</option>
                                                	<option value="ZAR">South Africa Rand</option>
                                                	<option value="KRW">South Korea Won</option>
                                                	<option value="ESP">Spain Pesetas</option>
                                                	<option value="XDR">Special Drawing Right (IMF)</option>
                                                	<option value="SDD">Sudan Dinar</option>
                                                	<option value="SEK">Sweden Krona</option>
                                                	<option value="CHF">Switzerland Francs</option>
                                                	<option value="TWD">Taiwan Dollars</option>
                                                	<option value="THB">Thailand Baht</option>
                                                	<option value="TTD">Trinidad and Tobago Dollars</option>
                                                	<option value="TRL">Turkey Lira</option>
                                                	<option value="VEB">Venezuela Bolivar</option>
                                                	<option value="ZMK">Zambia Kwacha</option>
                                                	<option value="EUR">Euro</option>
                                                	<option value="XCD">Eastern Caribbean Dollars</option>
                                                	<option value="XDR">Special Drawing Right (IMF)</option>
                                                	<option value="XAG">Silver Ounces</option>
                                                	<option value="XAU">Gold Ounces</option>
                                                	<option value="XPD">Palladium Ounces</option>
                                                	<option value="XPT">Platinum Ounces</option>
                                                </select>
                                            </fieldset>
                                        </div>
                                    </div>
									<div class="pull-right">
										{{ Form::submit(trans('common.save_changes'), ['class' => 'btn btn-success']) }}
									</div>
									<div class="clearfix"></div>
								</form>
							</div>
						</div>
					</div>

            </div>
			</div>
		</div>
		
<script>
    window.checkCheckboxStatus = function () {
        if ($('#watermark').is(':checked') == true) {
            $('#watermark').val(1);
            $('.watermark_settings').css('display', '');
        } else {
            $('#watermark').val(0);
            $('.watermark_settings').css('display', 'none');
        }
    };
    $(document).ready(function () {
        checkCheckboxStatus();
        $('#watermark').change(function () {
            checkCheckboxStatus();
        });
    });
</script>
