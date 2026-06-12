<div class="chat-wrapper-cool" wire:poll.15s>
    <script src="https://meet.jit.si/external_api.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

        :root {
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --accent-primary: #8b5cf6;
            --accent-secondary: #d946ef;
            --bg-dark: #0f172a;
            --text-bright: #f8fafc;
            --text-dim: #94a3b8;
            --mine-gradient: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
            --theirs-bg: rgba(30, 41, 59, 0.7);
        }

        .chat-wrapper-cool {
            display: flex;
            height: 800px;
            width: 100%;
            background: var(--bg-dark);
            border-radius: 2rem;
            overflow: hidden;
            border: 1px solid var(--glass-border);
            font-family: 'Outfit', sans-serif;
            color: var(--text-bright);
            position: relative;
        }

        .chat-wrapper-cool::before {
            content: '';
            position: absolute;
            top: -20%;
            left: -10%;
            width: 50%;
            height: 50%;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.15) 0%, transparent 70%);
            z-index: 0;
            pointer-events: none;
        }

        /* Sidebar Styling */
        .sidebar-cool {
            width: 380px;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(20px);
            border-right: 1px solid var(--glass-border);
            display: flex;
            flex-direction: column;
            z-index: 10;
        }

        .sidebar-header-cool {
            padding: 2rem 1.5rem;
        }

        .sidebar-header-cool h5 {
            font-size: 1.75rem;
            font-weight: 700;
            background: linear-gradient(to right, #fff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1.5rem;
        }

        .search-box-cool {
            position: relative;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: 1rem;
            padding: 0.75rem 1rem 0.75rem 3rem;
            transition: all 0.3s ease;
        }

        .search-box-cool:focus-within {
            background: rgba(255, 255, 255, 0.07);
            border-color: var(--accent-primary);
            box-shadow: 0 0 20px rgba(139, 92, 246, 0.2);
        }

        .search-input-cool {
            background: transparent;
            border: none;
            color: white;
            width: 100%;
            outline: none;
            font-size: 0.95rem;
        }

        .search-icon-cool {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-dim);
        }

        .user-list-cool {
            flex-grow: 1;
            overflow-y: auto;
            padding: 0 1rem 1rem;
        }

        .user-item-cool {
            display: flex;
            align-items: center;
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-radius: 1.25rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid transparent;
        }

        .user-item-cool:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        .user-item-cool.active {
            background: rgba(139, 92, 246, 0.1);
            border-color: rgba(139, 92, 246, 0.3);
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.2);
        }

        .avatar-cool {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--mine-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-right: 1.25rem;
            font-size: 1.4rem;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            flex-shrink: 0;
        }

        .user-info-cool {
            flex-grow: 1;
            min-width: 0;
        }

        .user-name-cool {
            font-weight: 600;
            font-size: 1.05rem;
            margin-bottom: 0.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .last-msg-cool {
            color: var(--text-dim);
            font-size: 0.875rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .time-cool {
            font-size: 0.75rem;
            color: var(--text-dim);
        }

        .unread-badge-cool {
            background: #ef4444;
            color: white;
            min-width: 22px;
            height: 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.4);
        }

        /* Calling UI */
        .call-overlay-cool {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(20px);
            z-index: 2000;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            border-radius: 2rem;
        }

        .call-avatar-pulse {
            width: 120px;
            height: 120px;
            background: var(--mine-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin-bottom: 2rem;
            position: relative;
        }

        .call-avatar-pulse::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: var(--accent-primary);
            opacity: 0.4;
            animation: pulse-ring 1.5s infinite;
        }

        @keyframes pulse-ring {
            0% { transform: scale(0.8); opacity: 0.5; }
            100% { transform: scale(1.5); opacity: 0; }
        }

        .call-actions-cool {
            display: flex;
            gap: 2rem;
            margin-top: 3rem;
        }

        .call-btn-cool {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-accept { background: #10b981; color: white; }
        .btn-decline { background: #ef4444; color: white; }
        .btn-accept:hover { transform: scale(1.1); box-shadow: 0 0 20px rgba(16, 185, 129, 0.4); }
        .btn-decline:hover { transform: scale(1.1); box-shadow: 0 0 20px rgba(239, 68, 68, 0.4); }

        #jitsi-container {
            width: 100%;
            height: 100%;
            border-radius: 2rem;
            overflow: hidden;
        }

        /* Main Chat Area */
        .chat-main-cool {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background: radial-gradient(circle at top right, rgba(139, 92, 246, 0.05), transparent);
            z-index: 5;
        }

        .header-cool {
            padding: 1.5rem 2rem;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .active-user-info {
            display: flex;
            align-items: center;
        }

        .status-dot {
            width: 10px;
            height: 10px;
            background: #22c55e;
            border-radius: 50%;
            margin-right: 0.5rem;
            box-shadow: 0 0 10px #22c55e;
        }

        .messages-cool {
            flex-grow: 1;
            padding: 2rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            scroll-behavior: smooth;
        }

        .msg-row-cool {
            display: flex;
            flex-direction: column;
            max-width: 75%;
            animation: slideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .msg-row-cool.mine {
            align-self: flex-end;
            align-items: flex-end;
        }

        .msg-row-cool.theirs {
            align-self: flex-start;
            align-items: flex-start;
        }

        .bubble-cool {
            padding: 1rem 1.25rem;
            border-radius: 1.5rem;
            font-size: 0.95rem;
            line-height: 1.6;
            position: relative;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .mine .bubble-cool {
            background: var(--mine-gradient);
            color: white;
            border-bottom-right-radius: 0.25rem;
        }

        .theirs .bubble-cool {
            background: var(--theirs-bg);
            backdrop-filter: blur(5px);
            border: 1px solid var(--glass-border);
            color: var(--text-bright);
            border-bottom-left-radius: 0.25rem;
        }

        .msg-image-cool {
            max-width: 100%;
            border-radius: 1rem;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .msg-image-cool:hover {
            transform: scale(1.02);
        }

        .file-attachment-cool {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.05);
            padding: 0.75rem 1rem;
            border-radius: 1rem;
            gap: 0.75rem;
            color: white;
            text-decoration: none;
            border: 1px solid var(--glass-border);
        }

        /* Input Area */
        .input-area-cool {
            padding: 2rem;
            background: transparent;
        }

        .input-glass-cool {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 1.5rem;
            padding: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .input-cool {
            flex-grow: 1;
            background: transparent;
            border: none;
            color: white;
            padding: 0.75rem 1rem;
            outline: none;
            font-size: 1rem;
        }

        .action-btn-cool {
            width: 44px;
            height: 44px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            color: var(--text-dim);
            background: transparent;
            border: none;
        }

        .action-btn-cool:hover {
            background: rgba(255, 255, 255, 0.07);
            color: white;
            transform: translateY(-2px);
        }

        .send-btn-cool {
            background: var(--mine-gradient);
            color: white !important;
            box-shadow: 0 8px 16px rgba(139, 92, 246, 0.3);
        }

        .send-btn-cool:hover {
            background: var(--mine-gradient);
            box-shadow: 0 12px 20px rgba(139, 92, 246, 0.4);
        }

        .attachment-preview {
            position: absolute;
            bottom: 100%;
            left: 2rem;
            background: rgba(30, 41, 59, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 1rem;
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 100;
        }

        /* Scrollbar */
        .sticker-picker-cool {
            position: absolute;
            bottom: 100%;
            left: 0;
            background: rgba(30, 41, 59, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 1.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            width: 250px;
            z-index: 1000;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        }

        .sticker-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.75rem;
        }

        .sticker-item {
            font-size: 1.75rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease;
            padding: 0.5rem;
            border-radius: 0.75rem;
        }

        .sticker-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: scale(1.2) rotate(5deg);
        }

        .bubble-cool.sticker {
            background: transparent !important;
            box-shadow: none !important;
            font-size: 4rem;
            padding: 0 !important;
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            text-align: center;
            padding: 3rem;
        }

        .empty-state-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 2rem;
            margin-bottom: 2rem;
            transition: all 0.5s ease;
        }
    </style>

    <!-- Sidebar -->
    <div class="sidebar-cool">
        <div class="sidebar-header-cool p-4">
            <h5>ScamFree India Chat</h5>
            <div class="search-box-cool">
                <span class="search-icon-cool">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </span>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    class="search-input-cool" 
                    placeholder="Search nexus..."
                >
            </div>
        </div>
        <div class="user-list-cool">
            @forelse($users as $user)
                <div 
                    wire:click="selectUser({{ $user['id'] }})"
                    class="user-item-cool {{ $selectedUserId === $user['id'] ? 'active' : '' }}"
                    wire:key="user-{{ $user['id'] }}"
                >
                    <div class="avatar-cool" @if(!empty($user['profile_avatar'])) style="background-image: url('{{ $user['profile_avatar'] }}'); background-size: cover; background-position: center; color: transparent;" @endif>
                        @if(empty($user['profile_avatar']))
                            {{ substr($user['name'] ?? 'U', 0, 1) }}
                        @endif
                    </div>
                    <div class="user-info-cool">
                        <div class="user-name-cool">
                            <span>{{ $user['name'] }}</span>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                @if(isset($user['unread_count']) && $user['unread_count'] > 0)
                                    <span class="unread-badge-cool">{{ $user['unread_count'] }}</span>
                                @endif
                                <span class="time-cool">{{ $user['last_message_date'] }}</span>
                            </div>
                        </div>
                        <div class="last-msg-cool">
                            {{ $user['last_message'] ?? 'No transmissions yet' }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center p-4 text-dim">No signals found</div>
            @endforelse
        </div>
    </div>

    <!-- Main Chat -->
    <div class="chat-main-cool">
        @if($conversation)
            <div class="header-cool">
                <div class="active-user-info">
                    <div class="avatar-cool" style="width: 48px; height: 48px; font-size: 1.1rem; margin-right: 1rem; @if($selectedUser && !empty($selectedUser->profile_avatar)) background-image: url('{{ $selectedUser->profile_avatar }}'); background-size: cover; background-position: center; color: transparent; @endif">
                        @if(!$selectedUser || empty($selectedUser->profile_avatar))
                            {{ substr($conversation->name ?? 'C', 0, 1) }}
                        @endif
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 1.1rem;">{{ $conversation->name }}</div>
                        <div class="status-dot-container" style="display: flex; align-items: center; font-size: 0.8rem; color: var(--text-dim); margin-top: 2px;">
                            <span class="status-dot"></span> Signal Active
                        </div>
                    </div>
                </div>
                <div class="header-actions" style="display: flex; gap: 0.5rem;">
                    <button class="action-btn-cool" wire:click="startCall('voice')"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l2.28-2.28a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg></button>
                    <button class="action-btn-cool" wire:click="startCall('video')"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg></button>
                </div>
            </div>

            <div class="messages-cool" id="chat-messages-container">
                @forelse($messages as $msg)
                    @php
                        $currentUser = Auth::user() ?? Auth::guard('admin')->user();
                        $isMine = isset($currentUser) && $msg['sender_id'] == $currentUser->id && $msg['sender_type'] == get_class($currentUser);
                    @endphp
                    
                    <div class="msg-row-cool {{ $isMine ? 'mine' : 'theirs' }}" wire:key="msg-{{ $msg['id'] ?? $loop->index }}">
                        <div class="bubble-cool {{ isset($msg['type']) && $msg['type'] === 'sticker' ? 'sticker' : '' }}">
                            @if(isset($msg['type']) && $msg['type'] === 'image')
                                <img src="{{ asset('storage/' . $msg['body']) }}" class="msg-image-cool" onclick="window.open(this.src)">
                            @elseif(isset($msg['type']) && $msg['type'] === 'sticker')
                                {{ $msg['body'] }}
                            @elseif(isset($msg['type']) && $msg['type'] === 'file')
                                <a href="{{ asset('storage/' . $msg['body']) }}" target="_blank" class="file-attachment-cool">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>
                                    <span>Download Resource</span>
                                </a>
                            @else
                                {{ $msg['body'] }}
                            @endif
                            
                            @if(!isset($msg['type']) || $msg['type'] !== 'sticker')
                                <div class="time-cool" style="margin-top: 0.5rem; text-align: {{ $isMine ? 'right' : 'left' }}; opacity: 0.6;">
                                    {{ \Carbon\Carbon::parse($msg['created_at'])->format('h:i A') }}
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center p-5 text-dim">Initialize transmission...</div>
                @endforelse
            </div>

            <div class="input-area-cool" style="position: relative;">
                @if($attachment)
                    <div class="attachment-preview">
                        @if(in_array($attachment->getClientOriginalExtension(), ['jpg', 'jpeg', 'png', 'gif']))
                            <img src="{{ $attachment->temporaryUrl() }}" style="width: 50px; height: 50px; border-radius: 0.5rem; object-fit: cover;">
                        @else
                            <div style="width: 50px; height: 50px; background: rgba(255,255,255,0.1); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>
                            </div>
                        @endif
                        <div style="flex-grow: 1;">
                            <div style="font-size: 0.8rem; font-weight: 500;">{{ $attachment->getClientOriginalName() }}</div>
                            <div style="font-size: 0.7rem; color: var(--text-dim);">Ready for uplink</div>
                        </div>
                        <button class="action-btn-cool" wire:click="$set('attachment', null)"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
                    </div>
                @endif

                <form wire:submit.prevent="sendMessage" class="m-0">
                    <div class="input-glass-cool">
                        <input type="file" id="file-upload" wire:model="attachment" style="display: none;">
                        <button type="button" class="action-btn-cool" onclick="document.getElementById('file-upload').click()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                        </button>
                        <div style="position: relative;" x-data="{ open: false }">
                            <button type="button" class="action-btn-cool" @click="open = !open">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M8 14s1.5 2 4 2 4-2 4-2"></path><line x1="9" y1="9" x2="9.01" y2="9"></line><line x1="15" y1="9" x2="15.01" y2="9"></line></svg>
                            </button>
                            <div x-show="open" @click.away="open = false" class="sticker-picker-cool" style="display: none;" x-transition>
                                <div class="sticker-grid">
                                    @php
                                        $stickers = ['🔥', '🚀', '✨', '💎', '🎨', '🎮', '🍕', '🌈', '⚡', '🤖', '🎉', '🌟'];
                                    @endphp
                                    @foreach($stickers as $sticker)
                                        <div class="sticker-item" wire:click="sendSticker('{{ $sticker }}')" @click="open = false">
                                            {{ $sticker }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <input 
                            wire:model="newMessage" 
                            type="text" 
                            class="input-cool" 
                            placeholder="Type a transmission..."
                            autocomplete="off"
                        >
                        <button 
                            type="submit" 
                            class="action-btn-cool send-btn-cool"
                            wire:loading.attr="disabled"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-icon" style="width: 150px; height: 150px; background: rgba(139, 92, 246, 0.05);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent-primary);"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                </div>
                <h5>Neural Link Ready</h5>
                <p>Establish a connection via the sidebar to begin secure transmissions.</p>
            </div>
        @endif
    </div>

    <div 
        x-data="{ 
            calling: false, 
            incoming: false, 
            active: false, 
            callData: null,
            jitsi: null,
            initCall(data, isOutgoing) {
                this.callData = data;
                if (isOutgoing) {
                    this.calling = true;
                } else {
                    this.incoming = true;
                }
            },
            acceptCall() {
                this.incoming = false;
                this.active = true;
                this.$nextTick(() => this.setupJitsi());
            },
            setupJitsi() {
                const domain = 'meet.jit.si';
                const options = {
                    roomName: this.callData.roomName,
                    width: '100%',
                    height: '100%',
                    parentNode: document.querySelector('#jitsi-container'),
                    userInfo: {
                        displayName: '{{ Auth::user()->name ?? 'Admin' }}'
                    },
                    configOverwrite: { prejoinPageEnabled: false },
                    interfaceConfigOverwrite: {
                        TOOLBAR_BUTTONS: ['microphone', 'camera', 'closedcaptions', 'desktop', 'fullscreen', 'fittowindow', 'hangup', 'profile', 'chat', 'recording', 'livestreaming', 'etherpad', 'sharedvideo', 'settings', 'raisehand', 'videoquality', 'filmstrip', 'invite', 'feedback', 'stats', 'shortcuts', 'tileview', 'videobackgroundblur', 'download', 'help', 'mute-everyone', 'security']
                    }
                };
                this.jitsi = new JitsiMeetExternalAPI(domain, options);
                this.jitsi.addEventListener('videoConferenceLeft', () => this.endCall());
            },
            endCall() {
                if (this.jitsi) this.jitsi.dispose();
                this.calling = false;
                this.incoming = false;
                this.active = false;
                this.jitsi = null;
            }
        }"
        @outgoing-call.window="initCall($event.detail.data, true)"
        @incoming-call.window="initCall($event.detail.data, false)"
        class="call-wrapper"
    >
        <!-- Outgoing Call Overlay -->
        <template x-if="calling">
            <div class="call-overlay-cool">
                <div class="call-avatar-pulse" @if($selectedUser && !empty($selectedUser->profile_avatar)) style="background-image: url('{{ $selectedUser->profile_avatar }}'); background-size: cover; background-position: center; font-size: 0;" @endif>
                    @if(!$selectedUser || empty($selectedUser->profile_avatar))
                        {{ substr($conversation->name ?? 'C', 0, 1) }}
                    @endif
                </div>
                <h2 x-text="'Calling ' + '{{ $conversation->name ?? 'User' }}' + '...'"></h2>
                <p>Initiating secure Nexus link</p>
                <div class="call-actions-cool">
                    <button class="call-btn-cool btn-decline" @click="endCall()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.68 13.31a16 16 0 0 0 3.41 2.6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7 2 2 0 0 1 1.72 2v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91"></path><line x1="23" y1="1" x2="1" y2="23"></line></svg>
                    </button>
                    <button class="call-btn-cool btn-accept" @click="acceptCall()">
                         <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23 7 16 12 23 17 23 7"></polygon><rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>
                    </button>
                </div>
            </div>
        </template>

        <!-- Incoming Call Overlay -->
        <template x-if="incoming">
            <div class="call-overlay-cool">
                <div class="call-avatar-pulse" :style="callData && callData.callerAvatar ? 'background-image: url(' + callData.callerAvatar + '); background-size: cover; background-position: center;' : ''">
                    <template x-if="!callData || !callData.callerAvatar">
                        <span x-text="callData ? callData.callerName.charAt(0) : '?'"></span>
                    </template>
                </div>
                <h2 x-text="callData ? callData.callerName + ' is calling...' : 'Incoming Call...'"></h2>
                <p>Secure transmission request</p>
                <div class="call-actions-cool">
                    <button class="call-btn-cool btn-decline" @click="endCall()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.68 13.31a16 16 0 0 0 3.41 2.6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7 2 2 0 0 1 1.72 2v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91"></path><line x1="23" y1="1" x2="1" y2="23"></line></svg>
                    </button>
                    <button class="call-btn-cool btn-accept" @click="acceptCall()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l2.28-2.28a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                    </button>
                </div>
            </div>
        </template>

        <!-- Active Call Modal -->
        <template x-if="active">
            <div class="call-overlay-cool" style="padding: 1rem;">
                <div id="jitsi-container"></div>
                <button class="call-btn-cool btn-decline" style="position: absolute; bottom: 2rem; left: 50%; transform: translateX(-50%); z-index: 2001;" @click="endCall()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.68 13.31a16 16 0 0 0 3.41 2.6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7 2 2 0 0 1 1.72 2v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91"></path><line x1="23" y1="1" x2="1" y2="23"></line></svg>
                </button>
            </div>
        </template>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            const scrollToBottom = () => { 
                const container = document.getElementById('chat-messages-container');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            };

            Livewire.on('chat-updated', (data) => {
                setTimeout(scrollToBottom, 50);
                
                const isIncoming = data && data.isIncoming;

                if (isIncoming) {
                    if (typeof FFSound !== 'undefined') {
                        FFSound.notify();
                    }
                    if (typeof Notify !== 'undefined') {
                        new Notify({
                            status: 'info',
                            title: 'New Transmission',
                            text: 'Incoming signal received in Nexus.',
                            effect: 'slide',
                            speed: 300,
                            showIcon: true,
                            showCloseButton: true,
                            autoclose: true,
                            autotimeout: 5000,
                            type: 'outline',
                            position: 'right top',
                        });
                    }
                }
            });
            
            Livewire.hook('morph.updated', ({ component, el }) => {
                if (el.id === 'chat-messages-container') {
                    scrollToBottom();
                }
            });
            
            scrollToBottom();
        });
    </script>
</div>



