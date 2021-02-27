<template>
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-2 visible-lg">

      </div>
      <div class="col-md-12 col-lg-12">
        <div class="messages-page" id="messages-page">
          <div class="card">
            <div class="card-header no-bg user-pages">
              <div class="page-heading header-text">
                Messages
              </div>
              <div class="user-info-bk">
                <a href="#" class="btn btn-success pull-right">
                  Create message
                </a>
              </div>
            </div>
            <div class="card-body nopadding">
              <div class="row message-box">
                <div class="col-md-4 col-sm-4 col-xs-4 message-col-4">
                  <div class="user-search-bar">
                    <ul class="list-inline no-margin">
                      <li class="dropdown">
                        <a href="#" class="dropdown-togle" data-toggle="dropdown" role="button" aria-haspopup="true"
                          aria-expanded="false">
                          <svg class="sort-icon has-tooltip" aria-hidden="true" data-original-title="null">
                            <use xlink:href="#icon-sort" href="#icon-sort">
                              <svg id="icon-sort" viewBox="0 0 24 24">
                                <path
                                  d="M4 19h4a1 1 0 0 0 1-1 1 1 0 0 0-1-1H4a1 1 0 0 0-1 1 1 1 0 0 0 1 1zM3 6a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1 1 1 0 0 0-1-1H4a1 1 0 0 0-1 1zm1 7h10a1 1 0 0 0 1-1 1 1 0 0 0-1-1H4a1 1 0 0 0-1 1 1 1 0 0 0 1 1z">
                                </path>
                              </svg>
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
                                <input id="nameOrUsername" type="radio" name="filter_type" checked="">
                                <label for="nameOrUsername">Search by Name or Username</label>
                              </div>
                            </a>
                            <a href="#">
                              <div>
                                <input id="favouriteUser" type="radio" name="filter_type">
                                <label for="favouriteUser">Favorite Users</label>
                              </div>
                            </a>
                          </li>
                        </ul>
                      </li>
                    </ul>
                    <div class="input-group">
                      <input type="text" class="form-control" id="searchUserInput" placeholder="Search">
                      <span class="input-group-btn">
                        <button class="btn btn-default" type="button"><i class="fa fa-search"></i></button>
                      </span>
                    </div><!-- /input-group -->
                  </div>
                  <ul class="list-unstyled coversations-list scrollable" data-type="threads">

                  </ul>


                </div>

                <div class="col-md-8 col-sm-8 col-xs-8 message-col-8">
                  <div class="coversation-tree">
                    <div class="conversation">
                      <div class="left-side">



                        <span class="chat-status hidden"></span>

                      </div>
                      <div class="right-side">
                      </div>

                      <div class="clearfix"></div>
                    </div>

                    <ul class="list-unstyled coversations-thread ">


                    </ul>
                  </div>

                  <div class="input-group new-message">

                    <input class="form-control post-message" autocomplete="off" name="message">

                    <span class="input-group-btn">
                      <button class="btn btn-primary" type="button"><i class="fa fa-paper-plane"
                          aria-hidden="true"></i></button>
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
</template>

<script>
/**
 * Messages Dashboard View
 */
export default {
  //
  mounted() {
      console.log('------ rendered');
      this.axios.get('/chat-messages').then((response) => {
        console.log('-- response data', response);
      })
  }
}
</script>

<style lang="scss" scoped>
  @import "../../../sass/views/live-chat.scss";
</style>
