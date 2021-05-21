<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\UsesUuid;

class Message extends Model
{
    use UsesUuid;

    protected $guarded = [ 'id', 'created_at', 'updated_at' ];

    // We can assume [messages] will always belong to a chat thread (other messsages can use other tables)
    public function chatthread()
    {
        return $this->belongsTo(ChatThread::class, 'messagable_id');
    }

    // Redundant -> %TODO deprecate
    public function messagable()
    {
        return $this->morphTo();
    }

    public function mediafile()
    {
        return $this->morphOne(Mediafile::class, 'resource');
    }
}
