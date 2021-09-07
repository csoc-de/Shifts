# Shifts Admin Documentation

## Installation

In your Nextcloud, simply navigate to »Apps«, choose the category »Organization«, find the shifts app and enable it. After installing the app onto your Nextcloud you need to setup different groups and settings explained below. one addition License to enable the integrated  [Gantt-schedule-timeline-calendar](https://github.com/neuronetio/gantt-schedule-timeline-calendar) by generating a free License Key [here](https://gstc.neuronet.io/free-key/).

## Configuration
### Groups

| Group | Default | Purpose |
|---|---|---|
| Shiftworkers | Analyst | Group to identify all Shiftworkers that can take shifts |
| Shiftadmin | Shiftsadmin | Administrators to organize and plan the Shifts |
| Skillgroups | Level 1 | Skill groups which can be assigned to new Shifttypes which can then only be taken over by Shiftworkers with an equal or higher Skillgroup |

### Additional Strings

| String | Default | Purpose |
|---|---|---|
| CalendarOrganizer | admin | Account which acts as an organizer for the Nextcloud-Calendar events |
| CalendarOrganizerEmail | admin@test.com | Email of the afformentionend Organizer |
| CalendarName | ShiftsCalendar | Name of the Calendar where the shifts are saved |
| GSTC-License | - | Free GSTC-License needed to display the Shifts in an appropriate format. | 

The free license for the integrated  [Gantt-schedule-timeline-calendar](https://github.com/neuronetio/gantt-schedule-timeline-calendar) can be obtained [here](https://gstc.neuronet.io/free-key/).
