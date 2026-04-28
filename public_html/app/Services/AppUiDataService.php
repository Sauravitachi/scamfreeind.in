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
                   'video_section_title' => 'nullable|array',
                   'video_section_title.*'=> 'nullable|string|max:255',
                   'video_section_title_color' => ['nullable', 'regex:/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/'],
                    'video_section_subtitle' => 'nullable|array',
                    'video_section_subtitle.*' => 'nullable|string|max:255',
                    'video_section_subtitle_color' => ['nullable', 'regex:/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/'],
                    'video_section_video' => 'nullable|array',
                    'video_section_video.*' => 'nullable|url|max:2000',
                ]
            ],
             'expert_section' => [
                'name' => 'expert_section',
                'validation_rules' => [
                    'expert_section_title_.*' => 'nullable|string|max:255',
                    'expert_section_title_color_.*' => ['nullable', 'regex:/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/'],
                    'expert_section_subtitle_.*' => 'nullable|string|max:255',
                    'expert_section_subtitle_color_.*' => ['nullable', 'regex:/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/'],
                    'expert_section_area_.*' => 'nullable|string|max:255',
                    'expert_section_area_color_.*' => ['nullable', 'regax:/^#([0-9A-Fa-f]{}|[0-9A-Fa-f]{6})$/'],
                    'expert_section_image_.*' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:5048',
                    'expert_section_email_.*' => 'nullable|email',
                    'expert_section_phone_.*' => 'nullable|string|max:255',                    
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
