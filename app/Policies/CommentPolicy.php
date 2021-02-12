<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use App\Policies\Traits\OwnablePolicies;

class CommentPolicy extends BasePolicy
{
    use OwnablePolicies;

    protected $policies = [
        'viewAny'     => 'permissionOnly',
        'view'        => 'isOwner:pass isBlockedByOwner:fail',
        'update'      => 'isOwner:pass',
        'delete'      => 'isOwner:pass',
        'restore'     => 'isOwner:pass',
        'forceDelete' => 'isOwner:pass',
        'like'        => 'isOwner:pass',
    ];

    protected function index(User $user)
    {
        if ( $user->isAdmin() ) {
            return true;
        }
        if ( request()->has('user_id')  && $user->id===request()->user_id ) {
            return true;
        }
        return false;
    }

    protected function view(User $user, Comment $comment)
    {
        dd('here.v');
        dd($user, $comment);
        return $user->can('view', $comment->post); // %FIXME: this should be tested, was throwing 500
    }

    protected function like(User $user, Comment $comment)
    {
        return $comment->post->timeline->followers->contains($user->id);
    }

}
// show OG
/*
        $sessionUser = $request->user();
        //dd('here', $sessionUser->id);
        if ( !$sessionUser->isAdmin() ) { // admin can view all comments
            $isCommentOwner = ($sessionUser->id === $comment->user_id ); // can see own comments
            $isPostOwner = ($sessionUser->id === $comment->post->user_id ); // can see comments on own post
            $isFollowedTimeline = $sessionUser->followedtimelines->contains($comment->post->timeline_id); // can see comments on followed timeline's posts
            //dd( 'co: '.($isCommentOwner?'T':'F'), 'po: '.($isPostOwner?'T':'F'), 'ft: '.($isFollowedTimeline?'T':'F') );
            if ( !$isCommentOwner && !$isPostOwner && !$isFollowedTimeline ) {
                //dd('abort', 'co: '.($isCommentOwner?'T':'F'), 'po: '.($isPostOwner?'T':'F'), 'ft: '.($isFollowedTimeline?'T':'F') );
                abort(403);
            }
        }
 */

// index OG
/*
        if ( !$sessionUser->isAdmin() && !$request->hasAny('post_id', 'user_id') ) { // must have user or post id
            abort(403);
        }
        if ( !$sessionUser->isAdmin() && !$request->has('post_id') && $request->has('user_id') ) {
            if ( $sessionUser->id != $request->user_id ) { // must have user_id equal to session user 
                abort(403);
            }
        }
        // must have post_id and post must be accessible %TODO
*/
