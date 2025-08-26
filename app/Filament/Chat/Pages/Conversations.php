<?php

namespace App\Filament\Chat\Pages;

use App\Models\Chat;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Support\Colors\Color;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Filament\Support\Enums\ActionSize;
use Filament\Notifications\Notification;

class Conversations extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.chat.pages.conversations';

    protected static ?string $title = 'Chats';

    public Collection $users;
    public ?User $selectedUser = null;
    public ?Collection $messages;
    public ?string $newChat = null;
    public string $search = '';
    public ?int $authId;

    public function mount()
    {
        $this->getUsers();

        $this->fetchMessages();

        $this->authId = auth()->id();
    }

    public function getListeners(): array
    {
        return [
            "echo-private:chats." . auth()->id() . ",ChatMessageSent" => 'newChatReceived',
            "echo-private:chats." . auth()->id() . ",ChatMessageDeleted" => 'messageDeleted',
        ];
    }

    public function updatedSearch()
    {
        $this->getUsers();
    }

    public function updatednewChat($value)
    {
        $this->dispatch(
            'userTyping',
            authId: $this->authId,
            userName: auth()->user()->name,
            receiverId: $this->selectedUser->id
        );
    }

    public function selectUser(User $user)
    {
        if ($this->selectedUser?->id !== $user->id) {
            $this->selectedUser = $user;
            $this->fetchMessages();
        }

    }

    public function sendMessage()
    {
        if (empty($this->newChat) || !$this->selectedUser) {
            return;
        }

        $newChat = Chat::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $this->selectedUser->id,
            'message' => $this->newChat,
            'sent_at' => now(),
        ]);

        $this->newChat = null;

        $this->pushNewMessage($newChat);

        broadcast(new \App\Events\ChatMessageSent($newChat))
            ->toOthers();

        $this->dispatch('showLastMessage');
    }

    public function deleteConversationAction(): Action
    {
        return Action::make('deleteConversation')
            ->label('X')
            ->requiresConfirmation()
            ->modalHeading('Delete Conversation')
            ->modalDescription("Are you sure you'd like to delete your conversation with {$this->selectedUser->name}? This action cannot be undone.")
            ->color(Color::Rose)
            ->modalSubmitActionLabel('Yes, delete it!')
            ->action(function() {
                $this->messages->flatten()->each->delete();

                Cache::forget($this->getCacheKey());

                $this->fetchMessages();

                Notification::make()
                    ->title("Your conversation with {$this->selectedUser->name} has been deleted!")
                    ->danger()
                    ->send();

                broadcast(new \App\Events\ChatMessageDeleted(receiver_id: $this->selectedUser->id))
                    ->toOthers();
            });
    }

    public function deleteMessageAction(): Action
    {
        return Action::make('deleteMessage')
            ->label('X')
            ->modalHeading('Delete Message')
            ->modalDescription("Are you sure you'd like to delete this message?")
            ->color(Color::Red)
            ->size(ActionSize::ExtraSmall)
            ->extraAttributes([
                'class' => 'mx-auto my-8 bg-transparent text-gray-800',
            ])
            ->requiresConfirmation()

            ->action(function (array $arguments) {
                $chat = Chat::find($arguments['chat']);

                $chat?->delete();

                Cache::forget($this->getCacheKey());

                $this->fetchMessages();

                Notification::make()
                    ->title("Message {$chat->message} deleted!")
                    ->danger()
                    ->send();

                broadcast(new \App\Events\ChatMessageDeleted(receiver_id: $this->selectedUser->id))
                    ->toOthers();
            });

    }

    public function newChatReceived($event)
    {
        $chat = $event['chat'];

        $newChat = Chat::with('sender', 'receiver')->find($chat['id']);

        if ($this->selectedUser && $newChat->sender_id === $this->selectedUser->id) {
            $this->pushNewMessage($newChat);

            $newChat->update(['read_at' => now()]);
        }

        $this->dispatch('showLastMessage');

        Notification::make()
            ->title("{$newChat->sender->name} sent you a new chat message!")
            ->info()
            ->send();

        $this->getUsers();
    }

    public function messageDeleted($event)
    {
        Notification::make()
            ->title("{$event['user']['name']} deleted a chat!")
            ->danger()
            ->send();

        if ($this->selectedUser && $event['user']['id'] === $this->selectedUser->id) {
            Cache::forget($this->getCacheKey());

            $this->fetchMessages();
        }
    }

    private function fetchMessages()
    {
        $this->newChat = null;

        if (!$this->selectedUser) {
            $this->messages = collect();
            return;
        }

        $myUnreadMessagesFromCurrentUser = Chat::query()
            ->unread()
            ->where('receiver_id', auth()->id())
            ->where('sender_id', $this->selectedUser->id)
            ->get();

        $myUnreadMessagesFromCurrentUser
            ->each(fn(Chat $chat) => $chat->update([
                'read_at' => now(),
            ]));

        $this->messages = $this->selectedUser
            ? Cache::remember(
                $this->getCacheKey(),
                now()->addMinutes(25),
                fn() => $this->getChatsConversation()
            )
            : $this->getChatsConversation();

        $this->dispatch('showLastMessage');
    }

    private function pushNewMessage(Chat $newChat)
    {
        Cache::forget($this->getCacheKey());

        $formatedDate = now()->toDateString();
        $latestMessage = $this->messages->get($formatedDate);

        $latestMessage
            ? $latestMessage->push($newChat)
            : $this->messages[$formatedDate] = collect([$newChat]);
    }

    private function getCacheKey(): string
    {
        $owners = [$this->selectedUser?->id, auth()->id()];

        sort($owners);

        return join("-", [
            'conversations-for-user',
            $owners[0],
            'with',
            $owners[1],
        ]);
    }

    private function getChatsConversation(): Collection
    {
        return Chat::query()
            ->with(['sender', 'receiver'])
            ->where(function ($query) {
                $query->where('sender_id', auth()->id())
                    ->where('receiver_id', $this->selectedUser->id);
            })->orWhere(function ($query) {
                $query->where('sender_id', $this->selectedUser->id)
                    ->where('receiver_id', auth()->id());
            })
            ->orderBy('sent_at', 'asc')
            ->get()
            ->groupBy(function ($item) {
                return $item->sent_at->toDateString();
            })
            ->collect();

    }

    private function getUsers()
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
}
