<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class ManageUsers extends Component
{
    use WithPagination;

    public $name;
    public $email;
    public $password;
    public $role = 'staff';
    public $confirmingUserDeletion = false;
    public $userIdToDelete;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8',
        'role' => 'required|in:admin,staff',
    ];

    public function createUser()
    {
        $this->validate();

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role' => $this->role,
        ]);

        $this->reset(['name', 'email', 'password', 'role']);
        session()->flash('message', 'User created successfully.');
    }

    public function confirmUserDeletion($id)
    {
        $this->confirmingUserDeletion = true;
        $this->userIdToDelete = $id;
    }

    public function deleteUser()
    {
        $user = User::find($this->userIdToDelete);

        if ($user) {
            if ($user->id === auth()->id()) {
                session()->flash('error', 'You cannot delete yourself.');
            } else {
                $user->delete();
                session()->flash('message', 'User deleted successfully.');
            }
        }

        $this->confirmingUserDeletion = false;
        $this->userIdToDelete = null;
    }

    public function render()
    {
        return view('livewire.manage-users', [
            'users' => User::orderBy('created_at', 'desc')->paginate(10),
        ])->layout('layouts.app');
    }
}
