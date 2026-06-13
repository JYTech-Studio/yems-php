<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = User::staff()->orderBy('role')->orderBy('name')->paginate(12);

        return view('accounts.index', compact('accounts'));
    }

    public function create()
    {
        return view('accounts.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('profiles', 'email')],
            'role'     => ['required', 'in:admin,teacher'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);
        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('accounts.index')->with('status', '已建立帳號');
    }

    public function edit(User $account)
    {
        abort_unless($account->isStaff(), 404);

        return view('accounts.edit', compact('account'));
    }

    public function update(Request $request, User $account)
    {
        abort_unless($account->isStaff(), 404);

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('profiles', 'email')->ignore($account->id)],
            'role'      => ['required', 'in:admin,teacher'],
            'is_active' => ['required', 'boolean'],
            'password'  => ['nullable', 'confirmed', Password::defaults()],
        ]);

        if (! empty($data['password'])) {
            $account->password = Hash::make($data['password']);
        }
        unset($data['password']);
        $account->update($data);

        return redirect()->route('accounts.index')->with('status', '已更新帳號');
    }

    public function destroy(Request $request, User $account)
    {
        abort_unless($account->isStaff(), 404);
        abort_if($account->id === $request->user()->id, 403, '不能刪除自己的帳號');

        $account->delete();

        return redirect()->route('accounts.index')->with('status', '已刪除帳號');
    }
}
