# V1 API

A full read/write API is provided.

All URL's start with `/api/v1/`

All account parts are accessed by the account id, not the account username.

eg

* `/api/v1/account/ACCOUNT-ID/profile.json`

You can look up the account ID from the username using WebFinger.

For certain actions, an API token can be passed.

This may allow you to:

* See data you couldn't otherwise see
* Write data

## Contents

* [Authentication](authentication.md)
* [Profile](profile.md)
* [Events](events.md)
* [Tags](tags.md)
