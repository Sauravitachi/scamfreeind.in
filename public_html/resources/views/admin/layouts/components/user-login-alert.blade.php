@if (session()->has('user_login'))
    @php
        $currentUser = auth()->user();
        $loginBackAnchor = '<a href="#" onclick="loginBackToMyAccount();">Click here</a>';
        $message = "You are currently logged in as <strong>{$currentUser->name} ({$currentUser->username})</strong> account. {$loginBackAnchor} to head back to your account.";
    @endphp

    <x-admin.alert variant='warning' :message='$message' />

    <form action="{{ route('admin.users.login-back-to-user') }}" method="POST" id="loginBackForm">
        @csrf
    </form>

    @push('script')
        <script>
            function loginBackToMyAccount() {
                $('#loginBackForm').submit();
            }
        </script>
    @endpush
@endif