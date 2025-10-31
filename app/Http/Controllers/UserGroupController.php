<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserGroupController extends Controller
{
    /**
     * |KB Список групп пользователей (админ)
     */
    public function index()
    {
        $groups = UserGroup::orderByDesc('id')->paginate(20);
        return view('admin.user-groups.index', compact('groups'));
    }

    /**
     * |KB Форма создания группы
     */
    public function create()
    {
        return view('admin.user-groups.create');
    }

    /**
     * |KB Создать группу
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:user_groups,name',
            'code' => 'required|string|max:100|alpha_dash|unique:user_groups,code',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $group = UserGroup::create($validated);

        return redirect()->route('admin.user-groups.index')
            ->with('success', 'Группа создана успешно.');
    }

    /**
     * |KB Форма редактирования группы
     */
    public function edit(UserGroup $userGroup)
    {
        return view('admin.user-groups.edit', ['group' => $userGroup]);
    }

    /**
     * |KB Обновить группу
     */
    public function update(Request $request, UserGroup $userGroup)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:user_groups,name,' . $userGroup->id,
            'code' => 'required|string|max:100|alpha_dash|unique:user_groups,code,' . $userGroup->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $userGroup->update($validated);

        return redirect()->route('admin.user-groups.index')
            ->with('success', 'Группа обновлена успешно.');
    }

    /**
     * |KB Удалить группу
     */
    public function destroy(UserGroup $userGroup)
    {
        $userGroup->delete();
        return redirect()->route('admin.user-groups.index')
            ->with('success', 'Группа удалена.');
    }

    /**
     * |KB Форма назначения пользователей в группу
     */
    public function assignForm(UserGroup $userGroup)
    {
        $users = User::orderBy('name')->get();
        $userIdsInGroup = $userGroup->users()->pluck('users.id')->all();
        return view('admin.user-groups.assign', [
            'group' => $userGroup,
            'users' => $users,
            'userIdsInGroup' => $userIdsInGroup,
        ]);
    }

    /**
     * |KB Сохранить назначения пользователей в группе
     */
    public function assignStore(Request $request, UserGroup $userGroup)
    {
        $validated = $request->validate([
            'user_ids' => 'array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $selected = collect($validated['user_ids'] ?? []);

        // |KB Синхронизируем участников группы с указанием назначившего
        $syncPayload = [];
        foreach ($selected as $userId) {
            $syncPayload[$userId] = [
                'assigned_by' => Auth::id(),
                'comment' => 'Назначено администратором',
            ];
        }

        $userGroup->users()->sync($syncPayload);

        return redirect()->route('admin.user-groups.index')
            ->with('success', 'Состав группы обновлен.');
    }

    /**
     * |KB Удалить пользователя из группы
     */
    public function detachUser(UserGroup $userGroup, User $user)
    {
        $userGroup->users()->detach($user->id);
        return back()->with('success', 'Пользователь удален из группы.');
    }
}


