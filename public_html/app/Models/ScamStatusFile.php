<?php

namespace App\Models;

use App\Foundation\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScamStatusFile extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['scam_id', 'status_id', 'file_id', 'batch_id'];

    /**
     * Get the uploaded file of the chat
     */
    public function file(): BelongsTo
    {
        return $this->belongsTo(UploadedFile::class, 'file_id');
    }

    /**
     * Get the status of the file
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(ScamStatus::class, 'status_id');
    }

    /**
     * Generate new batch id for collection of files
     */
    public static function generateBatchId(Scam $scam): string
    {
        return md5($scam->id.rand(1111111, 9999999).time());
    }
}
