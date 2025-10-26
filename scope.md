Ich möchte ein php/web-basiertes theme- fähiges System entwickeln, mit dem Dateien im Markdown Format erstellt und verwaltet werden können und daraus dann statische webseiten erstellt werden, die der Server darstellen kann. 
Das System soll sich als Beispiel an https://github.com/datenstrom/yellow orientieren

Was ich benötige ist:
* php basiertes System zum anzeigen von statischen Dateien im Markdown Format
* das System soll bootstrap css nutzen
* der Content dieser Seite, die Markdown-Dateien, liegen in einem Verzeichnissystem ./content
  * unterhalb von ./content/... können die Inhalte in unterschiedlichen Unterverzeichnissen abgelegt werden, die eine thematische Gliederung ermöglichen
* das eigentliche php System liegt in ./system und ist darunter aufgeteilt in 
  * ./system/core für die Hauptanwendung, 
  * ./system/admin für die Verwaltungsfunktionen  
  * ./system/themes für die Themes
* die Markdown Dateien in ./content/... werden über einen Markdown Editor in der Admin UI editiert
  * dazu benötige ich einen einfachen Online Markdown Editor

## Technische Spezifikationen:
* PHP 8.4 als Mindestanforderung
* Einfacher Markdown-Parser (wird ausgewählt)
* Admin-Authentifizierung: einfaches Login (Username/Passwort, dateibasiert)
* Einfacher Online Markdown-Editor (wird implementiert)
* Dynamisches PHP-System (keine statische HTML-Generierung)
* Content besteht aus statischen Markdown-Dateien
