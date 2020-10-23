<style>
    .panel-create .panel-heading {
        display: flex;
        justify-content: space-between;
        flex-wrap: wrap;
    }
    .panel-create .panel-heading .post-type {
        display: flex;
        flex-wrap: wrap;
    }
    .panel-create .panel-heading .post-type>div {
        margin-left: 5px;
    }
    .panel-create .panel-heading .post-type>div label {
        cursor: pointer;
    }
    #postPriceModal .modal-dialog {
        width: 400px;
    }
    #postPriceModal .modal-dialog input {
        width: 100%;
    }
</style>

<form action="{{ url('') }}" method="post" class="create-post-form">
  {{ csrf_field() }}
    @if ($message = Session::get('error'))
        <div class="alert alert-success">
            <p>{{ $message }}</p>
        </div>
    @endif
    <div class="panel panel-default panel-create">
        <div class="panel-heading">
            <div class="heading-text">
                {{ trans('messages.whats-going-on') }}
            </div>
            @if(Auth::id() == $timeline->user->id)
            <div class="post-type">
{{--                <div>--}}
{{--                    <input type="radio" value="free" id="free" name="type" checked class="post-type-item">--}}
{{--                    <label for="free">Free</label>--}}
{{--                </div>--}}
{{--                <div>--}}
{{--                    <input type="radio" value="paid" id="paid" name="type" class="post-type-item">--}}
{{--                    <label for="paid">For Subscribers</label>--}}
{{--                </div>--}}
{{--                <div>--}}
{{--                    <input type="radio" value="price" id="price" name="type" class="post-type-item">--}}
{{--                    <label for="price">Set Price</label>--}}
{{--                </div>--}}
                <div class="modal fade" id="postPriceModal" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-body">
                                <input type="text" name="price" class="post-price-input" placeholder="$0.00">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Set</button>
                            </div>
                        </div>
                    </div>
                </div>
                <span class="current-post-type">Free</span>&nbsp; <a href="#" class="post-amount" data-toggle="modal" data-target="#postPriceModal"></a>
                <div class="dropdown">
{{--                    <i class="fa fa-dollar" data-toggle="tooltip" title="Set a price" style="color:#859ab5;"></i>--}}
                    <div class="dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="cursor: pointer">
                        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-three-dots-vertical" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/>
                        </svg>
                    </div>
                    <ul class="dropdown-menu">
                        <li class="main-link">
                            <a href="#">
                                <div>
                                    <input type="radio" value="free" id="free" name="type" checked class="post-type-item">
                                    <label for="free">Free</label>
                                </div>
                            </a>
                            <a href="#">
                                    <div>
                                        <input type="radio" value="paid" id="paid" name="type" class="post-type-item">
                                        <label for="paid">For Subscribers</label>
                                    </div>
                            </a>
                            <a href="#">
                                <div>
                                    <input type="radio" value="price" id="price" name="type" class="post-type-item">
                                    <label for="price">Set Price</label>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            @endif
        </div>
        <div class="panel-body">        
            <textarea name="description" class="form-control createpost-form comment" cols="30" rows="3" id="createPost" cols="30" rows="2" placeholder="{{ trans('messages.post-placeholder') }}"></textarea>
                <div class="user-tags-added" style="display:none">
                    &nbsp; -- {{ trans('common.with') }}
                    <div class="user-tag-names">
                    </div>
                </div>
                <div class="user-tags-addon post-addon" style="display: none">
                    <span class="post-addon-icon"><i class="fa fa-user-plus"></i></span>
                    <div class="form-group">
                        <input type="text" id="userTags" class="form-control user-tags youtube-text" placeholder="{{ trans('messages.who_are_you_with') }}" autocomplete="off" value="" >
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div class="users-results-wrapper"></div>
                <div class="youtube-iframe"></div>
                <div class="video-addon post-addon" style="display: none">
                    <span class="post-addon-icon"><i class="fa fa-film"></i></span>
                    <div class="form-group">
                        <input type="text" name="youtubeText" id="youtubeText" class="form-control youtube-text" placeholder="{{ trans('messages.what_are_you_watching') }}"  value="" >
                        <div class="clearfix"></div>
                    </div>
                </div>
              @if((env('SOUNDCLOUD_CLIENT_ID') != "" || (env('SOUNDCLOUD_CLIENT_ID') != null)))
                <div class="music-addon post-addon" style="display: none">
                    <span class="post-addon-icon"><i class="fa fa-music" aria-hidden="true"></i></span>
                   <div class="form-group">
                      <input type="text" name="soundCloudText" autocomplete="off" id ="soundCloudText" class="form-control youtube-text" placeholder="{{ trans('messages.what_are_you_listening_to') }}"  value="" >
                      <div class="clearfix"></div>
                   </div>
                </div>
                <div class="soundcloud-results-wrapper"></div>
              @endif
            <div class="location-addon post-addon" style="display: none">
                  <span class="post-addon-icon"><i class="fa fa-map-marker" aria-hidden="true"></i></span>
                 <div class="form-group">
                    <input type="text" name="location" id="pac-input" class="form-control" placeholder="{{ trans('messages.where_are_you') }}"  autocomplete="off" value="" onKeyPress="return initMap(event)"><div class="clearfix"></div>
                 </div>                 
            </div>
              <div class="emoticons-wrapper  post-addon" style="display:none">
                  
              </div>
              <div class="images-selected post-images-selected" style="display:none">
                  <span>3</span> {{ trans('common.photo_s_selected') }}
              </div>
{{--              <div class="images-selected post-video-selected" style="display:none">--}}
{{--                  <span>3</span>--}}
{{--              </div>--}}
              <input type="hidden" name="timeline_id" value="{{ $timeline->id }}">
              <input type="hidden" name="youtube_title" value="">
              <input type="hidden" name="youtube_video_id" value="">
              <input type="hidden" name="locatio" value="">
              <input type="hidden" name="soundcloud_id" value="">
              <input type="hidden" name="user_tags" value="">
              <input type="hidden" name="soundcloud_title" value="">
              <input type="file" class="post-images-upload hidden" multiple="multiple"  accept="image/jpeg,image/png,image/gif" name="post_images_upload[]" id="post_images_upload[]">
              <input type="file" class="post-video-upload hidden"  accept="video/mp4" name="post_video_upload" >
              <div id="post-image-holder"></div>
              <div id="post-video-holder"></div>
        </div>
        <div class="panel-footer">
            <ul class="list-inline left-list">
                <li><a href="#" id="imageUpload">
                        <svg data-toggle="tooltip" title="Upload an image" width="1.0625em" height="1em" viewBox="0 0 17 16" class="bi bi-image" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                          <path fill-rule="evenodd" d="M14.002 2h-12a1 1 0 0 0-1 1v9l2.646-2.354a.5.5 0 0 1 .63-.062l2.66 1.773 3.71-3.71a.5.5 0 0 1 .577-.094L15.002 9.5V3a1 1 0 0 0-1-1zm-12-1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2h-12zm4 4.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/>
                        </svg>
                    </a>
                </li>
                <li><a href="#" id="selfVideoUpload">
                        <svg data-toggle="tooltip" title="Upload a video" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-camera-video" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                          <path fill-rule="evenodd" d="M0 5a2 2 0 0 1 2-2h7.5a2 2 0 0 1 1.983 1.738l3.11-1.382A1 1 0 0 1 16 4.269v7.462a1 1 0 0 1-1.406.913l-3.111-1.382A2 2 0 0 1 9.5 13H2a2 2 0 0 1-2-2V5zm11.5 5.175l3.5 1.556V4.269l-3.5 1.556v4.35zM2 4a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h7.5a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1H2z"/>
                        </svg>
                    </a>
                </li>
                @if((env('SOUNDCLOUD_CLIENT_ID') != "" || (env('SOUNDCLOUD_CLIENT_ID') != null)))
                  <li><a href="#" id="musicUpload"><i class="fa fa-music"></i></a></li>
                @endif
                <li><a href="#" id="locationUpload">
                        <svg data-toggle="tooltip" title="Set your location" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-geo-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                          <path fill-rule="evenodd" d="M4 4a4 4 0 1 1 4.5 3.969V13.5a.5.5 0 0 1-1 0V7.97A4 4 0 0 1 4 3.999zm2.493 8.574a.5.5 0 0 1-.411.575c-.712.118-1.28.295-1.655.493a1.319 1.319 0 0 0-.37.265.301.301 0 0 0-.057.09V14l.002.008a.147.147 0 0 0 .016.033.617.617 0 0 0 .145.15c.165.13.435.27.813.395.751.25 1.82.414 3.024.414s2.273-.163 3.024-.414c.378-.126.648-.265.813-.395a.619.619 0 0 0 .146-.15.148.148 0 0 0 .015-.033L12 14v-.004a.301.301 0 0 0-.057-.09 1.318 1.318 0 0 0-.37-.264c-.376-.198-.943-.375-1.655-.493a.5.5 0 1 1 .164-.986c.77.127 1.452.328 1.957.594C12.5 13 13 13.4 13 14c0 .426-.26.752-.544.977-.29.228-.68.413-1.116.558-.878.293-2.059.465-3.34.465-1.281 0-2.462-.172-3.34-.465-.436-.145-.826-.33-1.116-.558C3.26 14.752 3 14.426 3 14c0-.599.5-1 .961-1.243.505-.266 1.187-.467 1.957-.594a.5.5 0 0 1 .575.411z"/>
                        </svg>
                    </a>
                </li>
                <li><a href="#" id="emoticons">
                        <svg data-toggle="tooltip" title="Choose an emoji" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-emoji-smile" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                          <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                          <path fill-rule="evenodd" d="M4.285 9.567a.5.5 0 0 1 .683.183A3.498 3.498 0 0 0 8 11.5a3.498 3.498 0 0 0 3.032-1.75.5.5 0 1 1 .866.5A4.498 4.498 0 0 1 8 12.5a4.498 4.498 0 0 1-3.898-2.25.5.5 0 0 1 .183-.683z"/>
                          <path d="M7 6.5C7 7.328 6.552 8 6 8s-1-.672-1-1.5S5.448 5 6 5s1 .672 1 1.5zm4 0c0 .828-.448 1.5-1 1.5s-1-.672-1-1.5S9.448 5 10 5s1 .672 1 1.5z"/>
                        </svg>
                    </a>
                </li>
                <li><a href="#" id="emoticons">
                        <svg data-toggle="tooltip" title="Set an expiration" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-clock-history" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                          <path fill-rule="evenodd" d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022l-.074.997zm2.004.45a7.003 7.003 0 0 0-.985-.299l.219-.976c.383.086.76.2 1.126.342l-.36.933zm1.37.71a7.01 7.01 0 0 0-.439-.27l.493-.87a8.025 8.025 0 0 1 .979.654l-.615.789a6.996 6.996 0 0 0-.418-.302zm1.834 1.79a6.99 6.99 0 0 0-.653-.796l.724-.69c.27.285.52.59.747.91l-.818.576zm.744 1.352a7.08 7.08 0 0 0-.214-.468l.893-.45a7.976 7.976 0 0 1 .45 1.088l-.95.313a7.023 7.023 0 0 0-.179-.483zm.53 2.507a6.991 6.991 0 0 0-.1-1.025l.985-.17c.067.386.106.778.116 1.17l-1 .025zm-.131 1.538c.033-.17.06-.339.081-.51l.993.123a7.957 7.957 0 0 1-.23 1.155l-.964-.267c.046-.165.086-.332.12-.501zm-.952 2.379c.184-.29.346-.594.486-.908l.914.405c-.16.36-.345.706-.555 1.038l-.845-.535zm-.964 1.205c.122-.122.239-.248.35-.378l.758.653a8.073 8.073 0 0 1-.401.432l-.707-.707z"/>
                          <path fill-rule="evenodd" d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0v1z"/>
                          <path fill-rule="evenodd" d="M7.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 7 9V3.5a.5.5 0 0 1 .5-.5z"/>
                        </svg>
                    </a>
                </li>
                <li><a href="#" id="emoticons">
                        <svg data-toggle="tooltip" title="Schedule this post" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-calendar-date" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                          <path fill-rule="evenodd" d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
                          <path d="M6.445 11.688V6.354h-.633A12.6 12.6 0 0 0 4.5 7.16v.695c.375-.257.969-.62 1.258-.777h.012v4.61h.675zm1.188-1.305c.047.64.594 1.406 1.703 1.406 1.258 0 2-1.066 2-2.871 0-1.934-.781-2.668-1.953-2.668-.926 0-1.797.672-1.797 1.809 0 1.16.824 1.77 1.676 1.77.746 0 1.23-.376 1.383-.79h.027c-.004 1.316-.461 2.164-1.305 2.164-.664 0-1.008-.45-1.05-.82h-.684zm2.953-2.317c0 .696-.559 1.18-1.184 1.18-.601 0-1.144-.383-1.144-1.2 0-.823.582-1.21 1.168-1.21.633 0 1.16.398 1.16 1.23z"/>
                        </svg>
                    </a>
                </li>
            </ul>
            <ul class="list-inline right-list">
                @if($user_post == 'group' && Auth::user()->is_groupAdmin(Auth::user()->id, $timeline->groups->id) || $user_post == 'group' && $timeline->groups->event_privacy == 'members' && Auth::user()->is_groupMember(Auth::user()->id, $timeline->groups->id))                 
                  <li><a href="{!! url($username.'/groupevent/'.$timeline->groups->id) !!}" class="btn btn-default">{{ trans('common.create_event') }}</a></li>
                @endif

                <li><button type="submit" class="btn btn-submit btn-success">{{ trans('common.post') }}</button></li>
            </ul>
            <div class="clearfix"></div>
        </div>
    </div>
    <a href="javascript:;" class="change-layout three-column">
        <svg data-toggle="tooltip" title="" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-arrows-angle-expand" fill="currentColor" xmlns="http://www.w3.org/2000/svg" data-original-title="Expand layout">
          <path fill-rule="evenodd" d="M5.828 10.172a.5.5 0 0 0-.707 0l-4.096 4.096V11.5a.5.5 0 0 0-1 0v3.975a.5.5 0 0 0 .5.5H4.5a.5.5 0 0 0 0-1H1.732l4.096-4.096a.5.5 0 0 0 0-.707zm4.344-4.344a.5.5 0 0 0 .707 0l4.096-4.096V4.5a.5.5 0 1 0 1 0V.525a.5.5 0 0 0-.5-.5H11.5a.5.5 0 0 0 0 1h2.768l-4.096 4.096a.5.5 0 0 0 0 .707z"></path>
        </svg>
    </a>
    <a href="javascript:;" class="change-layout one-column">
        <svg data-toggle="tooltip" title="" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-arrows-angle-contract" fill="currentColor" xmlns="http://www.w3.org/2000/svg" data-original-title="Contract layout">
          <path fill-rule="evenodd" d="M.172 15.828a.5.5 0 0 0 .707 0l4.096-4.096V14.5a.5.5 0 1 0 1 0v-3.975a.5.5 0 0 0-.5-.5H1.5a.5.5 0 0 0 0 1h2.768L.172 15.121a.5.5 0 0 0 0 .707zM15.828.172a.5.5 0 0 0-.707 0l-4.096 4.096V1.5a.5.5 0 1 0-1 0v3.975a.5.5 0 0 0 .5.5H14.5a.5.5 0 0 0 0-1h-2.768L15.828.879a.5.5 0 0 0 0-.707z"></path>
        </svg>
    </a>
