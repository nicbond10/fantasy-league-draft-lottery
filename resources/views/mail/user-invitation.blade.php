<x-mail::message>
# You’re invited

You have been invited to join Fantasy Draft Lottery. Use the button below to create your account. Accepting the invitation will verify the invited email address.

<x-mail::button :url="$acceptUrl">
Accept invitation
</x-mail::button>

This invitation expires {{ $expiresAt->diffForHumans() }}. If you were not expecting it, you can ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
