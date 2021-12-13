# Shifts Admin Documentation

## Installation

In your Nextcloud, simply navigate to »Apps«, choose the category »Organization«, find the shifts app and enable it. After installing the app onto your Nextcloud you need to setup different groups and settings explained below.
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

### Explaining some Funtionality

#### The Rules Tab for new Shift-types

These Options allow the Admin to define how many shifts are needed for a given weekday. When updating these values, open shifts will either be added or removed upon saving the changes.

When 0 is given, there will not be any open shifts added, but the admins can still add shifts of this type to the Plan.

When X is given, the app will add X amount of open shifts to the databse for a given weekday.
