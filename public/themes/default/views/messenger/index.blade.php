<style>
    .user-search-bar {
        display: flex;
        justify-content: space-between;
        padding-top: 15px;
        padding-bottom: 15px;
        align-items: center;
        border-bottom: 1px solid #DFE3E9 !important
    }

    .messages-page .panel-body .message-col-4 .user-search-bar .input-group {
        width: 80%;
        padding: 0 !important;
        border-bottom: none !important;
        margin-right: 15px;
    }
    
    .user-filter-dropdown label {
        cursor: pointer;
    }
    
    .fav-user {
        position: absolute;
        right: 15px;
        font-size: 18px;
        top: 50%;
        transform: translateY(-50%);
    }
    
    .is-not-fav {
        display: none;
    }
</style>
<!-- <div class="main-content">-->  
<div class="container">

    <div class="row">
        <div class="col-md-2 visible-lg">
            {!! Theme::partial('home-leftbar',compact('trending_tags')) !!}
        </div>
        <div class="col-lg-10 col-md-12">
            <div class="messages-page" id="messages-page" v-cloak>
                <div class="panel panel-default">
                    <div class="panel-heading no-bg user-pages">
                        <div class="page-heading header-text">
                            {{ trans('common.messages') }} 
                        </div>
                        <div class="user-info-bk">
                            <a href="#" class="btn btn-success pull-right" @click.prevent="showNewConversation">
                                {{ trans('common.create_message') }}
                            </a>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="panel-body nopadding">

                        {{-- messagebox --}}
                        <div class="row message-box">
                            <div class="col-md-4 col-sm-4 col-xs-4 message-col-4">
                                <div class="user-search-bar">
                                    <ul class="list-inline no-margin">
                                        <li class="dropdown">
                                            <a href="#" class="dropdown-togle" data-toggle="dropdown"
                                               role="button" aria-haspopup="true"
                                               aria-expanded="false">
                                                <svg class="sort-icon has-tooltip" aria-hidden="true" data-original-title="null">
                                                    <use xlink:href="#icon-sort" href="#icon-sort">
                                                        <svg id="icon-sort" viewBox="0 0 24 24"> <path d="M4 19h4a1 1 0 0 0 1-1 1 1 0 0 0-1-1H4a1 1 0 0 0-1 1 1 1 0 0 0 1 1zM3 6a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1 1 1 0 0 0-1-1H4a1 1 0 0 0-1 1zm1 7h10a1 1 0 0 0 1-1 1 1 0 0 0-1-1H4a1 1 0 0 0-1 1 1 1 0 0 0 1 1z"></path> </svg>
                                                    </use>
                                                </svg>
                                            </a>
                                            <ul class="dropdown-menu user-filter-dropdown">
                                                <li class="main-link">
                                                    <a href="#">
                                                        <div>
                                                            <input id="dateJoined" type="radio" name="filter_type">
                                                            <label for="dateJoined">Search by Date Joined (YYYY-MM-DD)</label>
                                                        </div>
                                                    </a>
                                                    <a href="#">
                                                        <div>
                                                            <input id="location" type="radio" name="filter_type">
                                                            <label for="location">Search by Location</label>
                                                        </div>
                                                    </a>
                                                    <a href="#">
                                                        <div>
                                                            <input id="nameOrUsername" type="radio" name="filter_type" checked>
                                                            <label for="nameOrUsername">Search by Name or Username.</label>
                                                        </div>
                                                    </a>
                                                    <a href="#">
                                                        <div>
                                                            <input id="favouriteUser" type="radio" name="filter_type">
                                                            <label for="favouriteUser">Favourite Users</label>
                                                        </div>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="searchUserInput" v-on:keyup="searchUsers()" placeholder="{{ trans('common.search') }}">
                                        <span class="input-group-btn">
                                        <button class="btn btn-default" type="button"><i class="fa fa-search"></i></button>
                                    </span>
                                    </div><!-- /input-group -->
                                </div>
                                <ul class="list-unstyled coversations-list scrollable" @wait-for="getConversations" data-type="threads">
                                    <li class="message-holder" v-bind:class="[ conversation.unread ? 'unseen-message' : '', (conversation.id==currentConversation.id) ? 'active' : '',  ]" v-for="conversation in conversations.data">
                                        <a href="#" class="show-conversation" @click.prevent="showConversation(conversation)">
                                            <div class="media post-list">
                                                <div class="media-left">
                                                    <img v-bind:src="conversation.user.avatar" alt="images"  class="img-radius img-46">
                                                </div>
                                                <div class="media-body">
                                                   
                                                    <h4 class="media-heading">
                                                        @{{ conversation.user.name }}
                                                        <span class="verified-badge bg-success" v-if="conversation.user.verified">
                                                            <i class="fa fa-check"></i>
                                                        </span>
                                                    </h4>
                                                    <div class="post-text">
                                                        @{{ conversation.lastMessage.body }}
                                                    </div>
                                                </div>
                                                <a href="#" class="fav-user is-fav" title="Add to favourite" v-if="conversation.user.is_favourite" v-on:click="favouriteUser(conversation.user.id)">
                                                    <i class="fa fa-star" aria-hidden="true"></i>
                                                </a>
                                                <a href="#" class="fav-user is-not-fav" title="Add to favourite" v-if="!conversation.user.is_favourite" v-on:click="favouriteUser(conversation.user.id)">
                                                    <i class="fa fa-star-o" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                        </a>
                                    </li>
                                </ul>

                                
                            </div>

                            <div class="col-md-8 col-sm-8 col-xs-8 message-col-8">
                                <div class="coversation-tree">
                                    <div class="conversation">
                                        <div class="left-side">
                                            
                                            @{{ currentConversation.user.name }}

                                             <span class="chat-status hidden"></span>
                                            
                                        </div>
                                        <div class="right-side">
                                        </div>
                                        <div class="new-conversation" v-if="newConversation">
                                            <input type="text" v-model="recipients" name="recipients[]" class="form-control" id="messageReceipient" placeholder="{{ trans('messages.search_people_placeholder') }}">
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>

                                        <ul class="list-unstyled coversations-thread "> 

                                                <li class="message-conversation" v-for="message in currentConversation.conversationMessages.data">
                                                <div class="media post-list">
                                                    <div class="media-left">
                                                        <a href="#">
                                                            <img v-bind:src="message.user.avatar" class="img-radius img-40" alt="">
                                                        </a>
                                                    </div>
                                                    <div class="media-body ">
                                                        <h4 class="media-heading"><a href="#">@{{ message.user.name }}</a><span class="text-muted">
                                                            <time class="microtime" datetime="@{{ message.created_at }}+00.00" title="@{{ message.created_at }}+00.00">
                                                                        @{{ message.created_at }}+00.00
                                                                    </time>
                                                        </span></h4>
                                                         <p class="post-text">
                                                            @{{ message.body }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                </div>

                                <div class="input-group new-message">
                                    
                                        <input class="form-control post-message" autocomplete="off" name="message" v-on:keyup.enter="postNewConversation()" v-model="messageBody" v-if="newConversation">
                                        <input class="form-control post-message" autocomplete="off" name="message" v-on:keyup.enter="postMessage(currentConversation)" v-model="messageBody" v-else>
                                        <span class="input-group-btn">
                                        <button class="btn btn-primary" type="button" v-on:click="postMessage(currentConversation)"><i class="fa fa-paper-plane" aria-hidden="true"></i></button>
                                    </span>
                                </div><!-- /input-group -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- </div> -->
<script>
    $(document).ready(function () {
        $('input[type="radio"]').change(function () {
           vue.searchUsers();            
        });
    })
    
    $(document).on('mouseenter', '.post-list', function () {
        $(this).find('.is-not-fav').show();
    });
    
    $(document).on('mouseleave', '.post-list', function () {
        $(this).find('.is-not-fav').hide();
    });
</script>
{!! Theme::asset()->container('footer')->usePath()->add('messages-js', 'js/messages.js') !!}
