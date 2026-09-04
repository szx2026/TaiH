<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdministrator($request);

        return view('members.index', [
            'departments' => Department::query()->where('is_active', true)->orderBy('name')->get(),
            'members' => User::query()->with('department')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdministrator($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'department_id' => ['required', Rule::exists('departments', 'id')],
            'role' => ['required', Rule::in(['administrator', 'manager', 'member'])],
        ]);

        User::create($data);

        return to_route('members.index');
    }

    private function authorizeAdministrator(Request $request): void
    {
        abort_unless($request->user()?->hasRole('administrator'), 403);
    }
}
