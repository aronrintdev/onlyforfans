<?php

namespace App\Http\Controllers;

// use Guzzle\Service\Client;
use App\Announcement;
use App\Post;
use App\Setting;
use App\Timeline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Teepluss\Theme\Facades\Theme;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->checkCensored();

        $this->middleware('auth');
        
    }

    protected function checkCensored()
    {
        $messages['not_contains'] = 'The :attribute must not contain banned words';
        if($this->request->method() == 'POST') {
            // Adjust the rules as needed
            $this->validate($this->request, 
                [
                  'name'          => 'not_contains',
                  'about'         => 'not_contains',
                  'title'         => 'not_contains',
                  'description'   => 'not_contains',
                  'tag'           => 'not_contains',
                  'email'         => 'not_contains',
                  'body'          => 'not_contains',
                  'link'          => 'not_contains',
                  'address'       => 'not_contains',
                  'website'       => 'not_contains',
                  'display_name'  => 'not_contains',
                  'key'           => 'not_contains',
                  'value'         => 'not_contains',
                  'subject'       => 'not_contains',
                  'username'      => 'not_contains',
                  'email'         => 'email',
                ],$messages);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $mode = "showfeed";
        $user_post = 'showfeed';
        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('default');

        $timeline = Timeline::where('username', Auth::user()->username)->first();

        $id = Auth::id();

        $followingIds = filterByBlockedFollowings();

        $trending_tags = trendingTags();
        $suggested_users = suggestedUsers();
        $suggested_groups = suggestedGroups();
        $suggested_pages = suggestedPages();

        // Check for hashtag
        if ($request->hashtag) {
            $hashtag = '#'.$request->hashtag;

            $posts = Post::where('description', 'like', "%{$hashtag}%")->where('active', 1)->whereIn('timeline_id', DB::table('followers')->where('follower_id', $id)->whereIn('leader_id', $followingIds)->pluck('leader_id'))->latest()->paginate(Setting::get('items_page'));
        } // else show the normal feed
        else {
            $posts = Post::whereIn('user_id', function ($query) use ($id, $followingIds) {
                $query->select('leader_id')
                    ->from('followers')
                    ->where('follower_id', $id)
                    ->whereIn('leader_id', $followingIds);
//            })->orWhere('user_id', $id)->where('active', 1)->limit(10);
            })->orWhereIn('id', function ($query1) use ($id) {
                $query1->select('post_id')
                    ->from('pinned_posts')
                    ->where('user_id', $id)
                    ->where('active', 1);
            })->orWhere('user_id', $id)->orWhere('timeline_id', $timeline->id)->where('active', 1)->latest()->paginate(Setting::get('items_page'));
        }

        if ($request->ajax) {
            $layout = 'post';
            if ($request->get('column')) {
                $layout = 'post_condensed_column';
            }
            $responseHtml = '';
            foreach ($posts as $post) {
                $responseHtml .= $theme->partial($layout, ['post' => $post, 'timeline' => $timeline, 'next_page_url' => $posts->appends(['ajax' => true, 'hashtag' => $request->hashtag])->nextPageUrl()]);
            }

            return $responseHtml;
        }

        $announcement = Announcement::find(Setting::get('announcement'));
        if ($announcement != null) {
            $chk_isExpire = $announcement->chkAnnouncementExpire($announcement->id);

            if ($chk_isExpire == 'notexpired') {
                $active_announcement = $announcement;
                if (!$announcement->users->contains(Auth::user()->id)) {
                    $announcement->users()->attach(Auth::user()->id);
                }
            }
        }

        $next_page_url = url('ajax/get-more-feed?page=2&ajax=true&hashtag='.$request->hashtag.'&username='.Auth::user()->username);
        $theme->setTitle($timeline->name.' '.Setting::get('title_seperator').' '.Setting::get('site_title').' '.Setting::get('title_seperator').' '.Setting::get('site_tagline'));
//        try{
//            echo $comment->user->avatar;
//        }catch(\Exception $e){
//            echo $e;
//        }

//        echo($posts);

        return $theme->scope('home', compact('timeline', 'posts', 'next_page_url', 'trending_tags', 'suggested_users', 'announcement', 'suggested_groups', 'suggested_pages', 'mode', 'user_post'))
            ->render();
    }

    public function getLocation(Request $request)
    {
        $location = str_replace(' ', '+', $request->location);

        $map_url = 'http://www.google.com/maps/place/'.$location;

        return redirect($map_url);
    }
}
