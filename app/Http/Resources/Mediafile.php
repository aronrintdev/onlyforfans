<?php
namespace App\Http\Resources;
use App\Models\Mediafile as MediafileModel;

use Illuminate\Http\Resources\Json\JsonResource;

class Mediafile extends JsonResource
{
    public function toArray($request)
    {
        $sessionUser = $request->user();
        $model = MediafileModel::find($this->id); // %FIXME: n+1 performance issue (not so bad if paginated?)
        $hasAccess = $sessionUser->can('view', $model);

        return [
            'access' =>  $hasAccess,
            'id' => $this->id,
            'slug' => $this->slug,
	        'name' => $this->name,
	        'basename' => $this->basename,
            'mfname' => $this->mfname,
            'filename' => $this->filename,
            'filepath' => $hasAccess ? $this->filepath : null, // full S3 URL %NOTE
            'midFilepath' => $hasAccess ? $this->midFilepath : null,
            'thumbFilepath' => $hasAccess ? $this->thumbFilepath : null,
            'blurFilepath' => !$hasAccess ? $this->blurFilepath : null,
            'mftype' => $this->mftype,
	        'is_primary' => $this->is_primary,
	        'has_thumb' => $this->has_thumb,
	        'has_mid' => $this->has_mid,
	        'has_blur' => $this->has_blur,
	        'is_image' => $this->is_image,
	        'is_video' => $this->is_video,
            'is_audio' => $this->is_audio,
            'mimetype' => $this->mimetype,
            'orig_ext' => $this->orig_ext,
            'orig_filename' => $this->orig_filename,
            'resource' => $this->whenLoaded('resource'),
            'resource_id' => $this->resource_id,
            'resource_type' => $this->resource_type,
            'orig_size' => $this->diskmediafile->orig_size,
            'created_at' => $this->created_at,
        ];
    }
}
