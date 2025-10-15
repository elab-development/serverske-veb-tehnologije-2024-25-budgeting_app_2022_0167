 
@component('mail::message')
# Reset lozinke

Zahtev za reset lozinke je primljen. Klikni na dugme ispod i postavi novu lozinku.

@component('mail::button', ['url' => $resetUrl])
Resetuj lozinku
@endcomponent

Ako dugme ne radi, otvori link u pregledaču:  
{{ $resetUrl }}

Hvala,  
**Budget App**
@endcomponent
