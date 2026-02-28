# PHP på Windows – 3 alternativ

Instruktioner för att installera PHP på PC (Windows) så att du kan köra `composer`, `php scripts/validate.php`, `composer phpstan` m.m.

---

## Alternativ 1: Officiell PHP (windows.php.net)

**Fördelar:** Ingen extra mjukvara, senaste PHP-versioner, lättvikt.

### Steg

1. Gå till [windows.php.net/download](https://windows.php.net/download/)
2. Ladda ner **VS16 x64 Non Thread Safe** (eller Thread Safe) – ZIP-fil för PHP 8.x
3. Packa upp till t.ex. `C:\php`
4. Lägg till i PATH:
   - Sök "Miljövariabler" i Windows
   - Redigera variabeln **Path** för användaren
   - Lägg till `C:\php` (eller din sökväg)
   - Bekräfta med OK
5. Starta om terminalen (PowerShell/CMD)
6. Kontrollera: `php -v`

---

## Alternativ 2: XAMPP

**Fördelar:** Allt-i-ett (Apache, MySQL, PHP, phpMyAdmin). Bra om du vill ha lokal webbmiljö utan Local.

### Steg

1. Ladda ner från [apachefriends.org](https://www.apachefriends.org/download.html)
2. Kör installationsprogrammet
3. Välj komponenter (PHP är inkluderat)
4. Standardinstallation: `C:\xampp`
5. Lägg PHP i PATH:
   - XAMPP:s PHP finns i `C:\xampp\php`
   - Lägg till `C:\xampp\php` i användarens Path (se Alternativ 1, steg 4)
6. Starta om terminalen
7. Kontrollera: `php -v`

---

## Alternativ 3: Chocolatey

**Fördelar:** En rad i terminalen. Uppdateringar med `choco upgrade php`.

### Förutsättning

Chocolatey måste vara installerat: [chocolatey.org/install](https://chocolatey.org/install)

(Kör PowerShell som administratör:)
```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
```

### Steg

1. Öppna PowerShell som administratör
2. Kör:
   ```powershell
   choco install php -y
   ```
3. Starta om terminalen
4. Kontrollera: `php -v`

---

## Efter installation

```powershell
cd c:\Projects\Museum-Railway-Timetable
composer install
php scripts\validate.php
composer phpstan
```

Om `composer` inte hittas: installera Composer från [getcomposer.org/download](https://getcomposer.org/download/).
