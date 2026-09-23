@use(App\Services\NotificationService)

<div class="nav-item dropdown d-flex me-3">
    <a href="javascript:;" class="nav-link px-0 notification-dropdown-icon" data-bs-toggle="dropdown" tabindex="-1"
        aria-label="Show notifications">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" />
            <path d="M9 17v1a3 3 0 0 0 6 0v-1" />
        </svg>
        @php $unreadNotificationsCount = auth()->user()->unreadNotifications()->count(); @endphp
        <span class="badge bg-red unread-notification-badge text-white"
            @if ($unreadNotificationsCount <= 0) style="display:none;" @endif>{{ $unreadNotificationsCount }}</span>
    </a>
    <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end dropdown-menu-card"
        style="width: 100%; min-width: 330px; max-width: 330px;">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title notification-list-title"></h4>
            </div>
            <div class="list-group list-group-flush list-group-hoverable notification-list-group">
            </div>
        </div>
    </div>
    <div class="notification-item-container" style="display: none;">
        <div class="list-group-item py-2">
            <div class="row align-items-center">
                <div class="col-1"><span class="status-dot status-dot-animated bg-red d-block"></span>
                </div>
                <div class="col-11">
                    <a href="javascript:;" onclick="redirect('{url}');" class="text-body d-block text-truncate">{title}
                        <div class="col">
                            <div class="d-block text-secondary text-truncate mt-n1">
                                {message}
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="see-more-notifications-label-container" style="display: none;">
        <div class="list-group-item py-1">
            <a href="{{ route('admin.notifications.index') }}">
                <div class="row align-items-center">
                    <div class="col-11">
                        <span class="text-body d-block text-center text-truncate">See more</span>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Celebration Modal Overlay -->
<div id="celebration-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(12px); z-index: 10000; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.5s ease;">
    <!-- Celebration Content Card -->
    <div id="celebration-card" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 24px; width: 90%; max-width: 480px; padding: 40px 30px; text-align: center; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); transform: scale(0.8); transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1); color: #f8fafc; font-family: 'Outfit', 'Inter', sans-serif;">
        
        <!-- Glowing Avatar Container -->
        <div style="position: relative; width: 130px; height: 130px; margin: 0 auto 24px;">
            <div class="celebration-avatar-glow" style="position: absolute; top: -5px; left: -5px; right: -5px; bottom: -5px; background: linear-gradient(45deg, #00f2fe, #4facfe, #0000ff); border-radius: 50%; opacity: 0.8; filter: blur(8px); animation: pulse-glow 2s infinite alternate;"></div>
            <img id="celebration-avatar" src="" alt="User Avatar" style="position: relative; width: 100%; height: 100%; object-fit: cover; border-radius: 50%; border: 4px solid #1e293b; z-index: 2;" />
        </div>

        <!-- Success Badge -->
        <div style="display: inline-block; background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.4); color: #34d399; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; padding: 6px 16px; border-radius: 9999px; margin-bottom: 16px; box-shadow: 0 0 15px rgba(16, 185, 129, 0.2);">
            New Case Registered!
        </div>

        <!-- Success Message -->
        <h3 id="celebration-title" style="font-size: 1.5rem; font-weight: 700; margin-bottom: 8px; color: #fff; line-height: 1.3;"></h3>
        <p id="celebration-message" style="font-size: 0.95rem; color: #94a3b8; margin-bottom: 24px;"></p>

        <!-- Elegant Quote Block -->
        <div style="position: relative; background: rgba(255, 255, 255, 0.03); border-left: 4px solid #00f2fe; padding: 16px 20px; border-radius: 0 16px 16px 0; margin-bottom: 30px; text-align: left;">
            <span style="position: absolute; top: -15px; left: 10px; font-size: 4rem; color: rgba(0, 242, 254, 0.15); font-family: Georgia, serif; pointer-events: none; line-height: 1;">&ldquo;</span>
            <p id="celebration-quote" style="font-style: italic; font-size: 0.95rem; color: #e2e8f0; line-height: 1.5; margin: 0; font-family: Georgia, serif; position: relative; z-index: 1;"></p>
        </div>

        <!-- Celebration Action Button -->
        <button id="celebration-close-btn" style="background: linear-gradient(135deg, #00f2fe 0%, #4facfe 100%); color: #fff; font-size: 0.95rem; font-weight: 600; padding: 12px 36px; border: none; border-radius: 12px; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 10px 20px -5px rgba(79, 172, 254, 0.4);">
            Awesome!
        </button>

    </div>
</div>

<style>
    @keyframes pulse-glow {
        0% { transform: scale(0.98); opacity: 0.6; filter: blur(6px); }
        100% { transform: scale(1.03); opacity: 0.9; filter: blur(10px); }
    }
    #celebration-close-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px -5px rgba(79, 172, 254, 0.6);
    }
    #celebration-close-btn:active {
        transform: translateY(0);
    }
</style>

