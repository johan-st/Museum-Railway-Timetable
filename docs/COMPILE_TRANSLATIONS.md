# Kompilera översättningsfiler

För att WordPress ska kunna använda översättningarna måste `.po`-filen kompileras till en `.mo`-fil.

## Metod 1: Använd Poedit (Rekommenderat)

1. Ladda ner och installera [Poedit](https://poedit.net/)
2. Öppna `languages/museum-railway-timetable-sv_SE.po` i Poedit
3. Klicka på "Save" - Poedit kompilerar automatiskt `.mo`-filen

## Metod 2: Använd msgfmt (Kommandorad)

Om du har `msgfmt` installerat (del av gettext-paketet):

```bash
msgfmt languages/museum-railway-timetable-sv_SE.po -o languages/museum-railway-timetable-sv_SE.mo
```

### Windows

För Windows kan du:
1. Installera [gettext för Windows](https://mlocati.github.io/articles/gettext-iconv-windows.html)
2. Eller använda Poedit (enklare)

### Linux/Mac

```bash
# Installera gettext om det saknas
# Ubuntu/Debian:
sudo apt-get install gettext

# macOS:
brew install gettext

# Kompilera sedan:
msgfmt languages/museum-railway-timetable-sv_SE.po -o languages/museum-railway-timetable-sv_SE.mo
```

## Metod 3: Använd WordPress-plugin

1. Installera [Loco Translate](https://wordpress.org/plugins/loco-translate/)
2. Gå till Loco Translate → Plugins → Museum Railway Timetable
3. Redigera översättningar och spara - plugin kompilerar automatiskt

## Viktigt

- **Efter varje ändring i `.po`-filen måste `.mo`-filen kompileras om**
- WordPress läser `.mo`-filen, inte `.po`-filen
- Om du bara kopierar `.po`-filen utan att kompilera kommer översättningarna inte att fungera

## Verifiera att översättningar fungerar

1. Se till att WordPress är inställt på svenska (Inställningar → Språk)
2. Rensa cache (om du använder cache-plugin)
3. Ladda om admin-sidan
4. Alla texter ska nu vara på svenska

