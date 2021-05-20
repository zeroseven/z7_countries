# Countries :jp: :us: :fr: :es: :it: :ru: :gb: :de:

Diese Erweiterung bringt eine Länderkonfiguration für Singletree-Websites.
Dabei können beliebige Datenbank-Tabellen um eine Länderauswahl erweitert werden um Inhalte nur für ausgewählte Länder zur Verfügung zu stellen.

Für jedes Land wird für jede Seite in jeder Sprachen eine Variante mit einer dynamischen Basis-URL generiert. Diese setzt sich aus dem ISO-Code der Sprache und dem ISO-Code des Landes zusammen.
Wird beim Aufruf einer Seite, eine solche Sprach-Land-Kombination in der URL ermittelt, wird bereits beim Initialiseren der `Site` die Sprache manipuliert indem die Basis-URL verändert wird.
Anschlißend werden automatisch in jeder Datenbankabfrage der konfigurierten Tabellen die Ländereinstellungen berücksichtigt.

Für jede Sprache steht weiterhin auch eine "internationale" Version – also ohne Land zur Verfügung.

Bei 100 Seiten in 4 Sprachen und 10 Länder gibt es also ganze `100 * 4 * (10 + 1) = 4400` Seiten (abzüglich derer, die für manche Länder ausgeblendet wurden).

### Beispiel:

* `examle.com/de/path` (Seite auf deutsch)
* `examle.com/de-de/path` (Seite auf deutsch für Deutschland)
* `examle.com/de-at/path` (Seite auf deutsch für Österreich)
* `examle.com/en/path` (Seite auf englisch)
* `examle.com/en-de/path` (Seite auf englisch für Deutschland)
* `examle.com/en-at/path` (Seite auf englisch für Österreich)

## Features:

* Manipulation der Base-URL mit dynamischer Sprach-Land-Kombination (z.B. `de-ch`, `en-nl` …)
* Erweiterung der hreflang-Tags
* Einfache Ländererweiterung für Tabellen.

## Konfiguration:

### Länder definieren:

Auf Rootebene (uid:0) können beliebig viele Länder erstellt werden. Diese Länder können für konfigurierte Tabellen verwendet werden.

### Datenbank erweitern

Um die Länderauswahl in einem Datensatz/Tabelle zu integrieren um entsprechend die Datenbank-Abfragen dafür dynamisch zu erweitern ist nur eine kleine Konfiguration notwendig:

```php
<?php

\Zeroseven\Countries\Service\TCAService::registerPalette('pages');
```

Der Database-Analyzer im Install-Tool erkennt diese Konfiguration automatisch und erweitert die Tabelle beim Ausführen entsprechend.
