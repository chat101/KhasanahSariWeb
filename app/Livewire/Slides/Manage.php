<?php

namespace App\Livewire\Slides;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Slide;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
class Manage extends Component
{
    use WithFileUploads;

    public $slides;
    public $slideId = null;

    // form fields
    public $title, $image, $existing_image_path, $link_url, $position=0, $is_active=true, $starts_at, $ends_at;

    public function mount() { $this->reload(); }

    public function reload() { $this->slides = Slide::orderBy('position')->get(); }

    public function create() { $this->resetForm(); }

    public function edit($id) {
        $s = Slide::findOrFail($id);
        $this->slideId = $s->id;
        $this->title = $s->title;
        $this->existing_image_path = $s->image_path;
        $this->link_url = $s->link_url;
        $this->position = $s->position;
        $this->is_active = $s->is_active;
        $this->starts_at = optional($s->starts_at)?->format('Y-m-d\TH:i');
        $this->ends_at   = optional($s->ends_at)?->format('Y-m-d\TH:i');
    }

    public function save() {
        $data = $this->validate([
            'title'     => ['nullable','string','max:255'],
            'image'     => [$this->slideId ? 'nullable' : 'required','image','max:4096'],
            'link_url'  => ['nullable','url'],
            'position'  => ['required','integer','min:0'],
            'is_active' => ['boolean'],
            'starts_at' => ['nullable','date'],
            'ends_at'   => ['nullable','date','after_or_equal:starts_at'],
        ]);

        if ($this->image) {
            if ($this->existing_image_path) {
                Storage::disk('public')->delete($this->existing_image_path);
            }
            $path = $this->image->store('slides','public');
        } else {
            $path = $this->existing_image_path; // keep old
        }

        Slide::updateOrCreate(
            ['id' => $this->slideId],
            [
                'title' => $this->title,
                'image_path' => $path,
                'link_url' => $this->link_url,
                'position' => $this->position,
                'is_active' => (bool)$this->is_active,
                'starts_at' => $this->starts_at,
                'ends_at' => $this->ends_at,
            ]
        );

        $this->resetForm();
        $this->reload();
        $this->dispatch('saved');
    }

    public function delete($id) {
        $s = Slide::findOrFail($id);
        Storage::disk('public')->delete($s->image_path);
        $s->delete();
        $this->reload();
    }

    private function resetForm() {
        $this->reset(['slideId','title','image','existing_image_path','link_url','position','is_active','starts_at','ends_at']);
        $this->position = 0; $this->is_active = true;
    }
    public function render()
    {
        return view('livewire.slides.manage');
    }
}
