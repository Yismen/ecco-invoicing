<x-filament-panels::page>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @section('title', __('filament-chat::chat.pages.conversations.title'))

    <div class="flex justify-between gap-6"  style="height: calc(100vh - 12rem);">
        {{-- Conversations --}}
        {{-- Users --}}
        <div class="flex flex-col w-1/3 gap-2">
            <div>
                <h1>Users</h1>

                <p class="text-sm text-gray-500 border-b pb-2">Manage your conversations with users.</p>
            </div>

            <input
                type="text"
                placeholder="Search users..."
                class="m-2"
                wire:model.live.debounce.500ms="search"
            />

            <ul class="mt-4 space-y-2 h-full overflow-y-auto bg-white rounded p-1">
                @foreach($users as $user)
                    <li
                        wire:click="selectUser({{ $user }})"
                        @class([
                            'cursor-pointer hover:text-gray-700 hover:bg-gray-100 flex items-center gap-2 text-gray-700 p-2 rounded',
                            'bg-primary-500 text-white font-bold' => $selectedUser && $selectedUser->id === $user->id,
                        ]) wire:key="user-{{ $user->id }}">
                        <span class="text-sm">{{ $user->name }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="flex flex-col p-4 bg-white rounded shadow flex-1 h-full">
            {{-- Selected User --}}
            {{-- Heading --}}
            <div class="flex flex-col border-b">
                @if ($selectedUser)
                    <h1>
                        {{ $selectedUser->name }}
                    </h1>
                    <p class="text-sm text-gray-500 mb-2">
                        {{ $selectedUser->email }}.
                    </p>
                @else
                    Select a user to start a conversation
                @endif
            </div>
            {{-- Messages --}}
            <div class="mt-4 h-full flex flex-col overflow-hidden" >
                {{-- Message List --}}
                <div class="flex flex-col mb-3 mt-2 justify-start items-end h-full overflow-y-auto" id="messages-list" style="scroll-behavior: smooth;">
                    @if ($selectedUser)
                        @foreach($messages as $date => $message)
                            <div class="w-full text-center my-2">
                                <span class="text-xs text-gray-500 bg-gray-200 px-2 py-1 rounded">
                                    {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}
                                </span>
                            </div>
                            @foreach($message as $msg)
                                <div
                                    @class([
                                        'max-w-xs md:max-w-md lg:max-w-lg px-4 py-2 rounded mb-2',
                                        'bg-primary-500 text-white self-end' => $msg->sender_id === auth()->id(),
                                        'bg-gray-200 text-gray-800 self-start' => $msg->sender_id !== auth()->id(),
                                    ])
                                    wire:key="message-{{ $msg->id }}"
                                >
                                    <p class="whitespace-pre-wrap">{{ $msg->message }}</p>
                                </div>
                        @endforeach
                    @endforeach
                </div>
                <div>
                    <form wire:submit.prevent="sendMessage" class="mt-6 flex ">
                        <input
                            type="text"
                            placeholder="Type your message..."
                            class="w-full p-2 border rounded"
                            wire:model.live.debounce.500ms="newMessage"
                            @if (!$selectedUser)
                                disabled
                            @endif
                        />
                        <button
                            type="submit"
                            @class([
                                'mt-4 px-4 py-2 bg-gray-200 text-gray-950 rounded hover:bg-gray-300 flex gap-2 items-center',
                                'opacity-50 cursor-not-allowed' => strlen($newMessage) < 1,
                            ])
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            @if(strlen($newMessage) < 1 )
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
                    <p class="text-sm text-gray-500 text-left self-start">Select a user to view messages.</p>
                @endif
        </div>
    </div>
    @pushOnce('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                scrollToBottom();

                Livewire.on('showLastMessage', (data) => {
                    console.log('Scrolling to bottom...');

                    scrollToBottom();
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
