<!-- <div class="main-content"> -->
<div class="container">
	<div class="row">
		<!-- <div class="visible-lg col-lg-2">
			{!! Theme::partial('home-leftbar',compact('trending_tags')) !!}
		</div> -->
		<div class="col-md-12 col-lg-12">
			<div class="panel panel-default">
				<div class="panel-heading no-bg panel-settings" style="display: flex; justify-content: space-between; flex-wrap: wrap;">
					<h3 class="panel-title">
						{{ trans('common.saved_items') }}
					</h3>
                    <a href="{{ route('purchased-posts') }}" class="btn btn-success purchased-posts">Show Purchased</a>
				</div>
				<div class="panel-body nopadding">
					<ul class="nav nav-pills heading-list">
						<li class="active"><a href="#posts" data-toggle="pill" class="text">{{ trans('common.posts') }}<span></span></a></li>
						<!-- <li class="divider">&nbsp;</li> -->
					</ul>
				</div>
				<div class="tab-content nopadding" style="margin-top:15px;">
					<div id="posts" class="tab-pane fade active in">
						<ul class="list-group page-likes">
							@include('flash::message')
							@if(count($posts) > 0)
								@foreach($posts as $post)
				                    {!! Theme::partial('post',compact('post','user')) !!}
				                @endforeach
							@else
				                <div class="alert alert-warning tmargin-10">{{ trans('messages.no_saved_posts') }}</div>
				            @endif       
						</ul>
					</div>
					<div id="pages" class="tab-pane fade">
						<ul class="list-group page-likes">
							@include('flash::message')
							@if(count($page_timelines) > 0)
								@foreach($page_timelines as $timeline)
				                    <li class="list-group-item holder">
										<div class="connect-list">
											<div class="connect-link side-left">
					                            <a href="{{ url($timeline->username) }}">
					                            	<img src=" @if($timeline->avatar_id) {{ url('page/avatar/'.$timeline->avatar->source) }} @else {{ url('page/avatar/default-page-avatar.png') }} @endif" alt="{{ $timeline->name }}" title="{{ $timeline->name }}" alt="{{ $timeline->name }}" class="img-icon">
					                            	{{ $timeline->name }}
					                            </a>
				                            </div>
				                            <div class="side-right follow-links">
				                            	<div class="left-col">
				                            		<div class="left-col">
				                            			<a href="#" class="btn btn-to-follow btn-default unsave-timeline follow" data-timeline-id="{{ $timeline->id }}"><i class="fa fa-save"></i> {{ trans('common.unsave') }} </a>
				                            		</div>
				                            	</div>
				                            </div>
				                            <div class="clearfix"></div>
				                        </div>
				                    </li>
				                @endforeach
							@else
				                <div class="alert alert-warning">{{ trans('messages.no_saved_pages') }}</div>
				            @endif       
						</ul>
					</div>
					<div id="groups" class="tab-pane fade">
						<ul class="list-group page-likes">
							@include('flash::message')
							@if(count($group_timelines) > 0)
								@foreach($group_timelines as $timeline)
				                    <li class="list-group-item holder">
										<div class="connect-list">
											<div class="connect-link side-left">
					                            <a href="{{ url($timeline->username) }}">
					                            	<img src=" @if($timeline->avatar_id) {{ url('page/avatar/'.$timeline->avatar->source) }} @else {{ url('page/avatar/default-page-avatar.png') }} @endif" alt="{{ $timeline->name }}" title="{{ $timeline->name }}" alt="{{ $timeline->name }}" class="img-icon">
					                            	{{ $timeline->name }}
					                            </a>
				                            </div>
				                            <div class="side-right follow-links">
				                            	<div class="left-col">
				                            		<div class="left-col">
				                            			<a href="#" class="btn btn-to-follow btn-default unsave-timeline follow" data-timeline-id="{{ $timeline->id }}"><i class="fa fa-save"></i> {{ trans('common.unsave') }} </a>
				                            		</div>
				                            	</div>
				                            </div>
				                            <div class="clearfix"></div>
				                        </div>
				                    </li>
				                @endforeach
							@else
				                <div class="alert alert-warning">{{ trans('messages.no_saved_groups') }}</div>
				            @endif       
						</ul>
					</div>
					<div id="events" class="tab-pane fade">
						<ul class="list-group page-likes">
							@include('flash::message')
							@if(count($event_timelines) > 0)
								@foreach($event_timelines as $timeline)
				                    <li class="list-group-item holder">
										<div class="connect-list">
											<div class="connect-link side-left">
					                            <a href="{{ url($timeline->username) }}">
					                            	<img src=" @if($timeline->avatar_id) {{ url('page/avatar/'.$timeline->avatar->source) }} @else {{ url('page/avatar/default-page-avatar.png') }} @endif" alt="{{ $timeline->name }}" title="{{ $timeline->name }}" alt="{{ $timeline->name }}" class="img-icon">
					                            	{{ $timeline->name }}
					                            </a>
				                            </div>
				                            <div class="side-right follow-links">
				                            	<div class="left-col">
				                            		<div class="left-col">
				                            			<a href="#" class="btn btn-to-follow btn-default unsave-timeline follow" data-timeline-id="{{ $timeline->id }}"><i class="fa fa-save"></i> {{ trans('common.unsave') }} </a>
				                            		</div>
				                            	</div>
				                            </div>
				                            <div class="clearfix"></div>
				                        </div>
				                    </li>
				                @endforeach
							@else
				                <div class="alert alert-warning">{{ trans('messages.no_saved_posts') }}</div>
				            @endif       
						</ul>
					</div>
				</div>
			</div>
		</div>

		<!-- <div class="col-md-5 col-lg-4">
			{{-- {!! Theme::partial('home-rightbar',compact('suggested_users', 'suggested_groups', 'suggested_pages')) !!} --}}
		</div> -->

		</div>
	</div>
<!-- </div> --><!-- /main-content -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css" />
<script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js"></script>
