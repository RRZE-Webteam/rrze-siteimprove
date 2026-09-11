[![Aktuelle Version](https://img.shields.io/github/package-json/v/rrze-webteam/rrze-siteimprove/main?label=Version)](https://github.com/RRZE-Webteam/rrze-siteimprove)
[![Release Version](https://img.shields.io/github/v/release/rrze-webteam/rrze-siteimprove?label=Release+Version)](https://github.com/rrze-webteam/rrze-siteimprove/releases/)
[![GitHub License](https://img.shields.io/github/license/rrze-webteam/rrze-siteimprove)](https://github.com/RRZE-Webteam/rrze-siteimprove)
[![GitHub issues](https://img.shields.io/github/issues/RRZE-Webteam/rrze-siteimprove)](https://github.com/RRZE-Webteam/rrze-siteimprove/issues)

# RRZE Siteimprove

Einbindung des Siteimprove Analytics JavaScripts in WordPress.

## Contributors

* RRZE-Webteam, http://www.rrze.fau.de

## Copyright

GNU General Public License (GPL) Version 3

## Documentation

See documenation at https://www.wp.rrze.fau.de

## Feedback

* https://github.com/RRZE-Webteam/rrze-siteimprove/issues
* webmaster@rrze.fau.de

## Entwicklerhinweise


Das Plugin bindet den Siteimprove Analytics-Code ein und ergänzt die Angabe der
Page-ID der jeweiligen Seiten als Metatag.

Die frühere Siteimprove Overlay-Integration mit Token-Ermittlung, Recheck und Recrawl
wurde entfernt. Diese Funktion wird inzwischen durch das offizielle Siteimprove
WordPress-Plugin bereitgestellt.

## Einstellungen

Die Einstellung sind im Backend von WordPress unter
Einstellungen › RRZE Siteimprove
zu finden.
Auf Multisite-Installationen sind diese nur Superadmins zugänglich.

Bei Aktivierung der Analytics-Funktion wird der Shortcode
`[siteimprove_analytics_privacy_policy]`
bereitgestellt. Dieser kann in einer Datenschutzerklärung eingefügt werden und wird bei der Ausführung
durch den deutschen bzw. dem englischen Text ersetzt, welcher den Einsatz der
Tracking-Codes gemäß der DSGVO erläutert. Zusätzlich wird ein OPT-Out-Link erstellt.
