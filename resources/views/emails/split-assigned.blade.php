@component('mail::message')
# Novi trošak

Pozdrav **{{ $split->user->name }}**, dodeljeno ti je učešće u trošku.

**Opis:** {{ $expense->description ?? '—' }}  
**Kategorija:** {{ optional($category)->name ?? '—' }}  
**Platio:** {{ $payer->name }}  
**Datum plaćanja:** {{ \Illuminate\Support\Carbon::parse($expense->paid_at)->format('d.m.Y.') }}  
**Tvoj iznos:** {{ number_format($split->amount, 2, ',', '.') }} RSD

@component('mail::button', ['url' => config('app.url')])
Otvori aplikaciju
@endcomponent

Hvala,  
**Budget App**
@endcomponent
