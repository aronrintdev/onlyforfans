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
            'id' => $this->id,
            'slug' => $this->slug,
	        'name' => $this->name,
	        'basename' => $this->basename,
            'mfname' => $this->mfname,
            'filename' => $this->filename,
            'filepath' => $this->filepath,
            'midFilepath' => $this->midFilepath,
            'mftype' => $this->mftype,
	        'has_thumb' => $this->has_thumb,
	        'has_mid' => $this->has_mid,
	        'has_blur' => $this->has_blur,
	        'is_image' => $this->is_image,
	        'is_video' => $this->is_video,
            'mimetype' => $this->mimetype,
            'orig_ext' => $this->orig_ext,
            'orig_filename' => $this->orig_filename,
            'resource_id' => $this->resource_id,
            'resource_type' => $this->resource_type,
            'created_at' => $this->created_at,
        ];
    }
}
