<div class="panel panel-default">
  <div class="panel-heading no-bg panel-settings">  
    <h3 class="panel-title">
      {{ trans('common.allnotifications') }} 
      @if(count($notifications) > 0)
        <span class="side-right">
          <a href="{{ url('allnotifications/delete') }}" class="btn btn-danger text-white allnotifications-delete">{{ trans('common.delete_all') }}</a>
        </span>
      @endif
    </h3>
  </div>

    <div class="panel-body timeline">

        <div class="tab">
            <button class="tablinks active" onclick="openCity(event, 'All')">
                <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-check-all" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" d="M8.97 4.97a.75.75 0 0 1 1.071 1.05l-3.992 4.99a.75.75 0 0 1-1.08.02L2.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093L8.95 4.992a.252.252 0 0 1 .02-.022zm-.92 5.14l.92.92a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 1 0-1.091-1.028L9.477 9.417l-.485-.486-.943 1.179z"/>
                </svg> <span style="line-height: 1em;margin-left: 5px;">All</span>
            </button>
            <button class="tablinks" onclick="openCity(event, 'Liked')">
                <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-suit-heart-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                  <path d="M4 1c2.21 0 4 1.755 4 3.92C8 2.755 9.79 1 12 1s4 1.755 4 3.92c0 3.263-3.234 4.414-7.608 9.608a.513.513 0 0 1-.784 0C3.234 9.334 0 8.183 0 4.92 0 2.755 1.79 1 4 1z"/>
                </svg> <span style="line-height: 1em;margin-left: 5px;">Liked</span>
            </button>
            <button class="tablinks" onclick="openCity(event, 'Subscribed')">
                <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-person-plus-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm7.5-3a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5z"/>
                </svg> <span style="line-height: 1em;margin-left: 5px;">Subscribed</span>
            </button>
        </div>

        <div id="All" class="tabcontent">
            <h3>All</h3>
            <div class="table-responsive">
                <table class="table apps-table fans">
                    @if(count($notifications) > 0)
                        <thead>
                        <th></th>
                        <th>{{ trans('common.notification') }}</th>
                        <th>{{ trans('admin.action') }}</th>
                        </thead>
                        <tbody>
                        @foreach($notifications as $notification)
                            <tr>
                                <td><a href="{{ url('/'.$notification->notified_from->timeline->username) }}">
                                        <img src="{{ $notification->notified_from->avatar }}" alt="{{$notification->notified_from->username}}" title="{{$notification->notified_from->name}}"></a><a href="{{ url($notification->notified_from->username) }}"></a>
                                </td>
                                <td>{{ str_limit($notification->description,50) }}</td>
                                <td><a href="#" data-notification-id="{{ $notification->id }}" class="notification-delete"><span class="trash-icon bg-danger">
                                        <svg data-toggle="tooltip" title="Delete" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-trash" xmlns="http://www.w3.org/2000/svg" fill="#fff" style="margin-top: 5px;">
                                          <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"></path>
                                          <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4L4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"></path>
                                        </svg></span></a>
                            </tr>
                        @endforeach
                        </tbody>
                    @else
                        <div class="alert alert-warning">{{ trans('messages.no_notifications') }}</div>
                        @include('flash::message')
                    @endif
                </table>
                <div class="pagination-holder">
                    {{ $notifications->render() }}
                </div>
            </div>
        </div>

        <div id="Liked" class="tabcontent">
            <h3>Liked</h3>
            <div class="table-responsive">
                <table class="table apps-table fans">
                    @if(count($notifications) > 0)
                        <thead>
                        <th></th>
                        <th>{{ trans('common.notification') }}</th>
                        <th>{{ trans('admin.action') }}</th>
                        </thead>
                        <tbody>
                        @foreach($notifications as $notification)
                            @if ($notification->type == "like_post" || $notification->type == "unlike_post")
                                <tr>
                                    <td><a href="{{ url('/'.$notification->notified_from->timeline->username) }}">
                                            <img src="{{ $notification->notified_from->avatar }}" alt="{{$notification->notified_from->username}}" title="{{$notification->notified_from->name}}"></a><a href="{{ url($notification->notified_from->username) }}"></a>
                                    </td>
                                    <td>{{ str_limit($notification->description,50) }}</td>
                                    <td><a href="#" data-notification-id="{{ $notification->id }}" class="notification-delete"><span class="trash-icon bg-danger">
                                        <svg data-toggle="tooltip" title="Delete" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-trash" xmlns="http://www.w3.org/2000/svg" fill="#fff" style="margin-top: 5px;">
                                          <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"></path>
                                          <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4L4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"></path>
                                        </svg></span></a>
                                </tr>
                            @endif
                        @endforeach
                        </tbody>
                    @else
                        <div class="alert alert-warning">{{ trans('messages.no_notifications') }}</div>
                        @include('flash::message')
                    @endif
                </table>
                <div class="pagination-holder">
                    {{ $notifications->render() }}
                </div>
            </div>
        </div>

        <div id="Subscribed" class="tabcontent">
            <h3>Subscribed</h3>
            <div class="table-responsive">
                <table class="table apps-table fans">
                    @if(count($notifications) > 0)
                        <thead>
                        <th></th>
                        <th>{{ trans('common.notification') }}</th>
                        <th>{{ trans('admin.action') }}</th>
                        </thead>
                        <tbody>
                        @foreach($notifications as $notification)
                            @if ($notification->type == "follow" || $notification->type == "unfollow")
                                <tr>
                                    <td><a href="{{ url('/'.$notification->notified_from->timeline->username) }}">
                                            <img src="{{ $notification->notified_from->avatar }}" alt="{{$notification->notified_from->username}}" title="{{$notification->notified_from->name}}"></a><a href="{{ url($notification->notified_from->username) }}"></a>
                                    </td>
                                    <td>{{ str_limit($notification->description,50) }}</td>
                                    <td><a href="#" data-notification-id="{{ $notification->id }}" class="notification-delete"><span class="trash-icon bg-danger">
                                        <svg data-toggle="tooltip" title="Delete" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-trash" xmlns="http://www.w3.org/2000/svg" fill="#fff" style="margin-top: 5px;">
                                          <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"></path>
                                          <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4L4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"></path>
                                        </svg></span></a>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                        </tbody>
                    @else
                        <div class="alert alert-warning">{{ trans('messages.no_notifications') }}</div>
                        @include('flash::message')
                    @endif
                </table>
                <div class="pagination-holder">
                    {{ $notifications->render() }}
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
</script>