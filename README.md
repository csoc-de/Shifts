# Nextcloud Shifts

A shiftsplaner app for [Nextcloud](https://nextcloud.com).


![](docs/frontpage.png)


## Why is this so awesome?

* **Interactions with the Nextcloud Calender app!** Easy integration into the existing Calender of Nextcloud.
* **Manage and organize your Shifts System!** Customize your Shifts Model with different Shifttypes 
* **Separation of Users by different Nextcloud user Groups!** Divide your Workforce with different Skill-Levels
* **Integration of existing Open-Source Software!** Using the great [Gantt-schedule-timeline-calendar](https://github.com/neuronetio/gantt-schedule-timeline-calendar) and many more libraries


More to come:
* Further maturing of the app
* Individualisation


If you experience any issues or have any suggestions for improvement, use the issue tracker.

## Get on board
For new contributors, please check out [ContributingToNextcloudIntroductoryWorkshop](https://github.com/sleepypioneer/ContributingToNextcloudIntroductoryWorkshop)


## Development setup

Just clone this repo into your apps directory ([Nextcloud server](https://github.com/nextcloud/server#running-master-checkouts) installation needed). Additionally, [npm](https://www.npmjs.com/) to fetch [Node.js](https://nodejs.org/en/download/package-manager/) is needed for installing JavaScript dependencies.

Once npm and Node.js are installed, PHP and JavaScript dependencies can be installed by running:
```bash
make dev-setup
```

## Documentation


* [Admin documentation](doc/admin.md) (installation, configuration, troubleshooting)
