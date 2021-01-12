<style>    
    .change-layout {
        display: none;
    }
    
    .switch-layout img {
        width: 25px;
    }

    .switch-wrapper {
        vertical-align: top;
        margin-right: 5px;
    }

	.switch-wrapper i {
		font-size: 25px;
	}

    @media screen and (min-width: 1200px) {
        .timeline-condensed-column .panel-body, .jscroll-added .panel-body, .timeline-condensed-column > .col-lg-4 .panel-body {
            //height: 200px;
            overflow: auto;
        }   
    }
    
    .timeline-condensed-column .panel-heading .user-post-details li:last-child, .jscroll-added .panel-heading .user-post-details li:last-child, .timeline-condensed-column > .col-lg-4 .panel-heading .user-post-details li:last-child {
      /*
        max-width: 100px;
        */
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
    }

    .panel-post .single-image-panel {
        padding: 0;
    }
    .single-image-panel .single-image {
        margin: 0 !important;
    }

    .single-image-panel img{
        margin: 0 !important;
    }

    .single-image-panel a {
        margin: 0!important;
    }
    
    .favourite-grid {
        margin: 0 -7px;
    }
    
    .favourite-grid .img {
        background: #ccc;
        height: 100px;        
        margin-bottom: 14px;
    }

    .post-detail-modal .modal-body {
        padding: 0;
    }

    .post-detail-modal .modal-body .panel {
        margin-bottom: 0;
        box-shadow: none;
    }

    .post-detail-modal .modal-body .panel .panel-body {
        padding: 0;
        padding-bottom: 8px;
    }

    .favourite-grid .img .grid-item, .favourite-grid .img .post-image-holder, .favourite-grid .img .grid-item a, .favourite-grid .img .grid-item>.post-image-holder>a>img{
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    @media screen and (max-width: 1024px) {
        .favourite-grid .img {
            height: 200px;
        }
    }
    
    .favourite-grid > div {
        padding: 0 7px;
    }
    
    .profile-posts .active {
        animation: slideDown 0.5s forwards;
        transform-origin: top center;
    }
    
    @keyframes slideDown {
        0% {
            transform: scale(.9);
            opacity: 0;
        }
        50% {
            transform: scale(1.1);
        }
        100% {
            transform: scale(1);
            opacity: 1;
        }
    }
    
    .tabcontent {
        padding: 15px 0;
    }

    #Active.tabcontent .post-image-holder, #Expired.tabcontent .post-v-holder, #Active.tabcontent .locked-holder {
        margin-bottom: 30px;
    }
    
    #Active.tabcontent .post-image-holder a, #Expired.tabcontent .post-v-holder a{
        display: block;
        height: 180px !important;
        margin: 0;
        max-height: 350px;
        width: 100% !important;
    }

    #Active.tabcontent .post-image-holder a img, #Expired.tabcontent .post-v-holder a source{
        object-fit: cover !important;
        height: 100% !important;
        width: 100% !important;
    }

    .favourite-grid .post-v-holder a {
        height: 100px !important;
        display: block;
        background: #000;
        position: relative;
    }

    .favourite-grid .post-v-holder a:after {
        content: "\f144";
        display: inline-block;
        font: normal normal normal 14px/1 FontAwesome;
        font-size: 35px;
        color: #fff;
        text-rendering: auto;
        position: absolute;
        transform: translate(50%, -50%);
        right: 50%;
        top: 50%;
    }
    
    .favourite-grid .post-v-holder a video, .favourite-grid .post-v-holder a audio {
        display: none;
    }
    
    .favourite-grid .locked-content {
        height: 100%;
        width: 100%;
        margin: 0;
        font-size: 25px;
    }

    .favourite-grid .locked-content .btn {
        background-color: transparent;
        background-image: none;
        border-color: transparent;
        outline: none;
    }
    
    @media screen and (min-width: 992px) {
        .tabs-wrapper {
            margin-top: -85px;
        }

        .wide-tab {
            display: flex;
            justify-content: center;
        }
    }
    
    @media screen and (max-width: 768px) {
        .close-post-modal {
            height: 20px;
            display: block !important;
            width: 20px;
            position: absolute;
            top: -5px;
            right: -5px;
            box-shadow: 0 0 5px rgba(0,0,0,0.5);
            background: #fff !important;
            opacity: 1;
            border-radius: 50%;
        }
    }
    
    .locked-content.media-locked {
        margin: 0;
        height: 180px;
    }
    
    .fancybox-button--play {
        display: none !important;
    }
    
    .fancybox-button--thumbs {
        display: none !important;
    }

    .default-post.panel-post .image-with-blur .single-image {
        height: 440px;
        position: relative;
    }

    @media screen and (max-width: 576px) {
        .default-post.panel-post .image-with-blur .single-image {
            height: 250px
        }
    }

    .default-post.panel-post .image-with-blur .single-image a img {
        height: auto;
        width: auto;
        max-height: 100% !important;
        max-width: 100% !important;
    }

    .default-post.panel-post .image-with-blur .single-image a {
        position: absolute;
        text-align: center;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .default-post.panel-post .image-with-blur .single-image .first-image {
        z-index: 9 !important;
    }

    .default-post.panel-post .with-text {
        display: flex;
        max-height: inherit !important;
    }

    .default-post.panel-post .with-text a {
        flex-grow: 1;
        height: 100%;
        max-height: 100%;
    }

    .default-post.panel-post .with-text img {
        width: 100%;
        height: 400px;
        object-fit: cover;
        max-height: 100% !important;
    }

    @media screen and (max-width: 576px) {
        .default-post.panel-post .with-text img {
            height: 250px
        }
    }

    .explore-post .single-image {
        max-height: inherit !important;
        height: 400px;
        overflow: hidden;
    }

    .explore-post.panel-post .image-with-blur .single-image .first-image {
        z-index: 9;
    }

    .explore-post.panel-post .image-with-blur .single-image {
        height: 440px;
        position: relative;
    }

    .explore-post.panel-post .image-with-blur .single-image a {
        position: absolute;
        text-align: center;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .explore-post.panel-post .image-with-blur .single-image img {
        height: auto !important;
        width: auto !important;
        max-height: 100% !important;
        max-width: 100% !important;
    }s
</style>

@php
  $sessionUser = Auth::user();
@endphp

<!-- %VIEW: public/themes/default/views/timeline/posts -->
<div class="container profile-posts">
	<div class="row">
		<div class="col-md-12">
			{!! Theme::partial('user-header',compact('timeline', 'liked_post', 'liked_pages','user','joined_groups','followRequests','following_count',
			'followers_count','follow_confirm','user_post','joined_groups_count','guest_events', 'user_lists')) !!}
			<div class="row">
				<div class=" timeline">
					<div class="col-md-4 sidebar-wrapper">
						{!! Theme::partial('user-leftbar',compact('timeline','user','follow_user_status','own_groups','own_pages','user_events', 'favouritePosts', 'next_page_url')) !!}
					</div>
					<div class="col-md-8 content-wrapper">						
                            <div class="panel-body row tabs-wrapper nopadding">
                                <div class="tab">
                                    <button class="tablinks active" onclick="openCity(event, 'All')">
                                        Posts
                                    </button>
                                    <button class="tablinks" onclick="openCity(event, 'Active')">
                                        Photos
                                    </button>
                                    <button class="tablinks" onclick="openCity(event, 'Expired')">
                                        Video
                                    </button>
                                    @if($user->id != Auth::user()->id)
                                    <button class="tablinks" data-toggle="modal" data-target="#sendTipModal">
                                        Send Tip
                                    </button>
                                    @endif
                                </div>
                                <div id="All" class="tabcontent">
                                    @if($timeline->type == "user" && $user->id == Auth::user()->id)
                                        {!! Theme::partial('create-post',compact('timeline','user_post')) !!}
                                    @endif
                                    <div class="lists-dropdown-menu">
                                        <ul class="list-inline text-right no-margin">
                                            <li class="dropdown">
                                                <a href="#" class="dropdown-togle lists-dropdown-icon" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" style="color: #298ad3; display: inline-flex">
                                                    <svg data-toggle="tooltip" title="Sort" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-filter-circle" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                        <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                                        <path fill-rule="evenodd" d="M7 11.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5z"/>
                                                    </svg>
                                                </a>
                                                <ul class="post-dropdown-menu dropdown-menu profile-dropdown-menu-content">
                                                    <li class="main-link">
                                                        <div class="form-check">
                                                            <input class="red-checkbox" type="radio" name="period-post" id="periodAllTime" value="all" {{$period == 'all' ? "checked" : ""}}>
                                                            <label class="red-list-label" for="periodAllTime">
                                                                All time
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="red-checkbox" type="radio" name="period-post" id="periodLastThreeM" value="3m" {{$period == '3m' ? "checked" : ""}}>
                                                            <label class="red-list-label" for="periodLastThreeM">
                                                                Last three months
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="red-checkbox" type="radio" name="period-post" id="periodLastOneM" value="1m" {{$period == '1m' ? "checked" : ""}}>
                                                            <label class="red-list-label" for="periodLastOneM">
                                                                Last month
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="red-checkbox" type="radio" name="period-post" id="periodLastW" value="1w" {{$period == '1w' ? "checked" : ""}}>
                                                            <label class="red-list-label" for="periodLastW">
                                                                Last week
                                                            </label>
                                                        </div>
                                                    </li>
                                                    <hr>
                                                    <li class="main-link">
                                                        <div class="form-check">
                                                            <input class="red-checkbox" type="radio" name="sort-profile-post" id="sortByLatest" value="latest" {{$sort_by == 'latest' ? "checked" : ""}}>
                                                            <label class="red-list-label" for="sortByLatest">
                                                                Latest Posts
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="red-checkbox" type="radio" name="sort-profile-post" id="soryByLiked" value="liked" {{$sort_by == 'liked' ? "checked" : ""}}>
                                                            <label class="red-list-label" for="soryByLiked">
                                                                Most Liked
                                                            </label>
                                                        </div>
                                                    </li>
                                                    <hr>
                                                    <li class="main-link">
                                                        <div class="form-check">
                                                            <input class="red-checkbox" type="radio" name="order-profile-post" id="orderByASC" value="asc" {{$order_by == 'asc' ? "checked" : ""}}>
                                                            <label class="red-list-label" for="orderByASC">
                                                                Ascending
                                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="red-checkbox" type="radio" name="order-profile-post" id="orderByDESC" value="desc" {{$order_by == 'desc' ? "checked" : ""}}>
                                                            <label class="red-list-label" for="orderByDESC">
                                                                Descending
                                                            </label>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </li>
                                            <li class="switch-wrapper">
                                                <a href="javascript:;" class="switch-layout three-column">
                                                    <svg data-toggle="tooltip" title="Expand layout" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-arrows-angle-expand" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                        <path fill-rule="evenodd" d="M5.828 10.172a.5.5 0 0 0-.707 0l-4.096 4.096V11.5a.5.5 0 0 0-1 0v3.975a.5.5 0 0 0 .5.5H4.5a.5.5 0 0 0 0-1H1.732l4.096-4.096a.5.5 0 0 0 0-.707zm4.344-4.344a.5.5 0 0 0 .707 0l4.096-4.096V4.5a.5.5 0 1 0 1 0V.525a.5.5 0 0 0-.5-.5H11.5a.5.5 0 0 0 0 1h2.768l-4.096 4.096a.5.5 0 0 0 0 .707z"/>
                                                    </svg>
                                                </a>
                                            </li>
                                            <li class="switch-wrapper" style="display: none">
                                                <a href="javascript:;" class="switch-layout one-column">
                                                    <svg data-toggle="tooltip" title="Contract layout" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-arrows-angle-contract" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                        <path fill-rule="evenodd" d="M.172 15.828a.5.5 0 0 0 .707 0l4.096-4.096V14.5a.5.5 0 1 0 1 0v-3.975a.5.5 0 0 0-.5-.5H1.5a.5.5 0 0 0 0 1h2.768L.172 15.121a.5.5 0 0 0 0 .707zM15.828.172a.5.5 0 0 0-.707 0l-4.096 4.096V1.5a.5.5 0 1 0-1 0v3.975a.5.5 0 0 0 .5.5H14.5a.5.5 0 0 0 0-1h-2.768L15.828.879a.5.5 0 0 0 0-.707z"/>
                                                    </svg>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="timeline-posts timeline-default mark-a">
                                        @if(count($posts) > 0)
                                            @foreach($posts as $post)
                                              <article class="crate-post" id="tag-post_id_{{$post->id}}">
                                                  @if ( $post->isViewableByUser($sessionUser) )
                                                        {!! Theme::partial('post',compact('post','timeline','next_page_url', 'user')) !!}
                                                  @endif
                                              </article>
                                            @endforeach
                                        @else
                                            <p class="no-posts">{{ trans('messages.no_posts') }}</p>
                                        @endif
                                    </div>
                                  @php
                                    $twoColumn = false; // %PSG true; 
                                  @endphp
                                    <div class="timeline-posts timeline-condensed-column row mark-b" style="display: none">
                                        @if(count($posts) > 0)
                                            @foreach($posts as $post)
                                              <article class="crate-post" id="tag-post_id_{{$post->id}}">
                                                @if ( $post->isViewableByUser($sessionUser) )
                                                    {!! Theme::partial('post',compact('post','timeline','next_page_url', 'user','twoColumn')) !!}
                                                @endif
                                              </article>
                                            @endforeach
                                        @else
                                            <p class="no-posts">{{ trans('messages.no_posts') }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div id="Active" class="tabcontent">
                                    @if(count($postMedia) > 0)
                                        <div class="row">
                                            @foreach($postMedia as $post)
                                                @if(count($post->images()->get()) > 0 && $post->images()->get()->first()->type=='image')
                                                    <div class="timeline-photos">
                                                        @if ( $post->isViewableByUser($sessionUser) )
                                                            {!! Theme::partial('post_media',compact('post','timeline','next_page_url', 'user')) !!}
                                                        @endif
                                                    </div>
                                                @endif
                                            @endforeach                                            
                                        </div>
                                    @endif
                                </div>
                                <div id="Expired" class="tabcontent">
                                    @if(count($postMedia) > 0)
                                        <div class="row">
                                            @foreach($postMedia as $post)
                                                @if(count($post->images()->get()) > 0 && $post->images()->get()->first()->type=='video')
                                                    @if ( $post->isViewableByUser($sessionUser) )
                                                        {!! Theme::partial('post_media',compact('post','timeline','next_page_url', 'user')) !!}
                                                    @endif
                                                @endif
                                            @endforeach                                            
                                        </div>
                                    @else
                                        <p class="no-posts">{{ trans('messages.no_posts') }}</p>
                                    @endif
                                </div>
                            </div>
						
					</div><!-- /col-md-8 -->
				</div><!-- /main-content -->
			</div><!-- /row -->
		</div><!-- /col-md-10 -->
{{--		<div class="col-md-2">--}}
{{--			{!! Theme::partial('timeline-rightbar') !!}--}}
{{--		</div>--}}
	</div>
</div><!-- /container -->

<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css" />
<script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js"></script>


<script type="text/javascript">
    $('.switch-layout').click(function () {
        $('.timeline-default, .timeline-condensed-column, .sidebar-wrapper').toggle();
        $('.content-wrapper').toggleClass('col-lg-12 col-md-8');
        $('.content-wrapper .tabs-wrapper .tab').toggleClass('wide-tab');
        condensedLayout = !condensedLayout;
        twoColumn = !twoColumn;
        $.each($(".timeline-condensed-column>.panel"), function (i) {
            $(this).wrap('<div class="col-lg-6"></div>');
        });
        $('.switch-layout.three-column, .switch-layout.one-column').parent().toggle();
        if (!condensedLayout) {
            $.each($(".timeline-default>.col-lg-6 .panel"), function (i) {
                $(this).unwrap();
            });
        }
    });

    $("#All").slideDown();

    function openCity(evt, cityName) {
        // Declare all variables
        var i, tabcontent, tablinks;

        // Get all elements with class="tabcontent" and hide them
        tabcontent = document.getElementsByClassName("tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
            tabcontent[i].classList.remove('active');
        }

        // Get all elements with class="tablinks" and remove the class "active"
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }

        // Show the current tab, and add an "active" class to the button that opened the tab
        document.getElementById(cityName).style.display = "block";
        document.getElementById(cityName).classList.add('active');
        evt.currentTarget.className += " active";
    }
</script>
