<div class="panel panel-default">
  <div class="panel-heading no-bg panel-settings bottom-border">
    <h3 class="panel-title">
      {{ trans('common.lists') }}
    </h3>
  </div>
    <div class="lists-dropdown-menu">
        <ul class="list-inline text-right no-margin">
            <li class="dropdown">
                <a href="#" class="dropdown-togle lists-dropdown-icon" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                    <svg style="margin-right:15px;" width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-filter-circle" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                      <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                      <path fill-rule="evenodd" d="M7 11.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5z"/>
                    </svg>
                </a>
                <ul class="dropdown-menu profile-dropdown-menu-content">
                    <li class="main-link">
                        <div class="form-check">
                            <input class="red-checkbox" type="radio" name="sort-lists" id="sortByName" value="name" checked>
                            <label class="red-list-label" for="sortByName">
                               Name
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="red-checkbox" type="radio" name="sort-lists" id="sortByRecent" value="recent">
                            <label class="red-list-label" for="sortByRecent">
                                Recent
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="red-checkbox" type="radio" name="sort-lists" id="sortByPeople" value="people">
                            <label class="red-list-label" for="sortByPeople">
                                People
                            </label>
                        </div>
                    </li>
                    <hr>
                    <li class="main-link">
                        <div class="form-check">
                            <input class="red-checkbox" type="radio" name="order-lists" id="orderByASC" value="asc" checked>
                            <label class="red-list-label" for="orderByASC">
                                Ascending
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="red-checkbox" type="radio" name="order-lists" id="orderByDESC" value="desc">
                            <label class="red-list-label" for="orderByDESC">
                                Descending
                            </label>
                        </div>
                    </li>
                </ul>
            </li>
        </ul>
    </div>

    <div class="panel-body timeline my-lists">
        @if (!empty($user_lists))
            @foreach ($user_lists as $user_list)
                <a href="{{ url('mylist').'/'.$user_list['id'] }}">
                    <div class="modal-mylist-item">
                        <span class="red-mylist-label">{{$user_list['name']}}</span>
                        <span class="red-mylist-count-label">{{$user_list['count']}}</span>
                    </div>
                </a>
            @endforeach
        @endif

    </div>
</div>
