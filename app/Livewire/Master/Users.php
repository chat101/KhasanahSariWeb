<?php

namespace App\Livewire\Master;

use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class Users extends Component
{
    use WithPagination;

    // ====== State / Properti yang dibutuhkan Blade ======
    public $modal = false;
    public $search = '';
    public $userId = null;

    public $nama = '';
    public $email = '';
    public $role = '';
    public $password = '';
    public $konfirmasi_password = '';

    // ====== Rules dinamis ======
    public function rules()
    {
        $id = $this->userId ?: 'NULL';

        // Saat create: password wajib & konfirmasi
        // Saat update: password boleh kosong; kalau diisi, harus minimal 6 & sama dengan konfirmasi
        return [
            'nama'   => 'required|min:3',
            'email'  => 'required|email|unique:users,email,' . $id . ',id',
            'role'   => 'required|in:admin,gudang,finance',
            'password' => $this->userId
                ? 'nullable|min:6'
                : 'required|min:6',
            'konfirmasi_password' => $this->userId
                ? 'nullable|same:password'
                : 'required|same:password',
        ];
    }

    // ====== Aksi utama ======
    public function store()
    {
        $this->validate();

        $payload = [
            'name'  => $this->nama,      // mapping ke kolom 'name'
            'email' => $this->email,
            'role'  => $this->role,
        ];

        if (!empty($this->password)) {
            $payload['password'] = Hash::make($this->password);
        }

        User::updateOrCreate(
            ['id' => $this->userId],
            $payload
        );

        session()->flash('message', $this->userId ? 'User berhasil diperbarui.' : 'User berhasil ditambahkan.');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        $this->userId = $user->id;
        $this->nama   = $user->name;
        $this->email  = $user->email;
        $this->role   = $user->role ?? '';
        $this->password = '';
        $this->konfirmasi_password = '';

        $this->openModal();
    }

    #[On('delete-user')]
    public function deleteUser($payload)
    {
        $id = is_array($payload) ? ($payload['id'] ?? null) : $payload;
        if ($id) {
            User::whereKey($id)->delete();
            $this->dispatch('user-deleted'); // untuk SweetAlert sukses (listener di Blade-mu)
        }
    }

    // ====== Modal & util ======
    public function openModal()  { $this->modal = true; }
    public function closeModal() { $this->modal = false; }

    public function resetInputFields()
    {
        $this->reset(['userId','nama','email','role','password','konfirmasi_password']);
        $this->resetErrorBag();
        $this->resetValidation();
    }

    // ====== Query & Render ======
    public function getUsersProperty()
    {
        return User::query()
            ->when($this->search, function ($q) {
                $q->where(function ($qq) {
                    $qq->where('name','like',"%{$this->search}%")
                       ->orWhere('email','like',"%{$this->search}%")
                       ->orWhere('role','like',"%{$this->search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.master.users', [
            'users' => $this->users,
        ]);
    }
}
