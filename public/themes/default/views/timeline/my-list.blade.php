<style>
    .total-spent, .total-tipped, .subscribed-over, .inactive-over {
        position: relative;
    }

    .filter-input {
        border: 0;
        outline: none;
        text-align: center;
        font-weight: bold;
        color: #fff;
    }

    #subscriberFilterModal ul li span {
        color: #298ad3;
        position: absolute;
        height: 25px;
        border-radius: 50%;
        width: 25px;
        /*top: 0;*/
        vertical-align: middle;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        user-select: none;
        transition: .3s;
    }
    
    .text-wrapper {
        top: 0;
        left: 50%;
        position: absolute;
        transform: translateX(-50%);
        bottom: 0;
        display: block;
        max-width: 100%;
    }

    #subscriberFilterModal ul li span:hover {
        background: #e7f3ff;
    }

    #subscriberFilterModal ul li span:active {
        background: #9dd5ff;
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
    }
    
    #subscriberFilterModal ul {
        list-style: none;
        padding: 0 25px;
    }

    @media screen and (max-width: 576px) {
        #subscriberFilterModal ul {
            padding: 0;
        }

        .filter-input {
            width: 130px;
        }
    }

    #subscriberFilterModal ul li {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
    }

    #subscriberFilterModal ul label {
        /* vertical-align: middle; */
        font-size: 14px;
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
											<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-check-all" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                              <path fill-rule="evenodd" d="M8.97 4.97a.75.75 0 0 1 1.071 1.05l-3.992 4.99a.75.75 0 0 1-1.08.02L2.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093L8.95 4.992a.252.252 0 0 1 .02-.022zm-.92 5.14l.92.92a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 1 0-1.091-1.028L9.477 9.417l-.485-.486-.943 1.179z"/>
                                            </svg> <span style="line-height: 1em;margin-left: 5px;">All</span>
										</button>
										<button class="tablinks" onclick="openCity(event, 'Active')">
											<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-calendar2-check-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                              <path fill-rule="evenodd" d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 3.5c0-.276.244-.5.545-.5h10.91c.3 0 .545.224.545.5v1c0 .276-.244.5-.546.5H2.545C2.245 5 2 4.776 2 4.5v-1zm8.854 5.354a.5.5 0 0 0-.708-.708L7.5 10.793 6.354 9.646a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0l3-3z"/>
                                            </svg> <span style="line-height: 1em;margin-left: 5px;">Active</span>
										</button>
										<button class="tablinks" onclick="openCity(event, 'Expired')">
											<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-calendar-x-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                              <path fill-rule="evenodd" d="M4 .5a.5.5 0 0 0-1 0V1H2a2 2 0 0 0-2 2v1h16V3a2 2 0 0 0-2-2h-1V.5a.5.5 0 0 0-1 0V1H4V.5zM16 14V5H0v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2zM6.854 8.146a.5.5 0 1 0-.708.708L7.293 10l-1.147 1.146a.5.5 0 0 0 .708.708L8 10.707l1.146 1.147a.5.5 0 0 0 .708-.708L8.707 10l1.147-1.146a.5.5 0 0 0-.708-.708L8 9.293 6.854 8.146z"/>
                                            </svg> <span style="line-height: 1em;margin-left: 5px;">Expired</span>
										</button>
										<button class="tablinks" onclick="openCity(event, 'Restricted')">
											<svg width="1.0625em" height="1em" viewBox="0 0 17 16" class="bi bi-exclamation-triangle" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                              <path fill-rule="evenodd" d="M7.938 2.016a.146.146 0 0 0-.054.057L1.027 13.74a.176.176 0 0 0-.002.183c.016.03.037.05.054.06.015.01.034.017.066.017h13.713a.12.12 0 0 0 .066-.017.163.163 0 0 0 .055-.06.176.176 0 0 0-.003-.183L8.12 2.073a.146.146 0 0 0-.054-.057A.13.13 0 0 0 8.002 2a.13.13 0 0 0-.064.016zm1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566z"/>
                                              <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995z"/>
                                            </svg> <span style="line-height: 1em;margin-left: 5px;">Restricted</span>
										</button>
										<button class="tablinks" onclick="openCity(event, 'Blocked')">
											<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-dash-circle-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                              <path fill-rule="evenodd" d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.5 7.5a.5.5 0 0 0 0 1h7a.5.5 0 0 0 0-1h-7z"/>
                                            </svg> <span style="line-height: 1em;margin-left: 5px;">Blocked</span>
										</button>
                                        @if ($list_type_id == 'followers')
                                        <a href="#" data-toggle="modal" data-target="#subscriberFilterModal" class="filter-button">
                                            <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-sliders" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                              <path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zM9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3h9.05zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zM2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8h2.05zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zm-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1h9.05z"/>
                                            </svg>
                                        </a>
                                        @endif
                                        <div class="modal fade" id="subscriberFilterModal" role="dialog">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h3 class="modal-title">Filter Subscribers</h3>
                                                    </div>
                                                    <div class="modal-body">
                                                        <ul>
                                                            <li>
                                                                <div>
                                                                    <input type="radio" id="totalSpent" name="subscriber_filter">
                                                                    <label for="totalSpent">Total Spent</label>
                                                                </div>
                                                                <div class="total-spent">
                                                                    <span class="decrement"><i class="fa fa-minus" aria-hidden="true"></i></span>
                                                                    <span class="increment"><i class="fa fa-plus" aria-hidden="true"></i></span>
                                                                    <input min="0" value="100" data-id="1" name="total_spent" class="filter-input total-spent-input">
                                                                    <div class="text-wrapper"></div>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div>
                                                                    <input type="radio" id="tippedOver" name="subscriber_filter">
                                                                    <label for="tippedOver">Tipped over</label>
                                                                </div>
                                                                <div class="total-tipped">
                                                                    <span class="decrement"><i class="fa fa-minus" aria-hidden="true"></i></span>
                                                                    <span class="increment"><i class="fa fa-plus" aria-hidden="true"></i></span>
                                                                    <input min="0" value="10" data-id="2"  name="total_tipped" class="filter-input total-tipped-input">
                                                                    <div class="text-wrapper"></div>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div>
                                                                    <input type="radio" id="subscribedOver" name="subscriber_filter">
                                                                    <label for="subscribedOver">Subscribed for over</label>
                                                                </div>
                                                                <div class="subscribed-over">
                                                                    <span class="decrement"><i class="fa fa-minus" aria-hidden="true"></i></span>
                                                                    <span class="increment"><i class="fa fa-plus" aria-hidden="true"></i></span>
                                                                    <input min="0" value="1" data-id="3" name="subscribed_length" class="filter-input subscribed-length-input">
                                                                    <div class="text-wrapper"></div>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div>
                                                                    <input type="radio" id="inactiveOver" name="subscriber_filter">
                                                                    <label for="inactiveOver">Inactive for over</label>    
                                                                </div>
                                                                <div class="inactive-over">
                                                                    <span class="decrement"><i class="fa fa-minus" aria-hidden="true"></i></span>
                                                                    <span class="increment"><i class="fa fa-plus" aria-hidden="true"></i></span>
                                                                    <input min="0" value="1" data-id="4" name="subscriber_inactive_length" class="filter-input subscriber-inactive-input">
                                                                    <div class="text-wrapper"></div>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <a href="#" class="filter-modal-btn" data-dismiss="modal">Cancel</a>
                                                        <!-- <a href="#" class="filter-modal-btn" id="resetFilter">Reset</a> -->
                                                        <a href="#" class="btn filter-modal-btn btn-success" id="applyFilter">Apply</a>
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
											<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-check-all" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                              <path fill-rule="evenodd" d="M8.97 4.97a.75.75 0 0 1 1.071 1.05l-3.992 4.99a.75.75 0 0 1-1.08.02L2.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093L8.95 4.992a.252.252 0 0 1 .02-.022zm-.92 5.14l.92.92a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 1 0-1.091-1.028L9.477 9.417l-.485-.486-.943 1.179z"/>
                                            </svg> <span style="line-height: 1em;margin-left: 5px;">All</span>
										</button>
										<button class="tablinks" onclick="openCity(event, 'Active')">
											<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-calendar2-check-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                              <path fill-rule="evenodd" d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM2 3.5c0-.276.244-.5.545-.5h10.91c.3 0 .545.224.545.5v1c0 .276-.244.5-.546.5H2.545C2.245 5 2 4.776 2 4.5v-1zm8.854 5.354a.5.5 0 0 0-.708-.708L7.5 10.793 6.354 9.646a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0l3-3z"/>
                                            </svg> <span style="line-height: 1em;margin-left: 5px;">Active</span>
										</button>
										<button class="tablinks" onclick="openCity(event, 'Expired')">
											<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-calendar-x-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                              <path fill-rule="evenodd" d="M4 .5a.5.5 0 0 0-1 0V1H2a2 2 0 0 0-2 2v1h16V3a2 2 0 0 0-2-2h-1V.5a.5.5 0 0 0-1 0V1H4V.5zM16 14V5H0v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2zM6.854 8.146a.5.5 0 1 0-.708.708L7.293 10l-1.147 1.146a.5.5 0 0 0 .708.708L8 10.707l1.146 1.147a.5.5 0 0 0 .708-.708L8.707 10l1.147-1.146a.5.5 0 0 0-.708-.708L8 9.293 6.854 8.146z"/>
                                            </svg> <span style="line-height: 1em;margin-left: 5px;">Expired</span>
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
			</div>
			</div>
		</div>
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
            if (input.data('id') == 1) {
                input.val((val - 100) < 0 ? 0 : (val - 100)).trigger('keyup');
            } else if(input.data('id') == 2) {
                input.val((val - 10) < 0 ? 0 : (val - 10)).trigger('keyup');
            } else {
                input.val((val - 1) < 0 ? 0 : (val - 1)).trigger('keyup');
            }
            
        }
    });
    
    $('span.increment').click(function () {
        let input = $(this).siblings('input.filter-input');
        let val = input.val() != '' ? parseFloat(input.val()) : 0;
        if (val != NaN) {
            if (input.data('id') == 1) {
                input.val(val + 100).trigger('keyup');    
            } else if(input.data('id') == 2) {
                input.val(val + 10).trigger('keyup');
            } else if(input.data('id') == 3) {
                input.val((val + 1) > 12 ? val : (val + 1)).trigger('keyup');
            } else if(input.data('id') == 4) {
                input.val((val + 1) > 30 ? val : (val + 1)).trigger('keyup');
            }                
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
    let lastActiveFilter;
    $('#subscriberFilterModal').on('show.bs.modal', function () {
        lastActiveFilter = $('input[type="radio"]:checked');
        $('.filter-input').trigger('keyup');
    });    
    
    $('#subscriberFilterModal').on('show.bs.modal', function () {
        lastActiveFilter.prop('checked', true);
    });    
    
    $(document).on('keyup', '.filter-input', function () {
       let activeFilter = $(this).closest('li').find('input[type="radio"]');
        activeFilter.prop('checked', true);
        let val = $(this).val() != '' ? parseFloat($(this).val()) : 0;
        if($(this).data('id') == 1) {
            $(this).next('.text-wrapper').text($(this).val() + ' USD');
        }else if($(this).data('id') == 2) {
            $(this).val(val > 200 ? 200 : val);
            $(this).next('.text-wrapper').text($(this).val() + ' USD');
        }else if($(this).data('id') == 3) {
            $(this).val(val > 12 ? 12 : val);
            $(this).next('.text-wrapper').text($(this).val() + ' Month');
        } else if($(this).data('id') == 4) {
            $(this).val(val > 30 ? 30 : val);
            $(this).next('.text-wrapper').text($(this).val() + ' Day');
        }
    });
</script>