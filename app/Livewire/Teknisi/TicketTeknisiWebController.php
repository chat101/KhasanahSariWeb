<?php

namespace App\Livewire\Teknisi;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Teknisi\TicketTeknisi;
use App\Models\User;
use App\Models\UserPushToken;

class TicketTeknisiWebController extends Component
{
    use WithFileUploads;

    // Form fields
    public $title;
    public $category;
    public $description;
    public $photos = [];

    protected function rules()
    {
        return [
            'title'       => 'required|string|max:255',
            'category'    => 'required|string|max:200',
            'description' => 'required|string',
            'photos.*'    => 'image|max:4096|mimes:jpg,jpeg,png',
        ];
    }

    public function submit()
    {
        $this->validate();

        $user = Auth::user();
        $photoUrls = [];

        // Upload foto
        foreach ($this->photos as $photo) {
            $filename = 'ticket_' . Str::random(10) . '.' . $photo->getClientOriginalExtension();
            $path = $photo->storeAs('tickets', $filename, 'public');
            $photoUrls[] = asset('storage/' . $path);
        }

        // Simpan tiket
        $ticket = TicketTeknisi::create([
            'user_id'     => $user->id,
            'divisi_id'   => $user->divisi_id ?? null,
            'title'       => $this->title,
            'category'    => $this->category,
            'description' => $this->description,
            'photo_paths' => json_encode($photoUrls),
            'status'      => 'pending',
        ]);

        // Kirim notif ke teknisi (divisi_id == 12)
        $this->sendPushNotification($ticket);

        // Reset form
        $this->reset(['title', 'category', 'description', 'photos']);

        // Feedback UI
        $this->dispatch('swal:success', 'Tiket berhasil dikirim!');
    }

    private function sendPushNotification($ticket)
    {
        $targetUserIds = User::where('divisi_id', 12)
            ->whereHas('pushTokens')
            ->pluck('id');

        $tokens = UserPushToken::whereIn('user_id', $targetUserIds)
            ->pluck('expo_token')
            ->unique()
            ->values();

        if ($tokens->isEmpty()) return;

        try {
            $tokens->chunk(99)->each(function ($chunk) use ($ticket) {
                $messages = $chunk->map(fn($t) => [
                    'to'        => $t,
                    'title'     => 'Tiket Baru Masuk',
                    'body'      => $ticket->title,
                    'priority'  => 'high',
                    'channelId' => 'ticket_alerts',
                    'sound'     => 'biohazard.wav',
                    'data'      => [
                        'type' => 'ticket_new',
                        'ticket_id' => $ticket->id,
                    ],
                ]);

                $res = Http::acceptJson()->asJson()
                    ->post("https://exp.host/--/api/v2/push/send", $messages->toArray())
                    ->throw()
                    ->json();

                Log::info("push ticket_new via livewire", ['result' => $res]);
            });
        } catch (\Throwable $e) {
            Log::error("push error", ['err' => $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.teknisi.ticket-teknisi-web-controller');
    }
}
