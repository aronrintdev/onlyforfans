<?php
namespace App\Http\Resources;
use App\Models\Diskmediafile as DiskmediafileModel;

use Illuminate\Http\Resources\Json\JsonResource;

class Diskmediafile extends JsonResource
{
    public function toArray($request)
    {
        $sessionUser = $request->user();
        $model = DiskmediafileModel::find($this->id); // %FIXME: n+1 performance issue (not so bad if paginated?)
        $hasAccess = $sessionUser->can('view', $model);

        return [
            'access' =>  $hasAccess,
            'id' => $this->id,
            'slug' => $this->slug,
            'owner_id' => $this->owner_id,
            'filepath' => $this->filepath,
            'mimetype' => $this->mimetype,
            'orig_ext' => $this->orig_ext,
            'orig_filename' => $this->orig_filename,
            'orig_size' => $this->orig_size,
            'basename' => $this->basename,
            'has_blur' => $this->has_blur,
            'has_mid' => $this->has_mid,
            'has_thumb' => $this->has_thumb,

            'render_urls' => [
                'full' => $this->renderUrl(),
                'mid' => $this->renderUrlMid(),
                'thumb' => $this->renderUrlThumb(),
                'blur' => $this->renderUrlBlur(),
            ],

            //'cattrs' => $this->cattrs,
            //'meta' => $this->meta,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,

            'flag_count' => $this->contentflags->count(),
        ];
    }
}
