<?php

namespace App\Models;

use App\Foundation\Model;

class EscalationChat extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['escalation_id', 'user_id', 'message', 'file_id', 'is_edited'];

    /**
     * Get the escalation of the chat
     */
    public function escalation()
    {
        return $this->belongsTo(Escalation::class);
    }

    /**
     * Get the user of the chat
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the uploaded file of the chat
     */
    public function file()
    {
        return $this->belongsTo(UploadedFile::class, 'file_id');
    }
}
