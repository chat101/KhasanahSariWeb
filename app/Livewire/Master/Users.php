<?php

namespace App\Livewire\Master;

use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Operasional\Area;
use App\Models\Operasional\Wilayah;
use App\Models\MasterToko;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule; // tambahkan

class Users extends Component
{
    use WithPagination;

    // ====== State / Properti yang dibutuhkan Blade ======
    public $modal = false;
    public $search = '';
    public $userId = null;
    public $toko_id;
    public $tokos = [];
    public $nama = '';
    public $email = '';
    public $role = '';
    public $password = '';
    public $konfirmasi_password = '';

    public $area_id = '';
    public $wilayah_id = '';
    // ====== Rules dinamis ======
    public function rules()
    {
        $id = $this->userId ?: 'NULL';

        $role = strtolower($this->role ?? '');
        $isArea = $role === 'area';
        $isWilayah = $role === 'wilayah';
        $isKasir = in_array($role, ['kasir','personil','personil_toko']);

        return [
            'nama'   => ['required','min:3'],
            'email'  => ['required','email', 'unique:users,email,' . $id . ',id'],

            'role' => ['required', Rule::in([
                'admin',
                'gudang',
                'cream',
                'premixtoko',
                'premixpabrik',      // ✅ perbaiki ini
                'finance',
                'kasir',
                'personil',
                'personil_toko',
                'wilayah',
                'area',
            ])],

            'password' => $this->userId ? ['nullable','min:6'] : ['required','min:6'],
            'konfirmasi_password' => $this->userId ? ['nullable','same:password'] : ['required','same:password'],

            'area_id' => $isArea ? ['required','exists:area,id'] : ['nullable'],
            'wilayah_id' => $isWilayah ? ['required','exists:wilayah,id'] : ['nullable'],
            'toko_id' => $isKasir ? ['required','exists:tokos,id'] : ['nullable'],
        ];
    }
    public function updatedRole($value)
    {
        $v = strtolower($value ?? '');

        if ($v === 'area') {
            $this->wilayah_id = '';
            $this->toko_id = null;
        } elseif ($v === 'wilayah') {
            $this->area_id = '';
            $this->toko_id = null;
        } elseif (in_array($v, ['kasir','personil','personil_toko'])) {
            $this->area_id = '';
            $this->wilayah_id = '';
            // toko_id dibiarkan dipilih
        } else {
            $this->area_id = '';
            $this->wilayah_id = '';
            $this->toko_id = null;
        }
    }
    public function resetInputFields()
    {
        $this->reset([
            'userId',
            'nama',
            'email',
            'role',
            'password',
            'konfirmasi_password',
            'area_id',
            'wilayah_id',
            'toko_id'
        ]);
        $this->resetErrorBag();
        $this->resetValidation();
    }
    // ====== Aksi utama ======
    public function store()
    {
        $this->validate();

        $role = strtolower($this->role);

        $payload = [
            'name'  => $this->nama,
            'email' => $this->email,
            'role'  => $role,
            // lokasi: hanya salah satu yang terisi
            'area_id'    => $role === 'area' ? ($this->area_id ?: null) : null,
            'wilayah_id' => $role === 'wilayah' ? ($this->wilayah_id ?: null) : null,

              // ✅ kasir/personil
        'toko_id'    => in_array($role, ['kasir','personil','personil_toko']) ? ($this->toko_id ?: null) : null,
        ];

        if (!empty($this->password)) {
            $payload['password'] = Hash::make($this->password);
        }

        User::updateOrCreate(['id' => $this->userId], $payload);

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

        $this->area_id = (string)($user->area_id ?? '');
        $this->wilayah_id = (string)($user->wilayah_id ?? '');
        $this->toko_id = $user->toko_id; // ✅

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
    public function openModal()
    {
        $this->modal = true;
    }
    public function closeModal()
    {
        $this->modal = false;
    }



    // ====== Query & Render ======
    public function getUsersProperty()
    {
        return User::with(['area.wilayah', 'wilayahLangsung']) // lihat catatan model di bawah
            ->when($this->search, function ($q) {
                $q->where(function ($qq) {
                    $qq->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                        ->orWhere('role', 'like', "%{$this->search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(10);
    }
    public function mount()
    {
        $this->tokos = MasterToko::with('area')->orderBy('nmtoko')->get();
    }
    public function render()
    {
        return view('livewire.master.users', [
            'users' => $this->users,
            'areas' => Area::with('wilayah')->orderBy('nama_area')->get(), // agar tampil Area (Wilayah)
            'wilayahs' => Wilayah::orderBy('nama_wilayah')->get(),

        ]);
    }
}
