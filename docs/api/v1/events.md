# Events API


## List Events in JSON

| Information | Value                                    |
| ----------- | -----------------------------------------|
| Type        | `GET`                                    |
| URL         | `/api/v1/account/ACCOUNT-ID/events.json` |


You can pass the following GET parameters:

* `tag` - the ID of a tag. If passed only events with this tag will be included.
* `url` - if set, only events with this URL will be returned.

## List Events in iCal


| Information | Value                                    |
| ----------- | -----------------------------------------|
| Type        | `GET`                                    |
| URL         | `/api/v1/account/ACCOUNT-ID/events.ical` |


You can pass the following GET parameters:

* `tag` - the ID of a tag. If passed only events with this tag will be included.

## Read a single Event in JSON



| Information | Value                                            |
| ----------- | -------------------------------------------------|
| Type        | `GET`                                            |
| URL         | `/api/v1/account/ACCOUNT-ID/event/EVENT-ID.json` |


## Edit an Event


| Information | Value                                            |
| ----------- | -------------------------------------------------|
| Type        | `POST`                                           |
| URL         | `/api/v1/account/ACCOUNT-ID/event/EVENT-ID.json` |


## Create a new Event



| Information | Value                                            |
| ----------- | -------------------------------------------------|
| Type        | `POST`                                           |
| URL         | `/api/v1/account/ACCOUNT-ID/event.json`          |

