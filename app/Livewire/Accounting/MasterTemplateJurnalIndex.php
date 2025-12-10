<?php

namespace App\Livewire\Accounting;

use Livewire\Component;
use App\Models\Accounting\JournalTransactionType;
use App\Models\Accounting\JournalTransactionTemplate;

class MasterTemplateJurnalIndex extends Component
{
    public $transaction_type_id = '';

    public $showModal = false;
    public $editId = null;

    public $side = 'debit';
    public $order_no = 1;
    public $source_key;

    public $transactionTypes;
    public $templates ;

    protected function rules()
    {
        return [
            'transaction_type_id' => 'required|exists:journal_transaction_types,id',
            'side'       => 'required|in:debit,kredit',
            'order_no'   => 'required|integer|min:1',
            'source_key' => 'required|string|max:100',
        ];
    }

    public function mount()
    {
        $this->transactionTypes = JournalTransactionType::orderBy('nama')->get();

        // default: pilih jenis pertama kalau ada
        if (!$this->transaction_type_id && $this->transactionTypes->count() > 0) {
            $this->transaction_type_id = $this->transactionTypes->first()->id;
        }

        $this->loadTemplates();
    }

    public function render()
    {
        return view('livewire.accounting.master-template-jurnal-index');
    }

    public function updatedTransactionTypeId()
    {
        $this->loadTemplates();
    }

    protected function loadTemplates()
    {
        if (!$this->transaction_type_id) {
            $this->templates = [];
            return;
        }

        $this->templates = JournalTransactionTemplate::where('transaction_type_id', $this->transaction_type_id)
            ->orderBy('order_no')
            ->orderBy('side')
            ->get();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->side = 'debit';
        $this->order_no = ($this->templates->max('order_no') ?? 0) + 1;
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $row = JournalTransactionTemplate::where('transaction_type_id', $this->transaction_type_id)
            ->findOrFail($id);

        $this->editId   = $row->id;
        $this->side     = $row->side;
        $this->order_no = $row->order_no;
        $this->source_key = $row->source_key;

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editId) {
            $row = JournalTransactionTemplate::where('transaction_type_id', $this->transaction_type_id)
                ->findOrFail($this->editId);

            $row->update([
                'side'       => $this->side,
                'order_no'   => $this->order_no,
                'source_key' => $this->source_key,
            ]);

            $msg = 'Template jurnal berhasil diupdate.';
        } else {
            JournalTransactionTemplate::create([
                'transaction_type_id' => $this->transaction_type_id,
                'side'       => $this->side,
                'order_no'   => $this->order_no,
                'source_key' => $this->source_key,
            ]);

            $msg = 'Template jurnal baru berhasil ditambahkan.';
        }

        $this->showModal = false;
        $this->resetForm();
        $this->loadTemplates();

        $this->dispatch('notify', type: 'success', message: $msg);
    }
    public $sourceOptions = [
        'Input dari Form' => [
            'input_akun'       => 'input_akun — Akun biaya/pendapatan',
            'input_kas'        => 'input_kas — Kas',
            'input_kas_asal'   => 'input_kas_asal — Kas asal mutasi',
            'input_kas_tujuan' => 'input_kas_tujuan — Kas tujuan mutasi',
        ],
        'Role COA' => [
            'role:kas'           => 'role:kas — Default kas',
            'role:pendapatan'    => 'role:pendapatan — Default pendapatan',
            'role:biaya'         => 'role:biaya — Default biaya',
            'role:piutang'       => 'role:piutang — Default piutang',
            'role:hutang_dagang' => 'role:hutang_dagang — Default hutang dagang',
        ],
    ];
    public function delete($id)
    {
        $row = JournalTransactionTemplate::where('transaction_type_id', $this->transaction_type_id)
            ->findOrFail($id);

        $row->delete();

        $this->loadTemplates();

        $this->dispatch('notify', type: 'success', message: 'Template jurnal berhasil dihapus.');
    }

    protected function resetForm()
    {
        $this->resetErrorBag();
        $this->resetValidation();

        $this->editId    = null;
        $this->side      = 'debit';
        $this->order_no  = 1;
        $this->source_key = '';
    }

}
