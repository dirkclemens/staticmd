---
Title: Auto-Link Test
Visibility: public
---

# Auto-Link Test

## Nackte URLs (sollten zu Links werden):

Normale HTTP-URL:
https://github.com/dreed47/WifiMQTTManager    

HTTPS-URL:
https://www.example.com/path/to/resource    

WWW-URL ohne Protokoll:
www.google.com    

FTP-URL:
ftp://files.example.com/download    

Sehr lange URL:
https://www.example.com/very/long/path/with/many/segments/and/parameters?param1=value1&param2=value2&param3=value3    

## Bestehende Markdown-Links (sollten unverändert bleiben):

[GitHub Projekt](https://github.com/dreed47/WifiMQTTManager)   
[Example mit Text](https://www.example.com)   

## Listen mit gemischten URLs:

- https://www.debugpoint.com/upgrade-kde-plasma-6/
- Bypass any paywall: https://12ft.io/URL
- [Bereits formatierter Link](https://www.tutonaut.de/paywalls-umgehen)
- https://www.stern.de/gesundheit/gesund-leben/rueckenschmerz

## Code-Blöcke (sollten nicht konvertiert werden):

```bash
curl https://api.example.com/data
wget www.example.com/file.zip
```

Inline Code: `https://example.com` sollte auch nicht konvertiert werden.

## Normale Markdown-Formatierung:

**Fett** und *kursiv* funktioniert weiterhin.

Mit Emojis: :rocket: :smile: :heart: