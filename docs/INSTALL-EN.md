# Installation Nextcloud Shifts

## Prerequisite

* Required Nextcloud configurations
    * Analysts group with the name "analyst" (If other names fit better a small change in the code is necessary)
    * Shifts admin group with the name "ShiftsAdmin" (same here).
    * Shift schedule admin the organizer for the shift entries in the calendar(name: 'shiftsorganizer', email: 'shifts@csoc.de', can be changed of course, just needs to be updated in the code).
    * Control center shift plan calendar with editing rights for "ShiftsAdmin".
        * Name taken from current plan, subsequent change of name still needs code change at first (in future this will not be needed)
        * Shift plan admin must be the creator of the calendar (I haven't found a way to work around this yet)
* Required server configuration
    * git installed
    * npm and node installed

## Installation

```
cd /var/www/
sudo mkdir .cache
sudo mkdir .config
chown -R www-data .cache
chown -R www-data .config
```

Open Nextcloud folder with command line

usually `/var/www/nextcloud` (Apache2 web server)

Navigate to the Apps folder

```
cd apps/
```

Cloning the Git repository

```
git clone https://github.com/csoc-de/shifts (Only accessible with General VPN)
```

Navigate to Shift's folder

```
cd shifts/
```

Install the required PHP and Node packages

```
sudo -u www-data make composer
sudo -u www-data make dev-setup
```

Compiling the frontend code

```
sudo -u www-data make build-js
```

Then activate the app "Shifts" in the Nextcloud Apps settings.
