<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enum\SubscriptionRole;
use App\Services\FileService;

class AppUiData extends Model
{
    use HasFactory;

    protected $table = 'app_ui_data';

    protected $fillable = [
        'name',
        'data',
    ];


    public static function getVideoSectionData(): self|null
    {
        return self::where('name', 'video_section')->first();
    }

    

    public function getFormattedNameAttribute(): string
    {
        if (!isset($this->name))
            return '';
        return ucwords(str_replace('_', ' ', $this->name));
    }

    public function getData(): \stdClass|null
    {
        return $this->data ? json_decode($this->data) : null;
    }


    public function getImage($value)
    {
        if ($value) {
            return FileService::getFileUrl('files/app_ui_data/' . $this->name . '/', $value);
        } else {
            return imageNotFoundUrl();
        }
    }


    public static function deleteImageFromS3(string $url)
    {
        if ($url) {
            $filePath = parse_url($url, PHP_URL_PATH);
            $filePath = ltrim($filePath, '/stockpathshala-livedata/public/');
            FileService::deleteFromS3($filePath);
        }
    }




    public static function getExpiredPopup(User $user, string $resource)
    {
        if (!in_array($user->subscription_role, [SubscriptionRole::PRO_EXPIRED, SubscriptionRole::TRIAL_EXPIRED])) {
            return null;
        }

        $sub = $user->lastEndedSubscription(['id', 'is_trial', 'is_mentorship']);

        if (!$sub)
            return null;

        $uiData = null;

        if ($sub->is_mentorship) {
            $uiData = self::getMentorshipExpiredUserPopup()?->getData() ?? null;
        } else if ($sub->is_trial) {
            $uiData = self::getTrialExpiredUserPopup()?->getData() ?? null;
        } else {
            $uiData = self::getProExpiredUserPopup()?->getData() ?? null;
        }

        if (!$uiData)
            return null;

        return self::helper__expired_user_popup__modifiedData($uiData, $resource);
    }



    public static function helper__expired_user_popup__modifiedData(\stdClass $data, string $resource): \stdClass|null
    {
        if (!in_array($resource, ['course', 'live_class', 'batch_class', 'mentorship_class']))
            return null;



        $newData = new \stdClass;
        $newData->image_url = $data->image_url ?? null;
        $newData->button_title = $data->button_title ?? null;

        switch ($resource) {

            case 'course': {
                $newData->title = $data->course_title;
                $newData->subtitle = $data->course_subtitle;
                break;
            }

            case 'live_class': {
                $newData->title = $data->live_class_title;
                $newData->subtitle = $data->live_class_subtitle;
                break;
            }

            case 'batch_class': {
                $newData->title = $data->batch_class_title;
                $newData->subtitle = $data->batch_class_subtitle;
                break;
            }

        }

        return $newData;
    }
}
