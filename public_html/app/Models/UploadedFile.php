<?php

namespace App\Models;

use App\Foundation\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class UploadedFile extends Model
{
    use SoftDeletes;

    public const  PREVIEWABLE_MIMES = ['image/png', 'image/jpeg', 'image/jpg'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['disk', 'path', 'original_name', 'mime', 'size', 'user_id'];

    public function getUrlAttribute(): string
    {
        $url = Storage::url($this->path);

        // Only prepend app.url if not using s3
        // if (config('filesystems.default') !== 's3') {
        //     $url = config('app.url') . $url;
        // }

        return $url;
    }

    public function getIsPreviewableFileAttribute(): bool
    {
        return in_array($this->mime, UploadedFile::PREVIEWABLE_MIMES);
    }

    /**
     * Get the user who uploaded the file
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
