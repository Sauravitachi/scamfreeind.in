@use(App\Constants\Permission)

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>403 - Unauthorized | {{ config('settings.brand_name') }}</title>

    <style>
        @import url("https://fonts.googleapis.com/css?family=Lato");

        * {
            position: relative;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Lato", sans-serif;
        }

        body {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: linear-gradient(to bottom right, #EEE, #AAA);
        }

        .message {
            text-align: center;
        }

        h1 {
            margin: 40px 0 20px;
        }

        .lock {
            border-radius: 5px;
            width: 55px;
            height: 45px;
            background-color: #333;
            animation: dip 1s;
            animation-delay: 1.5s;
        }

        .lock::before,
        .lock::after {
            content: "";
            position: absolute;
            border-left: 5px solid #333;
            height: 20px;
            width: 15px;
            left: calc(50% - 12.5px);
        }

        .lock::before {
            top: -30px;
            border: 5px solid #333;
            border-bottom-color: transparent;
            border-radius: 15px 15px 0 0;
            height: 30px;
            animation: lock 2s, spin 2s;
        }

        .lock::after {
            top: -10px;
            border-right: 5px solid transparent;
            animation: spin 2s;
        }

        @keyframes lock {
            0% {
                top: -45px;
            }

            65% {
                top: -45px;
            }

            100% {
                top: -30px;
            }
        }

        @keyframes spin {
            0% {
                transform: scaleX(-1);
                left: calc(50% - 30px);
            }

            65% {
                transform: scaleX(1);
                left: calc(50% - 12.5px);
            }
        }

        @keyframes dip {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(10px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: center;
        }


        .button-group button {
            padding: 10px 20px;
            border: none;
            background-color: #333;
            color: #fff;
            cursor: pointer;
            border-radius: 5px;
            font-size: 1em;
            display: flex;
            align-items: center;
        }

        .button-group button:hover {
            background-color: #555;
        }

        .button-group svg {
            margin-right: 8px;
            vertical-align: middle;
        }

        hr {
            margin: 10px 0;
        }
    </style>
</head>

<body>
    <div class="lock"></div>
    <div class="message">
        <h1>Access to this page is restricted</h1>
        <p>Please check with the site admin if you believe this is a mistake.</p>
        <hr>
        <p>Currently logged in as <strong>{{ auth()->user()?->nameWithUsername }}</strong></p>
        <div class="button-group">
            <!-- Go to Dashboard Button -->

            @can(Permission::ADMIN_PANEL->value)
                <a href="{{ route('admin.home') }}" class="dashboard-button">
                    <button type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="currentColor" aria-hidden="true">
                            <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8v-10h-8v10zM13 3v6h8V3h-8z"></path>
                        </svg>
                        Go to Dashboard
                    </button>
                </a>
            @endcan

            <!-- Log Out Button -->
            <form style="display: inline;" action="{{ route('admin.auth.handle-logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-button">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20"
                        fill="currentColor" aria-hidden="true">
                        <path
                            d="M10 3h9a1 1 0 011 1v16a1 1 0 01-1 1h-9a1 1 0 010-2h8V5h-8a1 1 0 010-2zm-3.707 9l3.707 3.707a1 1 0 01-1.414 1.414l-4.707-4.707a1 1 0 010-1.414L8.586 6.293a1 1 0 111.414 1.414L6.414 11H17a1 1 0 110 2H6.414z">
                        </path>
                    </svg>
                    Log Out
                </button>
            </form>
        </div>
    </div>
</body>

</html>
