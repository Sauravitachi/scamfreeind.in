<?php

namespace App\Services;

use App\Http\Requests\Admin\ChangeAccountPasswordRequest;
use App\Http\Requests\Admin\UserForceReleaseFreezeRequest;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Laravolt\Avatar\Facade as Avatar;
use Yajra\DataTables\EloquentDataTable;

class UserService extends Service
{
    public function dataTable(Request $request): EloquentDataTable
    {
        $query = User::query();

        $query->with([
            'roles' => fn (MorphToMany $query) => $query->orderBy('priority', 'asc'),
            'preferences',
        ]);

        $query->withExists(['scamStatusFreezes', 'customerEnquiryFreezes']);

        // User status filter
        $query->when(
            $request->filled('filter_status'),
            fn (Builder $q) => $q->where('status', $request->boolean('filter_status'))
        );

        // Logged in users filter
        $query->when(
            $request->filled('filter_logged_in'),
            fn (Builder $q) => $request->boolean('filter_logged_in') ? $q->whereLoggedIn() : $q->whereNotLoggedIn()
        );

        // role filter
        $query->when(
            $request->filled('filter_role'),
            fn (Builder $q) => $q->whereHas('roles', fn ($q) => $q->where('id', $request->filter_role))
        );

        $table = datatables()->eloquent($query);

        $table->addColumn('profile_avatar', fn (User $u) => $u->profileAvatar);

        $table->addColumn('is_logged_in', fn (User $user) => $user->isLoggedIn());

        $table->addColumn('sub_admin_id', fn (User $user) => $user->subAdminId);
        $table->addColumn('sub_admin_name', function (User $user) {
            $subAdminId = $user->subAdminId;
            if (!$subAdminId) return null;
            return User::find($subAdminId)?->name;
        });
        $table->addColumn('user_type', fn (User $user) => $user->userType());

        $table->editColumn('created_at', fn (User $user) => format_date($user->created_at));
        $table->editColumn('login_at', fn (User $user) => $user->login_at?->diffForHumans());

        $table->orderColumn('is_logged_in', function (Builder $query, string $order): void {
            $query->orderBy('last_pinged_at', $order);
        });

        return $table;
    }

    public function create(UserRequest $request): User
    {
        $user = new User($request->validated());
        $user->avatar = $this->createAvatar($user);
        $user->save();
        $user->syncRoles($request->validated('role', []));

        return $user;
    }

    public function update(User $user, UserRequest $request): bool
    {
        $user->fill($request->validated());
        if ($user->isDirty()) {
            if ($user->isDirty('name')) {
                $user->avatar = $this->createAvatar($user);
            }
            $user->save();
        }
        $user->syncRoles($request->validated('role', []));

        return true;
    }

    public function changePassword(ChangeAccountPasswordRequest $request, User $user): bool
    {
        return $user->update(['password' => $request->validated('new_password')]);
    }

    public function forceReleaseFreeze(User $user, UserForceReleaseFreezeRequest $request): bool
    {
        $hours = $request->integer('freeze_disabled_until_hours');

        return $user->update(['freeze_disabled_until' => now()->addHours($hours)]);
    }

    public function createAvatar(User $user): string
    {
        if ($user->avatar) {
            Storage::delete($user->avatar);
        }

        $base64 = Avatar::create(name: $user->name)
            ->setDimension(width: 500, height: 500)->setFontSize(250)->toBase64();

        return UploadedFileService::getInstance()->saveAvatarFromBase64($base64);
    }

    public function isDeletable(User $user): bool
    {
        return true;
    }

    public function delete(User $user): ?bool
    {
        return $user->delete();
    }

    public function syncUserAvatars(): void
    {
        $users = User::all();

        $users->map(function (User $user) {

            $user->avatar = $this->createAvatar($user);
            $user->save();

        });
    }
}
