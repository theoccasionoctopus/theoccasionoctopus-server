# Authentication



## Getting a token

To get an API token, a user logs in to the website. They then go to their user settings. The tokens are listed there, or they can create new ones.

When creating a token, you can 

* set a note to remind you what this token is used for later.
* choose if the token has write access or not.
* choose if the token is locked to one account or not. When a user can manage more than one account, they can choose that a token should only work on one account.

## Using the token

Either:

* Pass the token as the GET paramater `access_token`.
* Pass the token as an `Authorization` header with the contents `Bearer TOKEN`.

