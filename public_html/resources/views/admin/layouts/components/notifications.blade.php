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

            // ================= FIREWORKS =================
            if (latestNotification.data.type === 'fireworks') {
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
