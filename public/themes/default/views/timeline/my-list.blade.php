<style>
    .total-spent, .total-tipped, .subscribed-over, .inactive-over {
        position: relative;
        padding-left: 25px;
    }

    .filter-input {
        border: 0;
        outline: none;
    }

    #subscriberFilterModal ul li span {
        font-size: 24px;
        color: #298ad3;
        position: absolute;
        height: 100%;
        width: 20px;
        top: 0;
        vertical-align: middle;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        user-select: none;
    }

    #subscriberFilterModal ul li span.decrement {        
        left: 0;
    }

    #subscriberFilterModal ul li span.increment {        
        right: 0;
    }
    
    .panel-heading {
        display: flex;
        justify-content: space-between;
    }
    
    input.filter-input::-webkit-outer-spin-button,
    input.filter-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    
    .filter-wrapper > div {
        margin-left: 10px;
    }

    .filter-button {
        float: right;
        margin-top: 10px;
        margin-right: 10px;
        color: #298ad3;
        vertical-align: middle;
        font-size: 21px;
    }
    
    .filter-modal-btn {
        margin-left: 10px;
        font-weight: bold;
    }
    
    #subscriberFilterModal ul {
        list-style: none;
        padding: 0 25px;
    }

    #subscriberFilterModal ul li {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
    }

    #subscriberFilterModal ul label {
        vertical-align: middle;
        font-size: 12px;
        text-transform: uppercase;
        cursor: pointer;
    }

    #resetFilter {
        color: #ccc;
    }
    
    .reset-filter {
        color: #00AEEF !important;
    }
