<?php

namespace Database\Seeders;

use App\Models\AppUiData;
use Illuminate\Database\Seeder;

class AppUiDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sections = [
            [
                'name' => 'video_section',
                'data' => [
                    'video_section_title' => 'Our Awareness Videos',
                    'video_section_subtitle' => 'Watch our latest educational content about scam prevention',
                    'video_section_video' => null,
                    'video_section_title_color' => '#6c757d',
                    'video_section_subtitle_color' => '#6c757d',
                ]
            ],
        ];

        foreach ($sections as $section) {
            AppUiData::updateOrCreate(
                ['name' => $section['name']],
                ['data' => json_encode($section['data'])]
            );
        }
    }
}
