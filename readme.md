TicketsApp
==========

Webova aplikace pro spravu vstupenek na akce/eventy. Umoznuje vytvaret akce, generovat vstupenky s QR kody, spravovat klienty, odesilat vstupenky emailem a overovat vstupenky pri vstupu pomoci skeneru.

Postaveno na frameworku [Nette 3.1](https://nette.org).


Funkce
------

- **Sprava akci** — vytvareni, editace, aktivace/deaktivace a mazani akci/eventu
- **Sprava vstupenek** — generovani vstupenek s unikatnim kodem a QR kodem (PNG), aktivace/deaktivace, mazani
- **Sprava klientu** — evidence klientu (jmeno, prijmeni, email), vyhledavani podle jmena, ID nebo emailu
- **Skener vstupenek** — overovani platnosti vstupenek nactenim QR kodu (vyuziva jsQR.js)
- **Odesilani emailu** — zasilani vstupenek s QR kodem jako prilohou na email klienta
- **Autentizace** — prihlaseni a registrace uzivatelu do administrace


Pozadavky
---------

- PHP >= 7.4
- MySQL/MariaDB
- Composer


Instalace
---------

1. Nainstalujte zavislosti:

       composer install

2. Vytvorte databazi a importujte SQL skripty z adresare `database_scripts/`:

       actions_table.sql
       tickets_table.sql
       clients_tickets_xref_table.sql

3. Nastavte pripojeni k databazi v konfiguraci (`config/common.neon`).

4. Ujistete se, ze adresare `temp/` a `log/` jsou zapisovatelne:

       chmod -R 777 temp log

5. Spustte vyvojovy server:

       php -S localhost:8000 -t www


Struktura projektu
------------------

```
app/
├── AdminModule/           Administracni modul
│   ├── presenters/        Presentery (Login, Register, Homepage, Management, Clients, Scanner)
│   └── templates/         Latte sablony pro admin
├── BaLib/                 Vlastni knihovna
│   ├── Base/              Bazove tridy (Repository, Person)
│   ├── Communications/    EmailSender
│   ├── Entities/          Datove entity (Action, Client, Ticket)
│   ├── Interfaces/        Rozhrani (IClientSearch)
│   ├── Repositories/      Repozitare pro pristup k DB
│   └── Validators/        EmailValidator
├── Model/                 Manazery business logiky
│   ├── ActionsManager     Sprava akci
│   ├── TicketsManager     Sprava vstupenek a generovani QR kodu
│   ├── ClientsManager     Sprava klientu
│   ├── EmailsManager      Odesilani emailu
│   ├── UsersManager       Sprava uzivatelu
│   └── MyAuthenticator    Autentizace
├── Presenters/            Hlavni presentery (Homepage, Error)
└── Bootstrap.php
config/                    Nette konfigurace (NEON)
database_scripts/          SQL skripty pro vytvoreni tabulek
www/                       Verejny adresar (vstupni bod, CSS, JS, obrazky)
```


Technologie
-----------

- **Backend:** PHP 7.4+, Nette 3.1
- **Frontend:** Bootstrap 5, jQuery, Naja (AJAX pro Nette)
- **QR kody:** chillerlan/php-qrcode (generovani), jsQR.js (cteni v prohlizeci)
- **Databaze:** MySQL (InnoDB)
- **Debugging:** Tracy


Databazove schema
-----------------

- **actions** — akce/eventy (`id`, `name`, `date_event`, `is_active`)
- **tickets** — vstupenky (`id`, `action_id` FK, `code`, `path`, `is_active`)
- **clients_tickets_xref** — vazba klient-vstupenka (`client_id` FK, `ticket_id` FK, `timestamp`)

> Tabulka `clients` neni soucasti SQL skriptu v repozitari — je potreba ji vytvorit zvlast.


Bezpecnost
----------

Adresare `app/`, `config/`, `log/` a `temp/` nesmi byt pristupne primo pres webovy prohlizec. Vice viz [Nette security warning](https://nette.org/security-warning).
