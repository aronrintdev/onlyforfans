<!-- main-section -->
<!-- <div class="main-content"> -->
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
                    <h3 class="panel-title">
                        {{ trans('common.privacy_settings') }}
                    </h3>
                </div>
                <div class="panel-body">
                    @include('flash::message')

                    {{ Form::open(array('class' => 'form-inline','url' => Auth::user()->username.'/settings/privacy', 'method' => 'post')) }}

                    {{ csrf_field() }}
                    <div class="privacy-question">

                        <ul class="list-group">
                            <!--<li href="#" class="list-group-item">-->
                            <!--	<fieldset class="form-group">-->
                        <!--		{{ Form::label('confirm_follow', trans('common.label_confirm_request')) }}-->
                        <!--		{{ Form::select('confirm_follow', array('yes' => trans('common.yes'), 'no' => trans('common.no')), $settings->confirm_follow, array('class' => 'form-control follow')) }}-->
                            <!--	</fieldset>-->
                            <!--</li>-->
                            <!--<li href="#" class="list-group-item">-->
                            <!--	<fieldset class="form-group">-->
                        <!--		{{ Form::label('follow_privacy', trans('common.label_follow_privacy')) }}-->
                        <!--		{{ Form::select('follow_privacy', array('everyone' => trans('common.everyone'), 'only_follow' => trans('common.people_i_follow')), $settings->follow_privacy, array('class' => 'form-control')) }}-->
                            <!--	</fieldset>-->
                            <!--</li>-->
                            <li href="#" class="list-group-item">
                                <fieldset class="form-group">
                                    {{ Form::label('comment_privacy', trans('common.label_comment_privacy')) }}
                                    {{ Form::select('comment_privacy', array('everyone' => trans('common.everyone'), 'only_follow' => trans('common.people_i_follow')), $settings->comment_privacy, array('class' => 'form-control')) }}
                                </fieldset>
                            </li>
                            <li href="#" class="list-group-item">
                                <fieldset class="form-group">
                                    {{  Form::label('timeline_post_privacy', trans('common.label_timline_post_privacy')) }}
                                    {{ Form::select('timeline_post_privacy', array('everyone' => trans('common.everyone'), 'only_follow' => trans('common.people_i_follow'), 'nobody' => trans('common.no_one')), $settings->timeline_post_privacy, array('class' => 'form-control')) }}
                                </fieldset>
                            </li>
                            <li href="#" class="list-group-item">
                                <fieldset class="form-group">
                                    {{ Form::label('post_privacy', trans('common.label_post_privacy')) }}
                                    {{ Form::select('post_privacy', array('everyone' => trans('common.everyone'), 'only_follow' => trans('common.people_i_follow')), $settings->post_privacy, array('class' => 'form-control')) }}
                                </fieldset>
                            </li>
                            <li href="#" class="list-group-item">
                                <fieldset class="form-group">
                                    {{ Form::label('message_privacy', trans('common.label_message_privacy')) }}
                                    {{ Form::select('message_privacy', array('everyone' => trans('common.everyone'), 'only_follow' => trans('common.people_i_follow')), $settings->message_privacy, array('class' => 'form-control')) }}
                                </fieldset>
                            </li>
                        </ul>
                        <div class="pull-right">
                            {{ Form::submit(trans('common.save_changes'), ['class' => 'btn btn-success']) }}
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div><!-- /panel -->
        </div>
        <div class="col-md-8">
            <div class="panel panel-default">
                <div class="panel-heading no-bg panel-settings">
                    <h3 class="panel-title d-flex">
                            <span>
                            {{ trans('common.blocked_profiles') }}</span>
                        <a class="pull-right text-right btn btn-success" data-toggle="modal"
                           data-target="#blockProfileModal">{{ trans('common.add') }}</a>
                    </h3>
                </div>
                <div class="panel-body">
                    @include('flash::message')

                    @if(count($blockedProfiles) > 0)
                        <div class="table-responsive manage-table">
                            <table class="table existing-products-table fans">
                                <thead>
                                <tr>
                                    <th>&nbsp;</th>
                                    <th>{{ trans('common.ip_address') }}</th>
                                    <th>{{ trans('common.country') }}</th>
                                    <th class="text-center">{{ trans('common.action') }}</th>
                                    <th>&nbsp;</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($blockedProfiles as $blockedProfile)
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>{{ $blockedProfile->ip_address != '' ? $blockedProfile->ip_address : 'N/A'}}</td>
                                        <td>{{ $blockedProfile->country != '' ? $blockedProfile->country : 'N/A' }}</td>
                                        <td class="text-center">
                                            <a href="javascript:void(0)" class="edit-block-profile text-success"
                                               style="margin-right:5px" data-id="{{ $blockedProfile->id }}"><i
                                                        class="fa fa-edit"></i></a>
                                            <a href="javascript:void(0)" class="delete-block-profile" style="color:red"
                                               data-id="{{ $blockedProfile->id }}"><i class="fa fa-trash"></i></a>
                                        </td>
                                        <td>&nbsp;</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="pagination-holder userpage">
                            {{ $blockedProfiles->render() }}
                        </div>
                    @else
                        <div class="alert alert-warning">{{ trans('messages.no_users') }}</div>
                    @endif
                </div>
            </div><!-- /panel -->
            <div id="blockProfileModal" class="tip-modal modal fade" role="dialog" tabindex='1'>
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="modal-header lists-modal">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h3 class="modal-title lists-modal-title">
                                    {{ trans('common.blocked_profiles') }}
                                </h3>
                            </div>
                            {{ Form::open(['id' => 'addBlockProfile']) }}
                            <div class="alert alert-info">{{ trans('common.add_atleast_one_field') }}</div>

                            {{ csrf_field() }}
                            <div class="b-stats-row__content form-group" style="margin:15px 20px !important;">
                                <label>{{ trans('common.ip_address') }}:</label>
                                <input type="text" name="ip_address" class="form-control" required>
                            </div>
                            <div class="b-stats-row__content form-group" style="margin:15px 20px !important;">
                                <label>{{ trans('common.country') }}:</label>
                                <input type="text" name="country" class="form-control">
                            </div>
                            <div class="modal-footer">
                                <button type="button" id="cancel" class="btn btn-default"
                                        data-dismiss="modal">{{ trans('common.cancel') }}</button>
                                <button type="button" id="blockProfile"
                                        class="btn btn-primary">{{ trans('common.save') }}</button>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
            <div id="editBlockProfileModal" class="tip-modal modal fade" role="dialog" tabindex='1'>
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="modal-header lists-modal">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h3 class="modal-title lists-modal-title">
                                    {{ trans("common.edit_blocked_profile") }}
                                </h3>
                            </div>
                            {{ Form::open(['id' => 'editBlockProfile']) }}
                            <div class="alert alert-info">{{ trans('common.add_atleast_one_field') }}</div>

                            {{ csrf_field() }}
                            <input type="hidden" name="id" id="blockProfileId">
                            <div class="b-stats-row__content form-group" style="margin:15px 20px !important;">
                                <label>{{ trans('common.ip_address') }}</label>
                                <input type="text" name="ip_address" id="ipAddress" class="form-control" required>
                            </div>
                            <div class="b-stats-row__content form-group" style="margin:15px 20px !important;">
                                <label>{{ trans('common.country') }}:</label>
                                <input type="text" name="country" id="country" class="form-control">
                            </div>
                            <div class="modal-footer">
                                <button type="button" id="cancel" class="btn btn-default"
                                        data-dismiss="modal">{{ trans('common.cancel') }}</button>
                                <button type="button" id="editBlockProfileBtn"
                                        class="btn btn-primary">{{ trans('common.save') }}</button>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-heading no-bg panel-settings">
                    <h3 class="panel-title">
                        {{ trans('common.water_mark_settings') }}
                    </h3>
                </div>
                <div class="panel-body nopadding">
                    <div class="fans-form">
                        <form method="POST" action="{{ url('/'.Auth::user()->username.'/settings/save-watermark-settings') }}" enctype="multipart/form-data">
                            {{ csrf_field() }}

                            <div class="row">
                                <div class="col-md-12">
                                    {{ Form::label('watermark_text', trans('Enable/Disable Watermark')) }}
                                    <fieldset class="form-group toggle {{ $errors->has('watermark_text') ? ' has-error' : '' }}">
                                        <input type="checkbox" id="watermark" name="watermark" class="form-control"
                                               {{ \Illuminate\Support\Facades\Auth::user()->settings()->watermark == 1 ? 'checked' : '' }} value="{{ \Illuminate\Support\Facades\Auth::user()->settings()->watermark == 1 ? 1 : 0 }}"/><label for="watermark">watermark</label>
                                    </fieldset>
                                </div>
                                <div class="watermark_settings">
                                    <div class="col-md-6">
                                        <fieldset class="form-group watermark_text {{ $errors->has('watermark_text') ? ' has-error' : '' }}">
                                            {{ Form::label('watermark_text', trans('common.watermark_text')) }}
                                            <input type="text" class="form-control" id="watermark_text" name="watermark_text" placeholder= "{{ trans('common.watermark_text') }}" value="{{ \Illuminate\Support\Facades\Auth::user()->settings()->watermark_text }}">

                                            @if ($errors->has('watermark_text'))
                                                <span class="help-block">
														{{ $errors->first('watermark_text') }}
													</span>
                                            @endif
                                        </fieldset>
                                    </div>
                                    <div class="col-md-6">
                                        <fieldset class="form-group watermark_file {{ $errors->has('watermark_file') ? ' has-error' : '' }}">
                                            {{ Form::label('watermark_file', trans('common.watermark_file')) }}
                                            @if(isset(Auth::user()->settings()->watermark_file_id))
                                                <a href="{{ $waterMarkUrl }}" download="{{\Illuminate\Support\Facades\Auth::user()->watermark_file_id}}">
                                                    {{ trans('common.existing_file') }}</a>
                                            @endif
                                            <input type="file" class="form-control" id="watermark_file" name="watermark_file" placeholder= "{{ trans('common.watermark_file') }}">

                                            @if ($errors->has('watermark_file'))
                                                <span class="help-block">
														{{ $errors->first('watermark_file') }}
													</span>
                                            @endif
                                            @if ($errors->any())
                                                <span class="help-block" style="color: red;">
													@foreach ($errors->all(':message') as $input_error)
                                                        {{ $input_error }}
                                                    @endforeach
													</span>
                                            @endif
                                        </fieldset>
                                    </div>
                                    <div class="col-md-6">
                                        <fieldset class="form-group {{ $errors->has('watermark_font_size') ? ' has-error' : '' }}">
                                            {{ Form::label('watermark_font_size', trans('common.watermark_font_size')) }}
                                            <input type="number" class="form-control" id="watermark_font_size" min="1" name="watermark_font_size" value="{{ \Illuminate\Support\Facades\Auth::user()->settings()->watermark_font_size }}" placeholder= "{{ trans('common.watermark_font_size') }}">

                                            @if ($errors->has('watermark_font_size'))
                                                <span class="help-block">
														{{ $errors->first('watermark_font_size') }}
													</span>
                                            @endif
                                        </fieldset>
                                    </div>
                                    <div class="col-md-6">
                                        <fieldset class="form-group {{ $errors->has('watermark_position') ? ' has-error' : '' }}">
                                            {{ Form::label('watermark_position', trans('common.watermark_position')) }}
                                            {{ Form::select('watermark_position', get_image_insert_location(), Auth::user()->settings()->watermark_position, array('class' => 'form-control')) }}
                                            @if ($errors->has('watermark_position'))
                                                <span class="help-block">
														{{ $errors->first('watermark_position') }}
													</span>
                                            @endif
                                        </fieldset>
                                    </div>
                                    <div class="col-md-6">
                                        <fieldset class="form-group {{ $errors->has('watermark_font_color') ? ' has-error' : '' }}">
                                            {{ Form::label('watermark_font_color', trans('common.watermark_font_color')) }}
                                            <input type="text" class="form-control" id="watermark_font_color" name="watermark_font_color" value="{{ \Illuminate\Support\Facades\Auth::user()->settings()->watermark_font_color }}" placeholder= "{{ '#'.trans('common.color_code') }}">

                                            @if ($errors->has('watermark_font_color'))
                                                <span class="help-block">
														{{ $errors->first('watermark_font_color') }}
													</span>
                                            @endif
                                        </fieldset>
                                    </div>
                                </div>
                            </div>

                            <div class="pull-right">
                                {{ Form::submit(trans('common.save_changes'), ['class' => 'btn btn-success']) }}
                            </div>
                            <div class="clearfix"></div>
                        </form>
                    </div><!-- /fans-form -->
                </div>
            </div>
        </div>
    </div><!-- /row -->
