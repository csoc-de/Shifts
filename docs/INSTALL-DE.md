# Installation Nextcloud-Shifts

## Voraussetzung

* Benötigte Nextcloud Konfigurationen
    * Analysten-Gruppe mit dem Namen "analyst" (Falls andere Namen besser passen ist eine kleine Änderung im Code notwendig)
    * Schichten-Administratoren-Gruppe mit dem Namen "ShiftsAdmin" (gleiches gilt hier)
    * Schichtplan-Admin der Organisator für die Schichteneinträge im Kalender(name: 'shiftsorganizer', Email: 'shifts@csoc.de', kann natürlich geändert werden, muss nur im Code aktualiesiert werden)
    * Leitstellen Schichtplan Kalender mit Bearbeitungsrechten für "ShiftsAdmin"
        * Name übernommen von momentanem Plan, nachträgliche Änderung des Namen benötigt zunächst noch Codeänderung (in Zukunft wird dies nicht mehr benötigt)
        * Schichtplan Admin muss der Ersteller des Kalenders sein (Ich habe leider noch keinen Weg gefunden dies zu umgehen)
* Benötigte Server Konfiguration
    * git installiert
    * npm und Node installiert


## Installation

```
cd /var/www/
sudo mkdir .cache
sudo mkdir .config
chown -R www-data .cache
chown -R www-data .config
```

Öffne Nextcloud Ordner mit Kommdanozeile

normalerweise `/var/www/nextcloud` (Apache2 Webserver)

Navigiere in den Apps Ordner

```
cd apps/
```

Klonen des Git Repositories

```
git clone https://github.com/csoc-de/shifts (Nur erreichbar mit Allgemeinem VPN)
```

Navigiere in Shifts Ordner

```
cd shifts/
```

Installation der benötigten PHP und Node Pakete

```
sudo -u www-data make composer
sudo -u www-data make dev-setup
```

Kompilieren des Frontend-Codes

```
sudo -u www-data make build-js
```

Anschließende Aktivierung der App "Shifts"in den Nextcloud Apps-Einstellungen


## Allgemeine Hinweise
- Die App/Nextcloud sind case-sensitiv. Die Gruppe 'Analyst' ist nicht das Selbe wie 'analyst'. Bitte prüfe, ob die Gruppenname **exakt** die Selben sind.
- Wenn eine Nextcloud-Gruppe umbenannt wird, kann es sein, dass sich die ID im Hintergrund nicht ändert ([#65](https://github.com/csoc-de/Shifts/issues/65))
