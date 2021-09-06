<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

use App\Http\Resources\ContenttagCollection;
use App\Http\Resources\Contenttag as ContenttagResource;
use App\Models\User;
use App\Models\Contenttag;
use App\Enums\ContenttagAccessLevelEnum;
use App\Http\Resources\MediafileCollection;
use App\Http\Resources\PostCollection;

class ContenttagsController extends AppBaseController
{
    public function index(Request $request)
    {
        $filters = [];
        $sessionUser = $request->user();

        $query = Contenttag::query(); // Init query
        $query->withCount('mediafiles')
              ->withCount('vaultfolders')
              ->withCount('posts');

        // Apply filters
        foreach ($filters as $key => $v) {
            switch ($key) {
                default:
            }
        }

        switch ($request->sortBy) {
        case 'ctag':
        case 'created_at':
        case 'updated_at':
        case 'posts_count':
        case 'mediafiles_count':
        case 'vaultfolders_count':
            $sortDir = $request->sortDir==='asc' ? 'asc' : 'desc';
            $query->orderBy($request->sortBy, $sortDir);
            break;
        default:
            $query->orderBy('updated_at', 'desc');
        }

//dd( $query->get()->toArray() );
        $data = $query->paginate( $request->input('take', Config::get('collections.defaultMax', 10)) );
        return new ContenttagCollection($data);
    }

    public function show($tagId)
    {
        $this->authorize('view', $tagId);
        $contenttag = Contenttag::where('id', $tagId)->first();

        $posts = $contenttag->posts()->with(['user', 'mediafiles', 'timeline'])->withCount(['comments', 'likes'])->get();
        $mediafiles = $contenttag->mediafiles()->with('contenttags')->get();

        return [
            'id' => $contenttag->id,
            'name' => $contenttag->ctag,
            'mediafiles' => $mediafiles,
            'posts' => new PostCollection($posts),
        ];
    }

}
