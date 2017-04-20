emailAPI
========

REST email API do generowanie i wysyłki wiadomości email.

# Instalacja
```
git clone git@github.com:oleksym/emailAPI.git
composer install
cd emailAPI
php bin/console doctrine:schema:create --env=prod
```

# Konfiguracja
Aby przetestować wysyłkę należy wprowadzić konfigurację mailera w pliku `app/config/parameters.yml`

# Uruchomienie
`php bin/console server:run`

# Przykłady użycia
#### Dodawanie rekordu
1. Wysyłamy żądanie POST na /api/v1/emails
2. Otrzymujemy odpowiedź (kod `201`) z ustawionym header location na /api/v1/emails/1

#### Przeglądanie rekordu
1. Wysyłamy żądanie GET na /api/v1/emails/1
2. Otrzymujemy odpowiedź (kod `200`) wraz z JSONem.

#### Modyfikowanie rekordu
1. Wysyłamy żądanie PUT lub PATCH na /api/v1/emails/1 z danymi (opcjonalnie):
- priority - liczba z zakresu 0-10
- provider - `smtp` lub `rest`
- sender - adres email nadawcy
- recipients - string lub tablica z adresami email odbiorców
- subject - tekst
- body - tekst
2. Jeśli wszystko przeszło poprawnie to otrzymujemy odpowiedź (kod `200`) wraz z JSONem. W przypadku pojawienia się błędów API zwróci odpowiedź (kod 404) oraz JSONa z wymienionymi błędami.

#### Usuwanie rekordu
1. Wysyłamy żądanie DELETE na /api/v1/emails/1
2. Otrzymujemy odpowiedź (kod `200`) wraz z JSONem.

#### Przeglądanie wszystkich rekordów
1. Wysyłamy żądanie GET na /api/v1/emails
2. Otrzymujemy odpowiedź (kod `200`) wraz z JSONem.

#### Wysyłanie wszystkich niewysłanych maili
1. Wysyłamy żądanie POST na /api/v1/emails/send
2. Otrzymujemy odpowiedź (kod `200`) wraz z JSONem a w nim informacje o ilości wysłanych wiadomości oraz błędy jakie wystąpiły.

# Endpoints
| Method | Path |
| ------ | ------ |
| GET         | /api/v1/emails |
| POST        | /api/v1/emails |
| GET         | /api/v1/emails/{id} |
| PUT/PATCH   | /api/v1/emails/{id} |
| DELETE      | /api/v1/emails/{id} |
| POST        | /api/v1/emails/send |

# Opis kodu
- `src/AppBundle/Controller/Api/V1/EmailController.php` - cała logika API. Adresy końcówek skonfigurowane są w adnotacjach a prefiks uzupełniony w `routing.yml`.
- `src/AppBundle/Entity/Email.php` - obiekt reprezentujący rekord Email
- `src/AppBundle/Service/Mailer.php` - service obsługujący wysyłkę przez SMTP (przy użyciu SwiftMailera) oraz przez RESTowe API (TODO)
- `src/AppBundle/Lib/RESTMailTransporter/RESTMailTransporter.php` - obsługa wysyłki przez API (TODO)
- `tests/AppBundle/*`
