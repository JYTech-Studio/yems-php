<?php

namespace App\Http\Controllers;

use App\Models\StudentParent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ParentController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));

        $parents = User::parentUsers()
            ->withCount('children')
            ->when($q !== '', fn ($query) => $query->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('parents.index', compact('parents', 'q'));
    }

    public function create()
    {
        return view('parents.create');
    }

    public function store(Request $request)
    {
        $parent = User::create($this->validated($request) + ['role' => 'parent']);

        return redirect()->route('parents.show', $parent)->with('status', '已新增家長');
    }

    public function show(User $parent)
    {
        abort_unless($parent->isParent(), 404);
        $parent->load(['children', 'accessTokens' => fn ($q) => $q->latest()]);

        // 可綁定的學生（尚未綁定的）
        $availableStudents = User::students()
            ->whereNotIn('id', $parent->children->pluck('id'))
            ->orderBy('name')->get();

        return view('parents.show', compact('parent', 'availableStudents'));
    }

    public function edit(User $parent)
    {
        abort_unless($parent->isParent(), 404);

        return view('parents.edit', compact('parent'));
    }

    public function update(Request $request, User $parent)
    {
        abort_unless($parent->isParent(), 404);
        $parent->update($this->validated($request, $parent));

        return redirect()->route('parents.show', $parent)->with('status', '已更新家長資料');
    }

    public function destroy(User $parent)
    {
        abort_unless($parent->isParent(), 404);
        $parent->delete();

        return redirect()->route('parents.index')->with('status', '已刪除家長');
    }

    // === 綁定 / 解除子女 ===
    public function attachChild(Request $request, User $parent)
    {
        abort_unless($parent->isParent(), 404);
        $data = $request->validate([
            'student_id' => ['required', 'exists:profiles,id'],
            'relation'   => ['nullable', 'string', 'max:50'],
        ]);

        StudentParent::firstOrCreate(
            ['student_id' => $data['student_id'], 'parent_id' => $parent->id],
            ['relation' => $data['relation'] ?? '家長'],
        );

        return back()->with('status', '已綁定子女');
    }

    public function detachChild(User $parent, User $student)
    {
        StudentParent::where('parent_id', $parent->id)->where('student_id', $student->id)->delete();

        return back()->with('status', '已解除綁定');
    }

    private function validated(Request $request, ?User $parent = null): array
    {
        return $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('profiles', 'email')->ignore($parent?->id)],
        ]);
    }
}