</style>
<!-- main-section -->
	<!-- <div class="main-content"> -->
		<div class="container">
			<div class="row">

                <div class="col-12">
					<div class="timeline-posts">

						<div class="panel panel-default">
							<div class="panel-heading no-bg panel-settings bottom-border">
								<h3 class="panel-title">

									<a href="{{url('/mylists')}}" class="btn-back">
										<svg class="g-icon" aria-hidden="true">
											<use xlink:href="#icon-back" href="#icon-back">
												<svg id="icon-back" viewBox="0 0 24 24"> <path d="M19 11H7.41l5.3-5.29A1 1 0 0 0 13 5a1 1 0 0 0-1-1 1 1 0 0 0-.71.29L3.59 12l7.7 7.71A1 1 0 0 0 12 20a1 1 0 0 0 1-1 1 1 0 0 0-.29-.71L7.41 13H19a1 1 0 0 0 0-2z"></path> </svg>
											</use>
										</svg>
									</a>

									{{ $list_type_name }}
								</h3>
							</div>


							<div class="panel-body timeline">

								@if ($list_type_id == 'followers')
									<div class="tab">
										<button class="tablinks active" onclick="openCity(event, 'All')">
											<svg class="g-icon" aria-hidden="true" style="">
												<use xlink:href="#icon-all" href="#icon-all">
													<svg id="icon-all" viewBox="0 0 24 24"> <path d="M15 6H5a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V9a3 3 0 0 0-3-3zm1 13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1zm3-17H8a1 1 0 0 0 0 2h11a1 1 0 0 1 1 1v11a1 1 0 0 0 2 0V5a3 3 0 0 0-3-3zm-6 9a1 1 0 0 0-.71.29L9 14.59l-1.29-1.3A1 1 0 0 0 7 13a1 1 0 0 0-1 1 1 1 0 0 0 .29.71l2 2a1 1 0 0 0 1.42 0l4-4A1 1 0 0 0 14 12a1 1 0 0 0-1-1z"></path> </svg>
												</use>
											</svg> ALL
										</button>
										<button class="tablinks" onclick="openCity(event, 'Active')">
											<svg class="g-icon" aria-hidden="true" style="">
												<use xlink:href="#icon-active" href="#icon-active">
													<svg id="icon-active" viewBox="0 0 24 24"> <path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8zm4.42-11.42a1 1 0 0 0-.71.3l-5.21 5.21-2.21-2.21a1 1 0 0 0-.71-.3 1 1 0 0 0-1 1 1 1 0 0 0 .3.71l3.62 3.62 6.62-6.62a1 1 0 0 0 .3-.71 1 1 0 0 0-1-1z"></path> </svg>
												</use>
											</svg> ACTIVE
										</button>
										<button class="tablinks" onclick="openCity(event, 'Expired')">
											<svg class="g-icon" aria-hidden="true" style="">
												<use xlink:href="#icon-expired" href="#icon-expired">
													<svg id="icon-expired" viewBox="0 0 24 24"> <path d="M22.56 18.34A2.63 2.63 0 0 0 22.2 17L14.3 3.33a2.65 2.65 0 0 0-4.6 0L1.8 17a2.63 2.63 0 0 0-.36 1.33A2.66 2.66 0 0 0 4.1 21h15.8a2.66 2.66 0 0 0 2.66-2.66zm-2 0a.66.66 0 0 1-.66.66H4.1a.66.66 0 0 1-.66-.66.63.63 0 0 1 .09-.34l7.9-13.68a.68.68 0 0 1 1.14 0L20.47 18a.63.63 0 0 1 .09.34zM12 13.5a1 1 0 0 0 1-1v-4a1 1 0 0 0-2 0v4a1 1 0 0 0 1 1zm0 1.1a1.4 1.4 0 1 0 1.4 1.4 1.4 1.4 0 0 0-1.4-1.4z"></path> </svg>
												</use>
											</svg> EXPIRED
										</button>
										<button class="tablinks" onclick="openCity(event, 'Restricted')">
											<svg class="g-icon" aria-hidden="true" style="">
												<use xlink:href="#icon-restricted" href="#icon-restricted">
													<svg id="icon-restricted" viewBox="0 0 24 24"> <path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zM4 12a8 8 0 0 1 8-8 7.92 7.92 0 0 1 4.9 1.69L5.69 16.9A7.92 7.92 0 0 1 4 12zm8 8a7.92 7.92 0 0 1-4.9-1.69L18.31 7.1A7.92 7.92 0 0 1 20 12a8 8 0 0 1-8 8z"></path> </svg>
												</use>
											</svg> RESTRICTED
										</button>
										<button class="tablinks" onclick="openCity(event, 'Blocked')">
											<svg class="g-icon" aria-hidden="true" style="">
												<use xlink:href="#icon-blocked" href="#icon-blocked">
													<svg id="icon-blocked" viewBox="0 0 24 24"> <path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm4.9 3.69l-4.9 4.9-4.9-4.9a7.95 7.95 0 0 1 9.8 0zM4 12a7.92 7.92 0 0 1 1.69-4.9l4.9 4.9-4.9 4.9A7.92 7.92 0 0 1 4 12zm3.1 6.31l4.9-4.9 4.9 4.9a7.95 7.95 0 0 1-9.8 0zm11.21-1.41l-4.9-4.9 4.9-4.9a7.95 7.95 0 0 1 0 9.8z"></path> </svg>
												</use>
											</svg> BLOCKED
										</button>
                                        @if ($list_type_id == 'followers')
                                        <a href="#" data-toggle="modal" data-target="#subscriberFilterModal" class="filter-button">
                                            <i class="fa fa-sliders" aria-hidden="true"></i>
                                        </a>
                                        @endif
                                        <div class="modal fade" id="subscriberFilterModal" role="dialog">
                                            <div class="modal-dialog">

                                                <!-- Modal content-->
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Filter Subscribers</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <ul>
                                                            <li>
                                                                <div>
                                                                    <input type="radio" id="totalSpent" name="subscriber_filter">
                                                                    <label for="totalSpent">Total Spent (USD)</label>
                                                                </div>
                                                                <div class="total-spent">
                                                                    <span class="decrement">-</span>
                                                                    <span class="increment">+</span>
                                                                    <input type="number" min="0" value="0" name="total_spent" class="filter-input total-spent-input">
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div>
                                                                    <input type="radio" id="tippedOver" name="subscriber_filter">
                                                                    <label for="tippedOver">Tipped over (USD)</label>
                                                                </div>
                                                                <div class="total-tipped">
                                                                    <span class="decrement">-</span>
                                                                    <span class="increment">+</span>
                                                                    <input type="number" min="0" value="0" name="total_tipped" class="filter-input total-tipped-input">
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div>
                                                                    <input type="radio" id="subscribedOver" name="subscriber_filter">
                                                                    <label for="subscribedOver">Subscribed over (Day)</label>
                                                                </div>
                                                                <div class="subscribed-over">
                                                                    <span class="decrement">-</span>
                                                                    <span class="increment">+</span>
                                                                    <input type="number" min="0" value="0" name="subscribed_length" class="filter-input subscribed-length-input">
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div>
                                                                    <input type="radio" id="inactiveOver" name="subscriber_filter">
                                                                    <label for="inactiveOver">Inactive over (Day)</label>    
                                                                </div>
                                                                <div class="inactive-over">
                                                                    <span class="decrement">-</span>
                                                                    <span class="increment">+</span>
                                                                    <input type="number" min="0" value="0" name="subscriber_inactive_length" class="filter-input subscriber-inactive-input">
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <a href="#" class="filter-modal-btn" data-dismiss="modal">Cancel</a>
                                                        <a href="#" class="filter-modal-btn" id="resetFilter">Reset</a>
                                                        <a href="#" class="filter-modal-btn" id="applyFilter">Apply</a>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
									</div>

									<div id="All" class="tabcontent">
										@if (count($saved_users) > 0)
											{!! Theme::partial('my-list',compact('saved_users')) !!}
										@else
											<div class="text-center">
												{{ trans('common.no_users_found') }}
											</div>
										@endif
									</div>
									<div id="Active" class="tabcontent">
										@if (count($saved_users) > 0)
											{!! Theme::partial('my-list',compact('saved_users')) !!}
										@else
											<div class="text-center">
												{{ trans('common.no_users_found') }}
											</div>
										@endif
									</div>
									<div id="Expired" class="tabcontent">
										<div class="text-center">
											{{ trans('common.no_users_found') }}
										</div>
									</div>
									<div id="Restricted" class="tabcontent">
										<div class="text-center">
											{{ trans('common.no_users_found') }}
										</div>
									</div>
									<div id="Blocked" class="tabcontent">
										<div class="text-center">
											{{ trans('common.no_users_found') }}
										</div>
									</div>
								@elseif ($list_type_id == 'following')
									<div class="tab">
										<button class="tablinks active" onclick="openCity(event, 'All')">
											<svg class="g-icon" aria-hidden="true" style="">
												<use xlink:href="#icon-f-all" href="#icon-f-all">
													<svg id="icon-f-all" viewBox="0 0 24 24"> <path d="M15 6H5a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V9a3 3 0 0 0-3-3zm1 13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1zm3-17H8a1 1 0 0 0 0 2h11a1 1 0 0 1 1 1v11a1 1 0 0 0 2 0V5a3 3 0 0 0-3-3zm-6 9a1 1 0 0 0-.71.29L9 14.59l-1.29-1.3A1 1 0 0 0 7 13a1 1 0 0 0-1 1 1 1 0 0 0 .29.71l2 2a1 1 0 0 0 1.42 0l4-4A1 1 0 0 0 14 12a1 1 0 0 0-1-1z"></path> </svg>
												</use>
											</svg> ALL
										</button>
										<button class="tablinks" onclick="openCity(event, 'Active')">
											<svg class="g-icon" aria-hidden="true" style="">
												<use xlink:href="#icon-f-active" href="#icon-f-active">
													<svg id="icon-f-active" viewBox="0 0 24 24"> <path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 8-8 8 8 0 0 1-8 8zm4.42-11.42a1 1 0 0 0-.71.3l-5.21 5.21-2.21-2.21a1 1 0 0 0-.71-.3 1 1 0 0 0-1 1 1 1 0 0 0 .3.71l3.62 3.62 6.62-6.62a1 1 0 0 0 .3-.71 1 1 0 0 0-1-1z"></path> </svg>
												</use>
											</svg> ACTIVE
										</button>
										<button class="tablinks" onclick="openCity(event, 'Expired')">
											<svg class="g-icon" aria-hidden="true" style="">
												<use xlink:href="#icon-f-expired" href="#icon-f-expired">
													<svg id="icon-f-expired" viewBox="0 0 24 24"> <path d="M22.56 18.34A2.63 2.63 0 0 0 22.2 17L14.3 3.33a2.65 2.65 0 0 0-4.6 0L1.8 17a2.63 2.63 0 0 0-.36 1.33A2.66 2.66 0 0 0 4.1 21h15.8a2.66 2.66 0 0 0 2.66-2.66zm-2 0a.66.66 0 0 1-.66.66H4.1a.66.66 0 0 1-.66-.66.63.63 0 0 1 .09-.34l7.9-13.68a.68.68 0 0 1 1.14 0L20.47 18a.63.63 0 0 1 .09.34zM12 13.5a1 1 0 0 0 1-1v-4a1 1 0 0 0-2 0v4a1 1 0 0 0 1 1zm0 1.1a1.4 1.4 0 1 0 1.4 1.4 1.4 1.4 0 0 0-1.4-1.4z"></path> </svg>
												</use>
											</svg> EXPIRED
										</button>
									</div>

									<div id="All" class="tabcontent">
										@if (count($saved_users) > 0)
											{!! Theme::partial('my-list',compact('saved_users')) !!}
										@else
											<div class="text-center">
												{{ trans('common.no_users_found') }}
											</div>
										@endif
									</div>
									<div id="Active" class="tabcontent">
										@if (count($saved_users) > 0)
											{!! Theme::partial('my-list',compact('saved_users')) !!}
										@else
											<div class="text-center">
												{{ trans('common.no_users_found') }}
											</div>
										@endif
									</div>
									<div id="Expired" class="tabcontent">
										<div class="text-center">
											{{ trans('common.no_users_found') }}
										</div>
									</div>
								@else
									@if (count($saved_users) > 0)
										{!! Theme::partial('my-list',compact('saved_users')) !!}
									@else
										<div class="text-center">
											{{ trans('common.no_users_found') }}
										</div>
									@endif
								@endif
							</div>
						</div>
					</div>
				</div>
			</div><!-- /col-md-6 --></div>
		</div>
	<!-- </div> -->
