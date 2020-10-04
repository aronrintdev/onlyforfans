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
    <div class="panel panel-default panel-create"> <!-- panel-create -->
        <div class="panel-heading">
            <div class="heading-text">
                {{ trans('messages.whats-going-on') }}
            </div>
            <div class="post-type">
                <div>
                    <input type="radio" value="free" id="free" name="type" checked class="post-type-item">
                    <label for="free">Free</label>
                </div>
                <div>
                    <input type="radio" value="paid" id="paid" name="type" class="post-type-item">
                    <label for="paid">For Subscribers</label>
                </div>
                <div>
                    <input type="radio" value="price" id="price" name="type" class="post-type-item">
                    <label for="price">Set Price</label>
                </div>
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
            </div>
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
              <!-- Hidden elements  -->
              <input type="hidden" name="timeline_id" value="{{ $timeline->id }}">
              <input type="hidden" name="youtube_title" value="">
              <input type="hidden" name="youtube_video_id" value="">
              <input type="hidden" name="locatio" value="">
              <input type="hidden" name="soundcloud_id" value="">
              <input type="hidden" name="user_tags" value="">
              <input type="hidden" name="soundcloud_title" value="">
              <input type="file"   class="post-images-upload hidden" multiple="multiple"  accept="image/jpeg,image/png,image/gif" name="post_images_upload[]" id="post_images_upload[]">
              <input type="file" class="post-video-upload hidden"  accept="video/mp4" name="post_video_upload" >
              <div id="post-image-holder"></div>
                <div id="post-video-holder"></div>
        </div><!-- panel-body -->

        <div class="panel-footer">
            <ul class="list-inline left-list">
                <li><a href="#" id="imageUpload"><i class="fa fa-camera-retro"></i></a></li>
                 <li><a href="#" id="selfVideoUpload"><i class="fa fa-film"></i></a></li>
                @if((env('SOUNDCLOUD_CLIENT_ID') != "" || (env('SOUNDCLOUD_CLIENT_ID') != null)))
                  <li><a href="#" id="musicUpload"><i class="fa fa-music"></i></a></li>
                @endif
                <li><a href="#" id="locationUpload"><i class="fa fa-map-marker"></i></a></li>
                <li><a href="#" id="emoticons"><i class="fa fa-smile-o"></i></a></li>
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
        <i class="fa fa-th"></i>
    </a>
    <a href="javascript:;" class="change-layout one-column">
        <i class="fa fa-align-justify"></i>
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
$(document).on('keyup', '.post-price-input', function () {
    $(this).val($(this).val().replace(/,/g, '').replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1'));
});

$(document).on('change', '.post-type-item', function () {
    $('.post-price-input').val('');
    postType = parseInt($(this).val());
    console.log($(this).val());
    if ($(this).val() === "price") {
        console.log($(this).val());
        $('#postPriceModal').modal('show');
    }
});
</script>




