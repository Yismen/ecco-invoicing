<?php

namespace App\Filament\Chat\Pages;

use App\Models\Chat;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class Conversations extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.chat.pages.conversations';

    public Collection $users;
    public ?User $selectedUser = null;
    public ?Collection $messages;
    public ?string $newMessage = null;
    public string $search = '';

    public function mount()
    {
        $this->users = $this->getUsers();

        $this->selectedUser = Chat::query()
            ->where('sender_id', auth()->id())
            ->orWhere('receiver_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->first()
            ->receiver ?? null;

        $this->fetchMessages();
    }

    public function updatedSearch()
    {
        $this->users = $this->getUsers();
    }

    protected function getUsers(): Collection
    {
        return User::query()
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
    }

    private function fetchMessages()
    {
        $this->newMessage = null;

        if (!$this->selectedUser) {
            $this->messages = collect();
            return;
        }

        $this->messages = Chat::query()
            ->where(function ($query) {
                $query->where('sender_id', auth()->id())
                    ->where('receiver_id', $this->selectedUser->id);
            })->orWhere(function ($query) {
                $query->where('sender_id', $this->selectedUser->id)
                    ->where('receiver_id', auth()->id());
            })->orderBy('created_at')
            ->get();
    }
}
