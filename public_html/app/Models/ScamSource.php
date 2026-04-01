<?php

namespace App\Models;

use App\Foundation\Model;

class ScamSource extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ['slug', 'title', 'indicator_color'];

    public static function sheetImportSource(array|string $columns = ['*']): ScamSource
    {
        $title = 'Sheet Import';
        $slug = 'sheet_import';

        return ScamSource::where('slug', $slug)->first($columns) ?? ScamSource::create(['slug' => $slug, 'title' => $title]);
    }

    public static function webhookSource(array|string $columns = ['*']): ScamSource
    {
        $title = 'Webhook';
        $slug = 'webhook';

        return ScamSource::where('slug', $slug)->first($columns) ?? ScamSource::create(['slug' => $slug, 'title' => $title]);
    }
}
