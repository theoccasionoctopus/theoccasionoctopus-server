# WebFinger

This supports the standard WebFinger protocol, so a user can be found on this server.

Make a request to `/.well-known/webfinger`

Pass the user you want to look up as the `resource` GET parameter.

This can simply the be the username of the user you want to look up (the more standard forms of `acct:USER@HOST` are also accepted)

eg

* GET `/.well-known/webfinger?resource=testone`

If the user does not exist, a 404 response is sent.

If the user is found, a JSON block of data is sent.

