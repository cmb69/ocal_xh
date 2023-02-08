# Ocal_XH

Ocal_XH ermöglicht es Kalender auf Ihrer Website zu präsentieren, die die
Belegung bestimmter Örtlichkeiten oder anderer Mietgegenstände anzeigen, so dass
Besucher leicht sehen können, ob ein Posten an einem bestimmten Datum oder in
einem bestimmten (stündlichen) Zeitraum verfügbar ist. Jeder Kalender ist nur
für einen einzigen Mietgegenstand gedacht; allerdings können sie mehrere
Kalender auf einer einzigen Seite anzeigen. Besucher sind in der Lage zwischen
einer Kalender- und einer Listenansicht umzuschalten.

Das Plugin bietet weder tatsächliche Buchungsmöglichkeiten
(allerdings können Sie es u.U. mit einem Mailformular wie
[Advancedform_XH](https://github.com/cmb69/advancedform_xh) kombinieren),
noch kann es als Veranstaltungskalender, wie
[Calendar_XH](https://github.com/cmb69/calendar_xh), verwendet werden.

- [Voraussetzungen](#voraussetzungen)
- [Download](#download)
- [Installation](#installation)
- [Einstellungen](#einstellungen)
- [Verwendung](#verwendung)
  - [Administration](#administration)
  - [Zustände](#zustände)
- [Problembehebung](#problembehebung)
- [Lizenz](#lizenz)
- [Danksagung](#danksagung)

## Voraussetzungen

Ocal_XH ist ein Plugin für CMSimple_XH.
Es benötigt CMSimple_XH ≥ 1.7.0 und PHP ≥ 7.1.0 mit der *json* Extension.

## Download

Das [aktuelle Release](https://github.com/cmb69/ocal_xh/releases/latest)
kann von Github herunter geladen werden.

## Installation

The Installation erfolgt wie bei vielen anderen CMSimple_XH Plugins auch. Im
[CMSimple_XH Wiki](https://wiki.cmsimple-xh.org/de/?fuer-anwender/arbeiten-mit-dem-cms/plugins)
finden Sie weitere Informationen.

1. **Sichern Sie die Daten auf Ihrem Server.**
1. Entpacken Sie die ZIP-Datei auf Ihrem Computer.
1. Laden Sie das gesamte Verzeichnis `ocal/` auf Ihren Server in das `plugins/`
   Verzeichnis von CMSimple_XH hoch.
1. Vergeben Sie Schreibrechte für die Unterverzeichnisse `css/`, `config/`
   und `languages/`.
1. Navigieren Sie zu `Plugins` → `Ocal`, und
   prüfen Sie, ob alle Voraussetzungen für den Betrieb erfüllt sind.

## Einstellungen

Die Konfiguration des Plugins erfolgt wie bei vielen anderen
CMSimple_XH-Plugins auch im Administrationsbereich der Homepage.
Wählen Sie `Plugins` → `Ocal`.

Sie können die Original-Einstellungen von Ocal_XH unter `Konfiguration`
ändern. Beim Überfahren der Hilfe-Icons mit der Maus werden Hinweise zu den
Einstellungen angezeigt.

Die Lokalisierung wird unter `Sprache` vorgenommen. Sie können die
Zeichenketten in Ihre eigene Sprache übersetzen (falls keine entsprechende
Sprachdatei zur Verfügung steht), oder sie entsprechend Ihren Anforderungen
anpassen.

Das Aussehen von Ocal_XH kann unter `Stylesheet` angepasst werden.

## Verwendung

Um einen *täglichen* Belegungskalender auf einer *Seite* anzuzeigen, verwenden Sie:

    {{{ocal('%NAME_DER_BELEGUNG%', %ANZAHL_DER_MONATE%)}}}

Um einen *stündlichen* Belegungskalender auf einer *Seite* anzuzeigen, verwenden Sie:

    {{{ocal_hourly('%NAME_DER_BELEGUNG%', %ANZAHL_DER_WOCHEN%)}}}

Um einen *täglichen* Belegungskalender im *Template* anzuzeigen, verwenden Sie:

    <?=ocal('%NAME_DER_BELEGUNG%', %ANZAHL_DER_MONATE%)?>

Um einen *stündlichen* Belegungskalender im *Template* anzuzeigen, verwenden Sie:

    <?=ocal_hourly('%NAME_DER_BELEGUNG%', %ANZAHL_DER_WOCHEN%)?>

`%NAME_DER_BELEGUNG%` ist ein beliebiger Name, der nur aus den Buchstaben `a`-`z`,
den Ziffern `0`-`9` und dem Minuszeichen (`-`) bestehen darf.

`%ANZAHL_DER_MONATE%` bzw. `%ANZAHL_DER_WOCHEN%` gibt die Anzahl der Kalender an,
die gleichzeitig angezeigt werden sollen.

Beachten Sie, dass Sie eine beliebige Anzahl von Belegungskalendern auf einer
einzelnen Seite platzieren können, sogar für dieselbe Belegung (was sinnvoll
sein könnte, wenn Sie auf einer Seite einen langen Zeitraum, z.B. ein Jahr,
darstellen wollen, aber nur einen kleinen Zeitraum, z.B. einen einzigen Monat,
im Template).

Einige Beispiele:

    {{{ocal('ferienwohnung', 12)}}}
    <?=ocal('ferienwohnung', 1)?>
    {{{ocal_hourly('rikscha', 2)}}}
    {{{ocal('schlauchboot-1', 6)}}}
    {{{ocal('schlauchboot-2', 6)}}}

### Administration

Die Administration der Belegungskalender erfolgt auf den Seiten, auf denen
sie angezeigt werden. Wenn Sie als Administrator angemeldet sind, sehen Sie eine
Werkzeugleiste, wo Sie einen der verfügbaren Zustände durch Anklicken auswählen
können. Dann können Sie einzelne Tage in den angezeigten Kalendern anklicken, um
diesen den gewählten Zustand zuzuweisen. Wenn Sie damit fertig sind, müssen Sie
Ihre Änderungen speichern.

Standardmäßig sind vier Zustände verfügbar, die wie folgt vorbelegt sind:

- `0` (weiß): nicht anwendbar
- `1` (grün): verfügbar
- `2` (gelb): reserviert oder teilweise verfügbar
- `3` (rot): (komplett) ausgebucht

Es ist möglich Anzahl und Bedeutung der Zustände [anzupassen](#zustände).

Beachten Sie, dass die Administration JavaScript und einen einigermaßen
zeitgemäßen Browser erfordert (z.B. wird IE < 8 nicht unterstüzt).

### Zustände

Es kann eine beliebige Anzahl von Zuständen verwendet werden, wobei jeder
eine eigene Bedeutung hat. Der höchste Zustand, der verfügbar sein soll,
muss in der Konfiguration (`State` → `Max`) eingestellt werden. Es ist zu
beachten, dass zusätzlich zu dieser Zustandsanzahl noch der `0` Zustand
existiert, der der Vorgabezustand ist, der nicht in den Listenansichten
angezeigt wird.

Die Zustandsbeschriftungen können in den Spracheinstellungen des Plugins
angepasst werden. Wurde `State` → `Max` erhöht, müssen die verwendeten
Sprachdatein in `plugins/ocal/languages/` manuell bearbeitet werden,
wo weitere Zustandsbeschriftungen etwa wie folgt hinzugefügt werden müssen:

    $plugin_tx['ocal']['label_state_4']="weiterer Zustand";

Die Zustandsfarben können im Stylesheet des Plugins angepasst werden, wo
Regeln für weitere Zustände wie folgt ergänzt werden können:

    *[data-ocal_state="4"] {
        background: blue;
        color: white;
    }

Zusammenfassung: der höchste Zustand wird in der Konfiguration festgelegt,
Zustandsbeschriftungen werden in der Sprachdatei hinzugefügt (oder
entfernt), und Zustandsfarbregeln werden im Stylesheet hinzugefügt (oder
entfernt).

## Problembehebung

Melden Sie Programmfehler und stellen Sie Supportanfragen entweder auf
[Github](https://github.com/cmb69/ocal_xh/issues)
oder im [CMSimple_XH Forum](https://cmsimpleforum.com/).

## Lizenz

Ocal_XH ist freie Software. Sie können es unter den Bedingungen
der GNU General Public License, wie von der Free Software Foundation
veröffentlicht, weitergeben und/oder modifizieren, entweder gemäß
Version 3 der Lizenz oder (nach Ihrer Option) jeder späteren Version.

Die Veröffentlichung von Ocal_XH erfolgt in der Hoffnung, dass es
Ihnen von Nutzen sein wird, aber *ohne irgendeine Garantie*, sogar ohne
die implizite Garantie der *Marktreife* oder der *Verwendbarkeit für einen
bestimmten Zweck*. Details finden Sie in der GNU General Public License.

Sie sollten ein Exemplar der GNU General Public License zusammen mit
Ocal_XH erhalten haben. Falls nicht, siehe <https://www.gnu.org/licenses/>.

© 2014-2017 Christoph M. Becker

## Danksagung

Ocal_XH wurde durch das occcal Plugin von CMSimple-Styles angeregt.
Vielen Dank an Jens Broecher für diese nette Idee.

Das Plugin-Icon wurde von [deviantdark](https://www.deviantart.com/deviantdark) gestaltet.
Vielen Dank für die Veröffentlichung des Icons unter LGPL.

Vielen Dank an die Gemeinschaft im [CMSimple_XH Forum](https://www.cmsimpleforum.com/)
für Tipps, Anregungen und das Testen.
Besonders möchte ich *rothom*, der der erste Tester war, und *hixi*,
die die stündlichen Kalender vorgeschlagen haben, danken.

Und zu guter letzt vielen Dank an [Peter Harteg](http://www.harteg.dk/),
den „Vater“ von CMSimple, und allen Entwicklern von
[CMSimple_XH](https://www.cmsimple-xh.org/de/) ohne die es dieses
phantastische CMS nicht gäbe.
