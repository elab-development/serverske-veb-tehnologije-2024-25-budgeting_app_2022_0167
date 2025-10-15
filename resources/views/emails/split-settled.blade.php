@component('mail::message')
# {{ $isSettled ? 'Plaćeno' : 'Neplaćeno' }}

Trošak **{{ $expense->description ?? '—' }}**  
Platio: **{{ $payer->name }}**  
Učesnik: **{{ $split->user->name }}**  
Iznos: **{{ number_format($split->amount, 2, ',', '.') }} RSD**

Status učešća je promenjen na: **{{ $isSettled ? 'PLAĆENO' : 'NEPLAĆENO' }}**.

@component('mail::button', ['url' => config('app.url')])
Otvori aplikaciju
@endcomponent

**Budget App**
@endcomponent