@pushonce('script')
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<script>
$(document).ready(function () {

    const limit = {{ NotificationService::NOTIFICATION_DROPDOWN_LIMIT }};
    const intervalSeconds = {{ NotificationService::NOTIFICATION_DROPDOWN_REFRESH_INTERVAL_SECONDS }};

    function toggleNotificationBadge(count) {
        if (count > 0) {
            $('.unread-notification-badge').text(count).show();
        } else {
            $('.unread-notification-badge').hide();
        }
    }

    function updateNotificationCount() {
        const url = "{{ route('admin.notifications.unread-notifications-count') }}";

        $.get(url, function (res) {
            const data = res.data || {};
            const count = data.count ?? 0;
            const latestNotification = data.latestNotification;


            toggleNotificationBadge(count);

            if (!latestNotification) return;

            const localLatestId = localStorage.getItem('latest_notification_id');
            if (localLatestId == latestNotification.id) return;

            // ================= SHOW TOAST =================
            // Play sound only for ScamStatusRegisteredNotification (type: 'fireworks')
            if (latestNotification.data.type === 'fireworks') {
                FFSound.notify(FFSound.urls.cashierUrl);
            } else {
                FFSound.notify();
            }
            new Notify({
                status: 'info',
                title: latestNotification.data.title,
                text: latestNotification.data.message,
                effect: 'slide',
                speed: 300,
                showIcon: true,
                showCloseButton: true,
                autoclose: true,
                autotimeout: 10000,
                type: 'outline',
                position: 'right top',
                sound: FFSound.urls.cashierUrl
            });

            // ================= CELEBRATION MODAL =================
            if (latestNotification.data.registration_id) {
                const causerName = latestNotification.data.causer_name || 'A user';
                const avatarUrl = latestNotification.data.causer_avatar || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(causerName) + '&background=0054a6&color=fff&size=128';
                const quoteText = latestNotification.data.causer_quote || "Keep pushing forward!";
                
                $('#celebration-avatar').attr('src', avatarUrl);
                $('#celebration-title').text(causerName);
                $('#celebration-message').text(latestNotification.data.message);
                $('#celebration-quote').text(quoteText);

                const $overlay = $('#celebration-overlay');
                const $card = $('#celebration-card');
                
                $overlay.css({ 'display': 'flex' });
                // Force reflow
                $overlay[0].offsetHeight;
                
                $overlay.css({ 'opacity': '1' });
                $card.css({ 'transform': 'scale(1)' });

                // Confetti explosion
                if (typeof confetti === 'function') {
                    confetti({
                        particleCount: 150,
                        spread: 80,
                        origin: { y: 0.6 }
                    });
                    
                    const duration = 3 * 1000;
                    const end = Date.now() + duration;
                    (function frame() {
                        confetti({
                            particleCount: 5,
                            angle: 60,
                            spread: 55,
                            origin: { x: 0 }
                        });
                        confetti({
                            particleCount: 5,
                            angle: 120,
                            spread: 55,
                            origin: { x: 1 }
                        });
                        if (Date.now() < end) {
                            requestAnimationFrame(frame);
                        }
                    })();
                }

                let autoCloseTimeout = setTimeout(closeCelebration, 12000);

                function closeCelebration() {
                    $overlay.css({ 'opacity': '0' });
                    $card.css({ 'transform': 'scale(0.8)' });
                    setTimeout(() => {
                        $overlay.css({ 'display': 'none' });
                    }, 500);
                    clearTimeout(autoCloseTimeout);
                }

                $('#celebration-close-btn').off('click').on('click', closeCelebration);
            } else if (latestNotification.data.type === 'fireworks') {
                if (typeof confetti === 'function') {
                    const duration = 4 * 1000; // 4 seconds
                    const end = Date.now() + duration;
                    (function frame() {
                        confetti({
                            particleCount: 10,
                            angle: 60,
                            spread: 80,
                            startVelocity: 60,
                            gravity: 0.9,
                            ticks: 300,
                            origin: { x: 0 }
                        });
                        confetti({
                            particleCount: 10,
                            angle: 120,
                            spread: 80,
                            startVelocity: 60,
                            gravity: 0.9,
                            ticks: 300,
                            origin: { x: 1 }
                        });
                        if (Date.now() < end) {
                            requestAnimationFrame(frame);
                        }
                    })();
                }
            }

            // ================= MARK AS READ =================
            fetch("{{ route('admin.notifications.mark-latest-read') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="_token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            });

            // ================= SAVE LAST ID =================
            localStorage.setItem('latest_notification_id', latestNotification.id);
        });
    }

    function fetchNotifications() {
        const url = "{{ route('admin.notifications.unread-notifications') }}";

        const itemTemplate = $('.notification-item-container').html();
        const seeMoreTemplate = $('.see-more-notifications-label-container').html();
        const $listGroup = $('.notification-list-group');
        const $title = $('.notification-list-title');
        const fallbackUrl = "{{ route('admin.notifications.index') }}";

        $.ajax({
            url,
            method: 'GET',
            beforeSend() {
                $listGroup.html(`<div class="d-flex justify-content-center my-5">${Loader.spinner}</div>`);
            },
            success(res) {
                const count = res.data.count;
                const notifications = res.data.notifications ?? [];

                let html = '';

                notifications.forEach(notification => {
                    const link = notification.data.link
                        ? notification.data.link + `?source=notification-${notification.id}`
                        : fallbackUrl;

                    html += itemTemplate
                        .replace('{title}', notification.data.title)
                        .replace('{message}', notification.data.message ?? '')
                        .replace('{url}', link);
                });

                if (count > limit) {
                    html += seeMoreTemplate;
                }

                $listGroup.html(html);

                $title.html(
                    notifications.length
                        ? 'Recent Notifications'
                        : `<i class="ti ti-bell-x me-1"></i> No Unread Notifications`
                );

                toggleNotificationBadge(count);
            }
        });
    }

    setInterval(updateNotificationCount, intervalSeconds * 1000);

    $('.notification-dropdown-icon').on('click', fetchNotifications);
});
</script>
@endpushonce
