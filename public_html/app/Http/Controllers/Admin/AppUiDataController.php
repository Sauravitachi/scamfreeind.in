<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FileService;
use App\Services\AppUiDataService;
use App\Models\AppUiData;
use Illuminate\Http\Request;
use JsValidator;

class AppUiDataController extends Controller
{

    protected $image_directory;

    public function __construct(
        protected AppUiDataService $service
    ) {

        $this->image_directory = 'files/app_ui_data/{name}';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $settings = $this->service->getAllDataSettings();
        $settingNames = array_column($settings, 'name');

        $savedDataByName = AppUiData::query()
            ->whereIn('name', $settingNames)
            ->get()
            ->keyBy('name');

        $data = collect($settingNames)->map(function (string $name) use ($savedDataByName) {
            return $savedDataByName->get($name) ?? new AppUiData(['name' => $name]);
        });

        return view('admin.app_ui_data.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \\Illuminate\\Http\\Response
     */
    public function create()
    {
        $settings = $this->service->getAllDataSettings();
        $settingNames = array_column($settings, 'name');

        if (empty($settingNames)) {
            return redirect()
                ->route('admin.app-ui-data.index')
                ->with('error', 'No App UI sections are configured.');
        }

        $existingNames = AppUiData::query()
            ->whereIn('name', $settingNames)
            ->pluck('name')
            ->all();

        $targetName = collect($settingNames)->first(function (string $name) use ($existingNames) {
            return !in_array($name, $existingNames, true);
        }) ?? $settingNames[0];

        return redirect()->route('admin.app-ui-data.edit', ['app_ui_datum' => $targetName]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(string $name)
    {
        $dataSettings = $this->service->getDataSetting($name);

        abort_if(!$dataSettings, 404);

        $validator = JsValidator::make($dataSettings['validation_rules']);

        $appUiData = $this->service->getDataByName($name);

        if (!$appUiData) {
            $appUiData = new AppUiData;
            $appUiData->name = $name;
            $data = new \stdClass;
        } else {
            $data = $appUiData->getData();
        }




        return view("admin/app_ui_data/edit", compact('appUiData', 'data', 'validator'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $name)
    {
        $dataSettings = $this->service->getDataSetting($name);

        abort_if(!$dataSettings, 404);

        $rules = $dataSettings['validation_rules'];
        
        // Expand wildcard rules for flat keys (e.g., expert_section_title_.*)
        $expandedRules = [];
        foreach ($rules as $key => $rule) {
            if (str_contains($key, '.*')) {
                // Escape special characters and replace .* with a matching group
                $pattern = '/^' . str_replace(['_', '.*'], ['\_', '(.*)'], $key) . '$/';
                foreach ($request->all() as $reqKey => $reqVal) {
                    if (preg_match($pattern, $reqKey)) {
                        $expandedRules[$reqKey] = $rule;
                    }
                }
            } else {
                $expandedRules[$key] = $rule;
            }
        }

        $request->validated_data = $request->validate($expandedRules);

        $method = "handle__$name";

        $request->merge([
            'rules' => $rules
        ]);

        return $this->$method($request, $name);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function handle__video_section(Request $request, string $name)
    {
        $data = $request->validated_data;

        $appUiData = $this->service->getDataByName($name) ?? new AppUiData;

        $appUiData->forceFill(['name' => $name, 'data' => json_encode($data)]);

        $appUiData->save();

        return redirect()->route('admin.app-ui-data.index')->with('success', 'UI Updated!');
    }

    public function handle__expert_section(Request $request, string $name)
    {
        $data = $request->validated_data;
        $appUiData = $this->service->getDataByName($name) ?? new AppUiData;
        $oldData = $appUiData->getData() ?? new \stdClass;

        // Handle uploaded images
        foreach ($request->allFiles() as $key => $file) {
            if (str_starts_with($key, 'expert_section_image_')) {
                $dir = str_replace('{name}', $name, $this->image_directory);
                $uploaded = FileService::imageUploader($request, $key, $dir);
                if ($uploaded) {
                    $data[$key] = $uploaded;
                }
            }
        }
        
        // Restore old image filenames if no new ones were uploaded but we have old ones
        foreach ($request->all() as $key => $value) {
           if (str_starts_with($key, 'expert_section_image_') && !$request->hasFile($key)) {
                if (isset($oldData->$key)) {
                    $data[$key] = $oldData->$key;
                }
           }
        }

        $appUiData->forceFill(['name' => $name, 'data' => json_encode($data)]);
        $appUiData->save();

        return redirect()->route('admin.app-ui-data.index')->with('success', 'Expert UI Updated!');
    }

    private function saveImage(Request $request, string $imageField, string $name)
    {
        $dir = str_replace('{name}', $name, $this->image_directory);
        $image = FileService::imageUploader($request, $imageField, $dir);
        return $image ? FileService::getFileUrl($dir . '/', $image) : imageNotFoundUrl();
    }

    
}
