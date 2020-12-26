# ActivityPub

The Occasion Octopus software uses ActivityPub for some communication between users on different servers.

But overall server-to-server support for ActivityPub is a work in progress.

Client-to-server support for ActivityPub is not planned.

## Authentication

All server-to-server posts to inboxes must be signed with HTTP Signatures.

## Other software

If you write ActivityPub event software and want to make sure it operates together well, get in touch.

### Gancio

Click "Follow Me" on an event to get an account name to follow. You can then discover events.

Events will not update immediately, but after a bit of time.

### Mastodon

As Mastodon does not publish events, there is no interaction possible.

### Mobzillion

If you follow the special account `relay` you can discover all events on a server.

Events will not update immediately, but after a bit of time.

Until [this issue](https://framagit.org/framasoft/mobilizon/-/issues/546) is fixed, it will only get the first page and not get all results.
