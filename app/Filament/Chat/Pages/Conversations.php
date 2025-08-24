<?php

namespace App\Filament\Chat\Pages;

use App\Models\Chat;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

use function Illuminate\Log\log;

class Conversations extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.chat.pages.conversations';

    protected static ?string $title = 'Chats';

    public Collection $users;
    public ?User $selectedUser = null;
    public ?Collection $messages;
    public ?string $newMessage = null;
    public string $search = '';

    public function mount()
    {
        $this->getUsers();

        $this->fetchMessages();
    }

    public function getListeners(): array
    {
        return [
            "echo-private:chats." . auth()->id() . ",ChatMessageSent" => 'newMessageReceived',
        ];
    }

    public function updatedSearch()
    {
        $this->getUsers();
    }

    protected function getUsers()
    {
        $this->users = User::query()
            ->where('id', '!=', auth()->id())
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name')
            ->get();
    }

    public function selectUser(User $user)
    {
        $this->selectedUser = $user;

        $this->fetchMessages();
    }

    public function newMessageReceived($event)
    {
        $chat = $event['chat'];

        $newChat = Chat::with('sender', 'receiver')->find($chat['id']);

        if ($this->selectedUser && $newChat->sender_id === $this->selectedUser->id) {
            $this->messages->push($newChat);
        }

        $this->dispatch('showLastMessage');
    }

    public function sendMessage()
    {
        if (empty($this->newMessage) || !$this->selectedUser) {
            return;
        }

        $newMessage = Chat::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $this->selectedUser->id,
            'message' => $this->newMessage,
            'sent_at' => now(),
        ]);

        $this->newMessage = null;

        $this->messages->push($newMessage);

        broadcast(new \App\Events\ChatMessageSent($newMessage))
            ->toOthers();


        $this->dispatch('showLastMessage');
    }

    public function fetchMessages()
    {
        $this->newMessage = null;

        if (!$this->selectedUser) {
            $this->messages = collect();
            return;
        }

        $this->messages = Chat::query()
            ->with(['sender', 'receiver'])
            ->where(function ($query) {
                $query->where('sender_id', auth()->id())
                    ->where('receiver_id', $this->selectedUser->id);
            })->orWhere(function ($query) {
                $query->where('sender_id', $this->selectedUser->id)
                    ->where('receiver_id', auth()->id());
            })
            ->orderBy('created_at')
            ->get()
            ->groupBy(function ($item) {
                return $item->created_at->toDateString();
            })
            ->collect();

        $this->dispatch('showLastMessage');
    }
}