</div>
<!-- </div> --><!-- /main-content -->
<script type="text/javascript">
    let url = "{{ route('block-profile',['username' => Auth::user()->username]) }}";
    let updateUrl = "{{ route('update-block-profile',['username' => Auth::user()->username]) }}";
    let editUrl = "{{ url(Auth::user()->username.'/settings/edit-block-profile') }}";
    let deleteUrl = "{{ url(Auth::user()->username.'/settings/delete-block-profile') }}";
    function notify(message,type,layout) {
        var n = noty({
            text: message,
            layout: 'bottomLeft',
            type: type ? type : 'success',
            theme: 'relax',
            timeout: 5000,
            animation: {
                open: 'animated fadeIn', // Animate.css class names
                close: 'animated fadeOut', // Animate.css class names
                easing: 'swing', // unavailable - no need
                speed: 500 // unavailable - no need
            }
        });
    }
    $('#blockProfile').click(function () {
        $(this).attr('disabled', true);
        $.ajax({
            url: url,
            type: 'post',
            data: new FormData($('#addBlockProfile')[0]),
            processData: false,
            contentType: false,
            success: function (result) {
                location.reload();
            },
            error: function (result) {
                notify(result.responseJSON.ip_address,'error');
            },
            complete: function () {
                $('#blockProfile').attr('disabled', false);
            },
        });
    });

    $('.edit-block-profile').click(function () {
        let id = $(this).data('id');
        $.ajax({
            url: editUrl + '/' + id,
            type: 'get',
            success: function (result) {
                $('#blockProfileId').val(result.blockedProfile.id);
                $('#ipAddress').val(result.blockedProfile.ip_address);
                $('#country').val(result.blockedProfile.country);
                $('#editBlockProfileModal').modal('show');
            },
            error: function (result) {
                notify(result.responseJSON,'error');
            },
        });
    });

    $('#editBlockProfileBtn').click(function () {
        $(this).attr('disabled', true);
        $.ajax({
            url: updateUrl,
            type: 'post',
            data: new FormData($('#editBlockProfile')[0]),
            processData: false,
            contentType: false,
            success: function (result) {
                location.reload();
            },
            error: function (result) {
                notify(result.responseJSON.ip_address,'error');
            },
            complete: function () {
                $('#editBlockProfileBtn').attr('disabled', false);
            },
        });
    });

    $('.delete-block-profile').click(function () {
        let id = $(this).data('id');
        $.confirm({
            title: 'Confirm!',
            content: 'Do you want to delete Blocked Profile?',
            confirmButton: 'Yes',
            cancelButton: 'No',
            confirmButtonClass: 'btn-primary',
            cancelButtonClass: 'btn-danger',
            closeOnConfirm: false,
            showLoaderOnConfirm: true,

            confirm: function () {
                $.ajax({
                    url: deleteUrl + '/' + id,
                    type: 'delete',
                    success: function (result) {
                        location.reload();
                    },
                    error: function (result) {
                        notify(result.responseJSON,'error');
                    },
                });
            },
            cancel: function () {

            },
        });
    });

    $('#blockProfileModal').on('hidden.bs.modal', function () {
        $('#addBlockProfile')[0].reset();
    });

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