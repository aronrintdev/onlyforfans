<template>
  <section class="major-nav">

    <hr />

    <h6>
      <router-link :to="{ name: 'user-management', params: {} }">User Management</router-link>
    </h6>
    <b-nav vertical class="">
      <b-nav-item>Users</b-nav-item>
      <b-nav-item>User Settings</b-nav-item>
      <b-nav-item>User Moderation (Blocks, Restrictions, Reports)</b-nav-item>
      <b-nav-item>Identity Verification</b-nav-item>
      <b-nav-item>Roles</b-nav-item>
      <b-nav-item>Permissions</b-nav-item>
      <b-nav-item>Invites</b-nav-item>
      <b-nav-item>Notifications</b-nav-item>
      <b-nav-item>Ranking (weights for suggested, explore, etc)</b-nav-item>
    </b-nav>

    <h6>
      <router-link :to="{ name: 'txn-management', params: {} }">Financial</router-link>
    </h6>
    <b-nav vertical class="">
      <b-nav-item><router-link :to="{ name: 'txn-management', params: {} }">Txn</router-link></b-nav-item>
      <b-nav-item>tbd</b-nav-item>
    </b-nav>


    <h6>Messages</h6>
    <b-nav vertical class="">
      <b-nav-item>Chat Messages</b-nav-item>
      <b-nav-item>Contacts</b-nav-item>
    </b-nav>

    <h6>Subscriptions</h6>
    <b-nav vertical class="">
      <b-nav-item>Subscriptions</b-nav-item>
      <b-nav-item>Campaigns</b-nav-item>
    </b-nav>

    <h6>Feeds</h6>
    <b-nav vertical class="">
      <b-nav-item>Timelines</b-nav-item>
      <b-nav-item>Posts</b-nav-item>
      <b-nav-item>Comments</b-nav-item>
    </b-nav>

    <h6>Stories</h6>
    <b-nav vertical class="">
      <b-nav-item>Stories</b-nav-item>
    </b-nav>

    <h6>Media</h6>
    <b-nav vertical class="">
      <b-nav-item>Vaults</b-nav-item>
      <b-nav-item>Files</b-nav-item>
      <b-nav-item>Share Logs</b-nav-item>
    </b-nav>

    <h6>Content Activity (?)</h6>
    <b-nav vertical class="">
      <b-nav-item>Tips</b-nav-item>
      <b-nav-item>Likes</b-nav-item>
      <b-nav-item>Favorites</b-nav-item>
    </b-nav>


    <h6>Site Management</h6>
    <b-nav vertical class="">
      <b-nav-item>General Settings</b-nav-item>
      <b-nav-item>Sessions</b-nav-item>
      <b-nav-item>Jobs/Queues</b-nav-item>
      <!--
      <b-nav-item>US States</b-nav-item>
      <b-nav-item>Countries</b-nav-item>
      -->
      <b-nav-item>Database (migrations, etc)</b-nav-item>
    </b-nav>

    <h6>Support</h6>
    <b-nav vertical class="">
      <b-nav-item>tbd</b-nav-item>
      <b-nav-item>tbd</b-nav-item>
    </b-nav>

    <h6>Analytics</h6>
    <b-nav vertical class="">
      <b-nav-item>tbd</b-nav-item>
      <b-nav-item>tbd</b-nav-item>
    </b-nav>

    <h6>???</h6>
    <b-nav vertical class="">
      <b-nav-item>Links</b-nav-item>
      <b-nav-item>Locations</b-nav-item>
    </b-nav>

  </section>
</template>

<script>
export default {
}
</script>

<style lang="scss" scoped>
  .major-nav {
    hr {
      border-top: solid 1px rgba(255, 255, 255, 0.5);
    }
    ul.nav {
      margin-bottom: 1rem;
    }
    h6 { 
      color: #fff;
      text-decoration: none;
    }
    h6 > a { 
      color: #fff;
      margin-bottom: 0.3rem;
      text-decoration: none;
    }
    .nav-link {
      font-size: 0.8rem;
      padding: 0.3rem 1rem;
      color: #a5a5a5;
    }
    .nav-link > a {
      color: #a5a5a5;
      text-decoration: none;
    }
    .nav-link > a.router-link-active {
      color: var(--primary); // #0056b3;
    }
  }
</style>
