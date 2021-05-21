<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\UsesUuid;

class ChatThread extends Model
{
    use UsesUuid;

    protected $table = 'chatthreads';
    protected $guarded = [ 'id', 'created_at', 'updated_at' ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function messages()
    {
        return $this->morphMany(Message::class, 'messagable');
    }
}
