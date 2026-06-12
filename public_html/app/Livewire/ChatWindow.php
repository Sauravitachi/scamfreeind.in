<?php

namespace App\Livewire;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;

class ChatWindow extends Component
{
    use WithFileUploads;
    public ?Conversation $conversation = null;
    public int $conversationId = 0;
    public $messages = [];
    public string $newMessage = '';
    public $attachment = null;
    
    public $users = [];
    public $selectedUserId = null;
    public ?User $selectedUser = null;
    public string $search = '';

    public function mount()
    {
        $this->loadUsers();
    }

    public function loadUsers()
    {
        $currentUser = Auth::user() ?? Auth::guard('admin')->user();
        if (!$currentUser) {
            $this->users = [];
            return;
        }

        $allUsers = User::where('id', '!=', $currentUser->id)
            ->when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->get();

        $conversations = Conversation::where('is_group', false)
            ->whereHas('participants', function ($query) use ($currentUser) {
                $query->where('participant_id', $currentUser->id)
                      ->where('participant_type', get_class($currentUser));
            })
            ->with(['participants', 'messages' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->get();

        $usersList = [];
        foreach ($allUsers as $user) {
            $conv = $conversations->first(function ($c) use ($user) {
                return $c->participants->contains(function ($p) use ($user) {
                    return $p->participant_id == $user->id && $p->participant_type == User::class;
                });
            });

            $lastMessage = $conv ? $conv->messages->first() : null;
            $lastMessageTime = $lastMessage ? $lastMessage->created_at->timestamp : 0;

            $unreadCount = 0;
            if ($conv) {
                $participant = $conv->participants->where('participant_id', $currentUser->id)
                    ->where('participant_type', get_class($currentUser))
                    ->first();
                
                $unreadCount = $conv->messages()
                    ->where('created_at', '>', $participant->last_read_at ?? '1970-01-01 00:00:00')
                    ->where(function($q) use ($currentUser) {
                        $q->where('sender_id', '!=', $currentUser->id)
                          ->orWhere('sender_type', '!=', get_class($currentUser));
                    })
                    ->count();
            }

            $usersList[] = [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'last_message' => $lastMessage ? $lastMessage->body : null,
                'last_message_time' => $lastMessageTime,
                'last_message_date' => $lastMessage ? $lastMessage->created_at->diffForHumans(null, true, true) : null,
                'has_conversation' => $conv ? true : false,
                'unread_count' => $unreadCount,
                'profile_avatar' => $user->profile_avatar,
            ];
        }

        // Sort by last message time descending, then those without conversations
        usort($usersList, function ($a, $b) {
            return $b['last_message_time'] <=> $a['last_message_time'];
        });

        $this->users = $usersList;
    }

    public function selectUser($userId)
    {
        $this->selectedUserId = $userId;
        $this->selectedUser = User::find($userId);
        $currentUser = Auth::user() ?? Auth::guard('admin')->user();
        
        if (!$currentUser) {
            return;
        }

        $conversation = Conversation::where('is_group', false)
            ->whereHas('participants', function ($query) use ($currentUser) {
                $query->where('participant_id', $currentUser->id)
                      ->where('participant_type', get_class($currentUser));
            })
            ->whereHas('participants', function ($query) use ($userId) {
                $query->where('participant_id', $userId)
                      ->where('participant_type', User::class);
            })
            ->first();

        if (!$conversation) {
            $selectedUser = User::find($userId);
            $conversation = Conversation::create([
                'name' => 'Chat with ' . ($selectedUser ? $selectedUser->name : 'User'),
                'is_group' => false,
            ]);

            $conversation->participants()->create([
                'participant_type' => get_class($currentUser),
                'participant_id' => $currentUser->id,
            ]);

            $conversation->participants()->create([
                'participant_type' => User::class,
                'participant_id' => $userId,
            ]);
        }

        $this->conversation = $conversation;
        $this->conversationId = $conversation->id;
        $this->markAsRead();
        $this->loadMessages();
        $this->dispatch('chat-updated');
    }

    public function markAsRead()
    {
        if (!$this->conversation) return;
        
        $currentUser = Auth::user() ?? Auth::guard('admin')->user();
        if (!$currentUser) return;

        $this->conversation->participants()
            ->where('participant_id', $currentUser->id)
            ->where('participant_type', get_class($currentUser))
            ->update(['last_read_at' => now()]);
            
        $this->loadUsers(); // Refresh counts in sidebar
    }

    public function loadMessages()
    {
        if (!$this->conversation) {
            $this->messages = [];
            return;
        }

        $this->messages = $this->conversation->messages()
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get()
            ->toArray();
    }

    #[On('echo-private:conversation.{conversationId},.message.sent')]
    public function onMessageSent($event)
    {
        if ($this->conversation && $this->conversation->id == $event['conversation_id']) {
            $this->messages[] = $event;
            $this->markAsRead(); // Mark as read since conversation is open
            $this->dispatch('chat-updated', isIncoming: true);
        } else {
            $this->loadUsers(); // Refresh sidebar counts for other conversations
            $this->dispatch('chat-updated', isIncoming: true);
        }
    }

    #[On('echo-private:conversation.{conversationId},.call.signal')]
    public function onCallSignal($event)
    {
        $this->dispatch('incoming-call', data: $event['data']);
    }

    public function startCall($type = 'video')
    {
        if (!$this->conversation) return;

        $user = Auth::user() ?? Auth::guard('admin')->user();
        $roomName = 'NexusCall_' . $this->conversation->id . '_' . time();

        $data = [
            'type' => $type,
            'roomName' => $roomName,
            'callerName' => $user->name,
            'callerAvatar' => $user->profile_avatar,
            'conversation_id' => $this->conversation->id,
            'action' => 'offer'
        ];

        broadcast(new \App\Events\CallSignal($data))->toOthers();
        $this->dispatch('outgoing-call', data: $data);
    }

    public function sendMessage()
    {
        if (!$this->conversation) {
            return;
        }

        $this->validate([
            'newMessage' => 'required_without:attachment|string|max:1000',
            'attachment' => 'nullable|file|max:10240', // 10MB max
        ]);

        $user = Auth::user() ?? Auth::guard('admin')->user(); 
        if (!$user) {
            return;
        }
        
        $type = 'text';
        $body = $this->newMessage;

        if ($this->attachment) {
            $path = $this->attachment->store('chat-attachments', 'public');
            $extension = $this->attachment->getClientOriginalExtension();
            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            if (in_array(strtolower($extension), $imageExtensions)) {
                $type = 'image';
            } else {
                $type = 'file';
            }
            $body = $path;
        }

        $message = Message::create([
            'conversation_id' => $this->conversation->id,
            'sender_type' => get_class($user),
            'sender_id' => $user->id,
            'body' => $body,
            'type' => $type,
        ]);
        
        $this->newMessage = '';
        $this->attachment = null;
        $this->messages[] = $message->toArray();
        
        try {
            broadcast(new MessageSent($message))->toOthers();
        } catch (\Exception $e) {
            report($e);
        }

        $this->dispatch('chat-updated', isIncoming: false);
        $this->loadUsers(); // Refresh sidebar with latest message
    }

    public function sendSticker($sticker)
    {
        if (!$this->conversation) return;

        $user = Auth::user() ?? Auth::guard('admin')->user(); 
        if (!$user) return;

        $message = Message::create([
            'conversation_id' => $this->conversation->id,
            'sender_type' => get_class($user),
            'sender_id' => $user->id,
            'body' => $sticker,
            'type' => 'sticker',
        ]);

        $this->messages[] = $message->toArray();

        try {
            broadcast(new MessageSent($message))->toOthers();
        } catch (\Exception $e) {
            report($e);
        }

        $this->dispatch('chat-updated', isIncoming: false);
        $this->loadUsers();
    }

    public function render()
    {
        return view('livewire.chat-window');
    }
}
