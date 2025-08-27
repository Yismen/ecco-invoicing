<x-filament-panels::page>
    @section('title', __('filament-chat::chat.pages.conversations.title'))

    <div class="flex justify-between gap-6"  style="height: calc(100vh - 12rem);">
        {{-- Conversations --}}
        {{-- Users --}}
        <div class="flex flex-col w-[27%] gap-2">
            <div>
                <h1>Users</h1>

                <p class="pb-2 text-sm text-gray-500 border-b">Manage your conversations with users.</p>
            </div>

            <div class="relative flex flex-row items-center justify-between m-2">
                <input
                    type="text"
                    placeholder="Search users..."
                    class="relative w-full h-8 rounded imput "
                    wire:model.live.debounce.500ms="search"
                />
                @if (strlen($search) > 1)
                    <button class="absolute right-[15px] p-2 " title="Clear Search" wire:click="$set('search', '')">X</button>
                @endif
            </div>

            <ul class="h-full p-1 mt-4 space-y-2 overflow-y-auto bg-white rounded">
                @foreach($users as $user)
                    <li
                        wire:click="selectUser({{ $user }})"
                        @class([
                            'cursor-pointer hover:text-gray-700 hover:bg-gray-100 flex items-center gap-2 text-gray-700 p-2 rounded text-sm',
                            'bg-primary-500 text-white font-bold' => $selectedUser && $selectedUser->id === $user->id,
                        ]) wire:key="user-{{ $user->id }}">
                            {{ $user->name }}

                            @php
                                $unreadChatsCount = $user->unreadChatsCountForUser(auth()->user());
                            @endphp
                        <span class="">
                            @if ($unreadChatsCount > 0)
                                <span class="px-2 py-1 ml-2 text-xs font-medium text-white bg-green-500 rounded-full">{{ $unreadChatsCount }}</span>
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="flex flex-col flex-1 h-full p-4 bg-white rounded shadow">
            {{-- Selected User --}}
            {{-- Heading --}}
            <div class="flex flex-row justify-between border-b">
                @if ($selectedUser)
                   <div>
                        <h1 class="flex justify-between">
                            {{ $selectedUser->name }}
                        </h1>
                        <p class="mb-2 text-sm text-gray-500">
                        {{ $selectedUser->email }}.
                        </p>
                    </div>
                    @if ($messages->count())
                        <div title="Delete Conversation">{{ $this->deleteConversationAction }}</div>
                    @endif
                @else
                    Select a user to start a conversation
                @endif
            </div>
            {{-- Messages --}}
            <div class="flex flex-col h-full mt-4 overflow-hidden" >
                {{-- Message List --}}
                <div class="flex flex-col items-end justify-start h-full mt-2 mb-3 overflow-y-auto" id="messages-list" style="scroll-behavior: smooth;">
                    @if ($selectedUser)
                        @foreach($messages as $date => $message)
                            <div class="w-full my-2 text-center">
                                <span class="px-2 py-1 text-xs text-gray-500 bg-gray-200 rounded">
                                    {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}
                                </span>
                            </div>
                            @foreach($message as $msg)
                                <div
                                    @class([
                                        'max-w-[75%] px-2 py-1 text-xs rounded mb-2 relative',
                                        'bg-primary-500 text-white self-end' => $msg->sender_id === auth()->id(),
                                        'bg-gray-200 text-gray-800 self-start' => $msg->sender_id !== auth()->id(),
                                    ])
                                    wire:key="message-{{ $msg->id }}"
                                >
                                    <p class="whitespace-pre-wrap">{{ $msg->message }}</p>
                                    @if ($msg->sender_id === auth()->id())
                                        <span class="absolute top-[-1rem] right-[3px] rounded text-gray-800 hover:text-gray-900 hover:rotate-90" title="Delete Message">
                                            {{ ($this->deleteMessageAction)(['chat' => $msg->id]) }}
                                        </span>
                                    @endif
                                </div>
                        @endforeach
                    @endforeach
                </div>
                <div>
                    <div class="w-full text-xs italic text-gray-700 " id="typing-indicator"></div>

                    <form wire:submit.prevent="sendMessage" class="flex mt-6 ">
                        <input
                            type="text"
                            placeholder="Type your message..."
                            class="w-full p-2 border rounded"
                            wire:model.live.debounce.500ms="newChat"
                            @if (!$selectedUser)
                                disabled
                            @endif
                        />
                        <button
                            type="submit"
                            @class([
                                'mt-4 px-4 py-2 bg-gray-200 text-gray-950 rounded hover:bg-gray-300 flex gap-2 items-center',
                                'opacity-50 cursor-not-allowed' => strlen($newChat) < 1,
                            ])
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            @if(strlen($newChat) < 1 )
                                disabled
                            @endif
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send" viewBox="0 0 16 16">
                                <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576zm6.787-8.201L1.591 6.602l4.339 2.76z"/>
                            </svg>
                            Send
                        </button>
                    </form>
                @else
                    <p class="self-start text-sm text-left text-gray-500">Select a user to view messages.</p>
                @endif
            </div>
        <x-filament-actions::modals />
    </div>
    @pushOnce('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                scrollToBottom();

                Livewire.on('showLastMessage', (data) => {
                    scrollToBottom();
                });

                Livewire.on('userTyping', (event) => {
                    window.Echo.private(`chats.${event.receiverId}`)
                        .whisper('typing', {
                            userId: event.authId,
                            userName: event.userName
                        });
                });

                window.Echo.private(`chats.{{ auth()->id() }}`)
                    .listenForWhisper('typing', (event) => {
                        var typingIndicator = document.getElementById('typing-indicator');

                        typingIndicator.innerText = `${event.userName} is typing...`;

                        setTimeout(() => {
                            typingIndicator.innerText = '';
                        }, 2000);
                    });
            });

            function scrollToBottom() {
                setTimeout(() => {
                    const messagesList = document.getElementById('messages-list');
                    if (messagesList) {
                        messagesList.scrollTop = messagesList.scrollHeight;
                    }
                }, 300);
            }
        </script>
    @endPushOnce
</x-filament-panels::page>
