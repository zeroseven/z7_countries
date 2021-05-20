# Countries :jp: :us: :fr: :es: :it: :ru: :gb: :uk: :de:

Diese Erweiterung bringt eine Länderkonfiguration für Singletree-Websites.
Dabei können beliebige Datenbank-Tabellen um eine Länderauswahl erweitert werden um Inhalte nur für ausgewählte Länder zur Verfügung zu stellen.
Zusätzliche steht pro Sprache auch eine "internationale" Version – also ohne Land zur Verfügung.

**Beispiele:**

* `examle.com/path` (Seite in der Standardsprache)
* `examle.com/de-de/path` (Seite auf deutsch für Deutschland)
* `examle.com/de-at/path` (Seite auf deutsch für Österreich)
* `examle.com/en-de/path` (Seite auf englisch für Deutschland)

## Features:

* Manipulation der Base-URL mit dynamischer Sprach-Land-Kombination (z.B. `de-ch`, `en-nl` …)
* Erweiterung der hreflang-Tags
* Einfache Ländererweiterung für Tabellen.

## Was passiert:

Für jedes Land wird pro Sprachen eine individelle Base-URL generiert. Diese setzt sich aus dem ISO-Code der Sprache und dem ISO-Code eines Landes zusammen.
Wird beim Aufruf einer Seite, eine solche Kombination in der URL ermittelt, wird bereits beim Initialiseren der `Site` die Sprache manipuliert indem die Basis-URL verändert wird.
In allen nachfolgenden Prozessen in TYPO3 wird die manipulierte Basis-URL verwendet – so dass es ab hier im Code "ganz normal" weitergeht.

## Konfigurieren

### Länder erstellen

Auf rootebene (uid:0) können beliebig viele Länder erstellt werden.
Dabei ist zu beachten, dass mit jedem Land für jede Seite und in jeder Sprache eine zusätzliche Version zur Verfügung gestellt wird.
Du solltest dir im Idealfall also vorher Gedanken machen welche Länder du benötigst.

### TCA erweitern

Um die Länderauswahl im Datensatz zu integrieren und letztlich auch die Abfrage dafür dynamisch zu erweitern ist nur eine kleine Konfiguration notwendig:

```php
<?php

\Zeroseven\Countries\Service\TCAService::registerPalette('pages');
```

… und fertig!
