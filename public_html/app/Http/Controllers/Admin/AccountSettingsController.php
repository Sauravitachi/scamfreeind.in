<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\UpdateAccountSettingsRequest;
use App\Services\ResponseService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AccountSettingsController extends \App\Foundation\Controller
{
    /**
     * Constructor for AccountSettingsController
     */
    public function __construct(
        protected UserService $userService,
        protected ResponseService $responseService,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user()->refresh();

        return view('admin.account-settings.index', compact('user'));
    }

    public function update(UpdateAccountSettingsRequest $request): JsonResponse
    {
        $user = $request->user();
        
        $validatedData = $request->validated();
        
        $user->fill([
            'name' => $validatedData['name'],
            'username' => $validatedData['username'],
            'email' => $validatedData['email'],
            'country_code' => $validatedData['country_code'] ?? 'in',
            'phone_number' => $validatedData['phone_number'],
        ]);

        $nameChanged = $user->isDirty('name');
        
        // Handle avatar deletion if requested
        if ($request->boolean('delete_avatar')) {
            if ($user->avatar) {
                Storage::delete($user->avatar);
            }
            $user->avatar = $this->userService->createAvatar($user);
        }
        
        // Handle uploading new profile picture
        if ($request->hasFile('profile_picture')) {
            if ($user->avatar) {
                Storage::delete($user->avatar);
            }
            
            $file = $request->file('profile_picture');
            $path = $file->store('avatar', 'public');
            $user->avatar = $path;
        } elseif (!$request->boolean('delete_avatar') && $nameChanged) {
            // If name changed, and no custom picture was uploaded, we could also regenerate
            // But to avoid overwriting custom uploaded avatars, we do not auto-regenerate here.
            // Users can click "Delete avatar" to regenerate a fresh initials avatar with their new name.
        }

        $user->save();

        $this->flashToast('success', 'Account Details Updated!');

        return $this->responseService->json(success: true, redirectTo: route('admin.account-settings.index'));
    }
}
