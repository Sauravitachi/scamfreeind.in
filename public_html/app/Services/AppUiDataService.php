<?php

namespace App\Services;

use App\Models\AppUiData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AppUiDataService
{

    public function getAllDataSettings(): array
    {

       return [
           'video_section' => [
                'name' => 'video_section',
                'validation_rules' => [
                   'video_section_title' => 'nullable|string|max:255',
                   'video_section_title_color' => ['nullable', 'regex:/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/'],
                    'video_section_subtitle' => 'nullable|string|max:255',
                    'video_section_subtitle_color' => ['nullable', 'regex:/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/'],
                    'video_section_video' => 'nullable|file|mimes:mp4,mov,avi,wmv|max:20480',
                ]
            ],
        ];
    }

    public function getDataSetting(string $name): array|null
    {
        return $this->getAllDataSettings()[$name] ?? null;
    }



    public function dataTable(Request $request)
    {
        $data = AppUiData::query();

        return $data;
    }

    public function getDataByName(string $name): AppUiData|null
    {
        return AppUiData::where('name', $name)->first();
    }

    public function isValidAppDataSetting(string $name): bool
    {
        $data = $this->getAllDataSettings();
        return isset($data[$name]);
    }
}
