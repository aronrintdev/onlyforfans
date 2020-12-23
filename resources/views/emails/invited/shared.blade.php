@component('mail::message')
# Welcome!

You have been invited to AllFans to view photos shared by {{ $invite->inviter->name }}!

{{-- json_encode($invite) --}}

@component('mail::button', ['url' => $invite->join_link])
Join Now!
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
