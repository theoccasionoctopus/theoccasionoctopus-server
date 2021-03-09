# Events API

## List Events and Occurrences - common filter paramaters

You can pass the following GET parameters:

* `tag` - the ID of a tag. If set, only events with this tag will be included.
* `url` - if set, only events with this URL will be returned.

## List Events and Occurrences in JSON

To list events:

| Information | Value                                    |
| ----------- | -----------------------------------------|
| Type        | `GET`                                    |
| URL         | `/api/v1/account/ACCOUNT-ID/events.json` |

To list event occurrences:

| Information | Value                                              |
| ----------- | ---------------------------------------------------|
| Type        | `GET`                                              |
| URL         | `/api/v1/account/ACCOUNT-ID/eventOccurrences.json` |

## List Events in iCal


| Information | Value                                    |
| ----------- | -----------------------------------------|
| Type        | `GET`                                    |
| URL         | `/api/v1/account/ACCOUNT-ID/events.ical` |

There is no option to list event occurrences in iCal - the iCal format is better suited to listing events.

## Read a single Event in JSON



| Information | Value                                            |
| ----------- | -------------------------------------------------|
| Type        | `GET`                                            |
| URL         | `/api/v1/account/ACCOUNT-ID/event/EVENT-ID.json` |

Note this is the event Id, not the event occurrence ID.


## Edit an Event


| Information | Value                                            |
| ----------- | -------------------------------------------------|
| Type        | `POST`                                           |
| URL         | `/api/v1/account/ACCOUNT-ID/event/EVENT-ID.json` |

Note this is the event Id, not the event occurrence ID.

## Create a new Event



| Information | Value                                            |
| ----------- | -------------------------------------------------|
| Type        | `POST`                                           |
| URL         | `/api/v1/account/ACCOUNT-ID/event.json`          |

