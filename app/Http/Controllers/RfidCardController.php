<?php

namespace App\Http\Controllers;

use App\Models\RfidCard;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RfidCardController extends Controller
{
    public function store(Request $request, User $student)
    {
        abort_unless($student->isStudent(), 404);

        $data = $request->validate([
            'card_uid' => ['required', 'string', 'max:255', Rule::unique('rfid_cards', 'card_uid')],
            'label'    => ['nullable', 'string', 'max:255'],
        ]);

        $student->rfidCards()->create($data);

        return back()->with('status', '已綁定 RFID 卡');
    }

    public function destroy(User $student, RfidCard $card)
    {
        abort_unless($card->student_id === $student->id, 404);
        $card->delete();

        return back()->with('status', '已解除卡片綁定');
    }
}