</form>
@if(Setting::get('postcontent_ad') != NULL)
    <div id="link_other" class="page-image">
        {!! htmlspecialchars_decode(Setting::get('postcontent_ad')) !!}
    </div>
@endif
<!--<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD_vuWi_hzMDDeenNYwaNAj0PHzzS2GAx8&libraries=places&callback=initMap"-->
<!--        async defer></script>-->
<script>
function initMap(event) 
{    
    var key;  
    var map = new google.maps.Map(document.getElementById('pac-input'), {
    });

    var input = /** @type {!HTMLInputElement} */(
        document.getElementById('pac-input'));        

    if(window.event)
    {
        key = window.event.keyCode; 

    }
    else 
    {
        if(event)
            key = event.which;      
    }       

    if(key == 13){       
    //do nothing 
    return false;       
    //otherwise 
    } else { 
        var autocomplete = new google.maps.places.Autocomplete(input);  
        autocomplete.bindTo('bounds', map);

    //continue as normal (allow the key press for keys other than "enter") 
    return true; 
    } 
}

let postType;
let postAmount = 0;
$(document).on('keyup', '.post-price-input', function () {
    $(this).val($(this).val().replace(/,/g, '').replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1'));
    
    postAmount = $(this).val();
});

$('#postPriceModal').on('hidden.bs.modal', function () {
    postAmount = postAmount != '' ? postAmount : 0;
    $('.post-amount').text('$'+postAmount);
});

$(document).on('change', '.post-type-item', function () {
    $('.post-price-input').val('');
    postType = parseInt($(this).val());
    $('.post-amount').text('').hide();
    $('.current-post-type').text($(this).next().text());
    if ($(this).val() === "price") {
        $('#postPriceModal').modal('show');
        $('.post-amount').show();
    }
});
</script>
