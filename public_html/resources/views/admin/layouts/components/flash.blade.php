@if ($toast = session()->get('toast'))
    @if (is_string($toast))
        <script>
            toast.open({
                type: 'primary',
                message: @js($toast)
            });
        </script>
    @else
        <script>
            toast.open(@js($toast));
        </script>
    @endif
    @php
        Session::forget('toast');
    @endphp
@endif
