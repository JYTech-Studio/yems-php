<?php

namespace App\Http\Controllers;

use App\Models\ParentAccessToken;
use App\Models\User;
use Illuminate\Http\Request;

class ParentTokenController extends Controller
{
    /** 產生新 token（預設撤銷舊的，對齊 yems revoke_others=true）。 */
    public function store(Request $request, User $parent)
    {
        abort_unless($parent->isParent(), 404);

        $parent->accessTokens()->where('is_active', true)->update(['is_active' => false]);

        $parent->accessTokens()->create([
            'token'      => ParentAccessToken::generateToken(),
            'created_by' => $request->user()->id,
        ]);

        return back()->with('status', '已產生新的家長存取連結（舊連結已失效）');
    }

    /** 撤銷該家長所有 token。 */
    public function destroy(User $parent)
    {
        abort_unless($parent->isParent(), 404);
        $parent->accessTokens()->update(['is_active' => false]);

        return back()->with('status', '已撤銷所有家長連結');
    }
}
