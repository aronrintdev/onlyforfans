<style>
    #routing::-webkit-outer-spin-button,
    #routing::-webkit-inner-spin-button,
    #account::-webkit-outer-spin-button,
    #account::-webkit-inner-spin-button{
        -webkit-appearance: none;
        margin: 0;
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
							{{ trans('common.add_bank') }}
						</h3>
					</div>
					<div class="panel-body nopadding">
                        <div class="tab">
                            <button class="tablinks active" onclick="openCity(event, 'Details')">
                                Account Details
                            </button>
                            <button class="tablinks" onclick="openCity(event, 'Payouts')">
                                Payouts
                            </button>
                            <button class="tablinks" onclick="openCity(event, 'Verified')">
                                Verify ID
                            </button>
                        </div>
                        <div id="Details" class="tabcontent">
                            <div class="fans-form">
                                <form  method="POST" action="{{ url('/'.$username.'/settings/bankdetails') }}" class="bank-details">
                                    {{ csrf_field() }}

                                    <div class="row">
                                    	<div class="col-md-6">
                                            <fieldset class="form-group required">
                                                <label for="bank-name">Bank Name</label>
                                                <input type="text" name="bank_name" id="bank-name" class="form-control" value="{{ $bankAccountDetails ? $bankAccountDetails->bank_name : '' }}" placeholder="Bank Name" required>
                                                @if($errors->has('bank_name'))
                                                    <label for="" class="text-danger">{{ $errors->first('bank_name') }}</label>
                                                @endif
                                            </fieldset>
                                    	</div>
                                    </div>
                                    <div class="row">
                                    	<div class="col-md-6">
                                            <fieldset class="form-group required">
                                                <label for="routing">Routing Number</label>
                                                <input type="number" name="routing" id="routing" class="form-control" value="{{ $bankAccountDetails ? $bankAccountDetails->routing : '' }}" placeholder="Routing Number" required>
                                                @if($errors->has('routing'))
                                                    <label for="" class="text-danger">{{ $errors->first('routing') }}</label>
                                                @endif
                                            </fieldset>
                                    	</div>
                                    </div>
                                    <div class="row">
                                    	<div class="col-md-6">
                                            <fieldset class="form-group required">
                                                <label for="account">Account Number</label>
                                                <input type="number" name="account" id="account" class="form-control" value="{{ $bankAccountDetails ? $bankAccountDetails->account : '' }}" placeholder="Account Number" required>
                                                @if($errors->has('account'))
                                                    <label for="" class="text-danger">{{ $errors->first('account') }}</label>
                                                @endif
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
                                        {{ Form::submit(trans('common.save_changes'), ['class' => 'btn btn-success oauth-link']) }}
                                    </div>
                                    <div class="clearfix"></div>
                                </form>
                            </div>
                        </div>
                        <div id="Payouts" class="tabcontent">
                            <div class="fans-form">
                                <table class="table apps-table fans">
                                    <tr>
                                        <td style="padding: 5px"><label>Gross Tips: </label></td>
                                        <td style="padding: 5px">${{ number_format($totalTip, 2) }} <em>(Received)</em></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 5px"><label>Gross Subscriptions: </label></td>
                                        <td style="padding: 5px">${{ number_format($subscriptionAmount, 2) }} <em>(Received)</em></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div id="Verified" class="tabcontent">
                            <div class="fans-form">
                                <form  method="POST" action="{{ url('/'.$username.'/settings/save-bank-details') }}" class=".bank-details">
                                    {{ csrf_field() }}
                                    <div class="row">
                                        <div class="col-md-6">
                                            <fieldset class="form-group required {{ $errors->has('name') ? ' has-error' : '' }}">
                                                {{ Form::label('name', trans('common.fullname')) }}
                                                {{ Form::text('name', Auth::user()->name, ['name' => 'name', 'value' => 'value','class' => 'form-control', 'placeholder' => trans('common.fullname')]) }}
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
                                            <fieldset class="form-group required {{ $errors->has('country') ? ' has-error' : '' }}">
                                                {{ Form::label('country', trans('common.country')) }}
                                                {{ Form::text('country', Auth::user()->payment != NULL ? Auth::user()->payment->country : '', array('class' => 'form-control', 'placeholder' => trans('common.country'))) }}
                                            </fieldset>
                                            @if ($errors->has('country'))
                                                <span class="help-block">
												{{ $errors->first('country') }}
											</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <fieldset class="form-group required {{ $errors->has('address') ? ' has-error' : '' }}">
                                                {{ Form::label('address', trans('common.street_address')) }}
                                                {{ Form::text('address', Auth::user()->payment != NULL ? Auth::user()->payment->address : '', ['class' => 'form-control', 'placeholder' => trans('common.street_address_placeholder')]) }}
                                            </fieldset>
                                            @if ($errors->has('address'))
                                                <span class="help-block">
												{{ $errors->first('address') }}
											</span>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <fieldset class="form-group required {{ $errors->has('city') ? ' has-error' : '' }}">
                                                {{ Form::label('city', trans('common.city_placeholder')) }}
                                                {{ Form::text('city', Auth::user()->payment != NULL ? Auth::user()->payment->city : '', ['class' => 'form-control', 'placeholder' => trans('common.city')]) }}
                                            </fieldset>
                                            @if ($errors->has('city'))
                                                <span class="help-block">
												{{ $errors->first('city') }}
											</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <fieldset class="form-group required {{ $errors->has('state') ? ' has-error' : '' }}">
                                                {{ Form::label('state', trans('common.state_province')) }}
                                                {{ Form::text('state', Auth::user()->payment != NULL ? Auth::user()->payment->state : '', ['class' => 'form-control', 'placeholder' => trans('common.state_province')]) }}
                                            </fieldset>
                                            @if ($errors->has('state'))
                                                <span class="help-block">
												{{ $errors->first('state') }}
											</span>
                                            @endif
                                        </div>
                                        <div class="col-md-6">
                                            <fieldset class="form-group required {{ $errors->has('zip') ? ' has-error' : '' }}">
                                                {{ Form::label('zip', trans('common.zip_postal_code')) }}
                                                {{ Form::text('zip', Auth::user()->payment != NULL ? Auth::user()->payment->zip : '', ['class' => 'form-control', 'placeholder' => trans('common.zip_postal_code')]) }}
                                            </fieldset>
                                            @if ($errors->has('zip'))
                                                <span class="help-block">
												{{ $errors->first('zip') }}
											</span>
                                            @endif
                                        </div>
                                    </div>
                                    <hr>

                                    <div class="row">
                                    	<div class="col-md-6">
                                    		<fieldset class="form-group">
                                    			{{ Form::label('document', trans('Document Type')) }}
                                    			{{ Form::select('document', array('Select' => 'Select One...', 'Passport' => 'Passport', 'Drivers License' => 'Drivers License', 'ID Card' => 'ID Card'), 'Select') }}
                                    		</fieldset>
                                    	</div>
                                    </div>
                                    <div class="row">
                                    	<div class="col-md-6">
                                    		<fieldset class="form-group">
                                    			{{ Form::label('photoID', trans('Photo of Your ID')) }}
                                    			{{ Form::file('image') }}
                                    		</fieldset>
                                    	</div>
                                    </div>
                                    <div class="row">
                                    	<div class="col-md-6">
                                    		<fieldset class="form-group">
                                    			{{ Form::label('photoholdingID', trans('Photo of You Holding ID')) }}
                                    			{{ Form::file('image') }}
                                    		</fieldset>
                                    	</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-10">
                                            <fieldset class="form-group {{ $errors->has('sell_content_confirm') ? ' has-error' : '' }}">
                                                {{ Form::checkbox('sell_content_confirm', 'Yes') }}
                                                {{ Form::label('sell_content_confirm', trans('messages.adult_content_confirm')) }}
                                            </fieldset>
                                            @if ($errors->has('sell_content_confirm'))
                                                <span class="help-block">
												{{ $errors->first('sell_content_confirm') }}
											</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="pull-right">
                                        {{ Form::submit(trans('common.save_changes'), ['class' => 'btn btn-success oauth-link']) }}
                                    </div>
                                    <div class="clearfix"></div>
                                </form>
                            </div>
                        </div>
					</div>
				</div>
			</div>
		</div>
    </div>
        <script>
            $("#Details").slideDown();

            function openCity(evt, cityName) {
                // Declare all variables
                var i, tabcontent, tablinks;

                // Get all elements with class="tabcontent" and hide them
                tabcontent = document.getElementsByClassName("tabcontent");
                for (i = 0; i < tabcontent.length; i++) {
                    tabcontent[i].style.display = "none";
                }

                // Get all elements with class="tablinks" and remove the class "active"
                tablinks = document.getElementsByClassName("tablinks");
                for (i = 0; i < tablinks.length; i++) {
                    tablinks[i].className = tablinks[i].className.replace(" active", "");
                }

                // Show the current tab, and add an "active" class to the button that opened the tab
                document.getElementById(cityName).style.display = "block";
                evt.currentTarget.className += " active";
            }
        </script>
