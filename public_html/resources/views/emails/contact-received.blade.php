<x-mail::message>
# New Contact Message

You have received a new message from the contact form.

**From:** {{ $contactMessage->name }} ({{ $contactMessage->email }})
**Subject:** {{ $contactMessage->subject ?? 'No Subject' }}

**Message:**
<x-mail::panel>
{{ $contactMessage->message }}
</x-mail::panel>

<x-mail::button :url="config('app.url') . '/admin/contact-messages'">
View in Admin Panel
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
