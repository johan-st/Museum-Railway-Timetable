# Local development (Local by Flywheel)

- **`deploy.ps1`** – Kopierar plugin till din Local-site (`inc`, `assets`, `languages`, huvudfiler).
- **`deploy.config.example.json`** – Kopiera till `deploy.config.json` och sätt `localPath` och `localUrl`. `deploy.config.json` är gitignored.

Kör från projektroten:

```powershell
.\local\deploy.ps1
.\local\deploy.ps1 -OpenBrowser
```
