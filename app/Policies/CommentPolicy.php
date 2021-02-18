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
        'like'        => 'isOwner:pass isBlockedByOwner:fail',
        'comment'     => 'isOwner:pass isBlockedByOwner:fail', // ie a comment reply
        'update'      => 'isOwner:pass:fail',
        'delete'      => 'isOwner:pass',
        'forceDelete' => 'isOwner:pass:fail',
        'restore'     => 'isOwner:pass:fail',
    ];

    protected function view(User $user, Comment $comment)
    {
        return $user->can('view', $comment->post); // %FIXME: this should be tested, was throwing 500
    }

    protected function like(User $user, Comment $comment)
    {
        return $comment->post->timeline->followers->contains($user->id);
    }

    protected function delete(User $user, Comment $comment)
    {
        // post owner can delete any comment on the post
        //return $comment->commentable->isOwner($user);
        return $comment->post->isOwner($user);
    }

    protected function forceDelete(User $user, Comment $comment)
    {
        return $comment->post->isOwner($user);
    }

    protected function isBlockedBy(User $sessionUser, User $user) : bool
    {
        return $sessionUser->$user->isBlockedBy($user);
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
