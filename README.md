 

# Opis
Ovo je studentski projekat za predmet „Serverske veb tehnologije“. Aplikacija služi za evidenciju i deljenje troškova: kreiranje troškova (Expense), podela po učesnicima (Split), poravnanja (Settlement) i kategorizacija (Category). Autentifikacija je preko Laravel Sanctum (Bearer tokeni).

## Korišćene tehnologije
- PHP 8+, Laravel 10+ (Eloquent ORM, Migrations, Seeders, Resources)
- Laravel Sanctum (token autentifikacija)
- MySQL/MariaDB ili druga kompatibilna SQL baza
- Mail (reset lozinke, obaveštenja), HTTP klijent (kursevi), Cache

## Brzi start
1) Konfiguracija
- Kopiraj env: cp .env.example .env
- U .env podesi: DB_* varijable, APP_URL, FRONTEND_URL, MAIL_*
2) Instalacija
- composer install
- php artisan key:generate
3) Baza i demo podaci
- php artisan migrate --seed   (ili php artisan migrate:fresh --seed)
4) Pokretanje
- php artisan serve   (API na http://localhost:8000)

 
 