<!-- /main-section -->

<script>

	$("#All").show();

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
    
    $('span.decrement').click(function () {
        let input = $(this).siblings('input.filter-input');
        let val = input.val() != '' ? parseFloat(input.val()) : 0;
        
        if (val != NaN) {
            input.val((val - 1) < 0 ? 0 : (val - 1)).trigger('keyup');
        }
    });
    
    $('span.increment').click(function () {
        let input = $(this).siblings('input.filter-input');
        let val = input.val() != '' ? parseFloat(input.val()) : 0;
        if (val != NaN) {
            input.val(val + 1).trigger('keyup');    
        }        
    });
    
    $(document).on('click', '#applyFilter', function () {
        const modal = $('#subscriberFilterModal');
        let isTotalSpent = modal.find('#totalSpent').is(':checked'); 
        let isTippedOver = modal.find('#tippedOver').is(':checked'); 
        let isSubscribedOver = modal.find('#subscribedOver').is(':checked'); 
        let isInactiveOver = modal.find('#inactiveOver').is(':checked');
        
        let totalTipped = $('.total-tipped-input').val();
        let totalSpent = $('.total-spent-input').val();
        let subscribedLength = $('.subscribed-length-input').val();
        let subscribeInactiveLength = $('.subscriber-inactive-input').val();
        $.ajax({
            url: "{{ url('/mylist/followers') }}",
            type: 'get',
            data: {
                total_tipped: isTippedOver ? totalTipped : 0,
                total_spent: isTotalSpent ? totalSpent : 0,
                subscribed_length: isSubscribedOver ? subscribedLength : 0,
                subscriber_inactive_length: isInactiveOver ? subscribeInactiveLength : 0,
            },
            success: function (result) {
                modal.modal('hide');
                console.log(result.data);
                $('#All').html(result.data)
            },
            error: function (result) {
                console.log(result);
            }
        });
    });
    
    $('input[type="radio"]').change(function () {
        console.log('changes');
        if ($('input[type="radio"]').is(':checked')) {
            $('#resetFilter').addClass('reset-filter');
        } else {
            $('#resetFilter').removeClass('reset-filter');
        }
    });
    
    $(document).on('click', '.reset-filter', function () {
        $('input[type="radio"]').attr('checked', false);
        $('.filter-input').val(0);
    });
</script>
