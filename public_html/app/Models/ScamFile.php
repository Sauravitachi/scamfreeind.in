<?php

namespace App\Models;

use App\Foundation\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScamFile extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'scam_id',
        'file_id',
        'message',
        'user_id',
    ];

    /**
     * Get the uploaded file object
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(UploadedFile::class, 'file_id');
    }

    /**
     * Get the user who uploaded the scam file
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
