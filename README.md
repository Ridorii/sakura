# Sakura

_A backend that doesn't suck_

I'll write a more descriptive readme once it's in a stable state.

## Apache 2.x Virtualhosts

```
<VirtualHost *:80 *:443>
    DocumentRoot [local path]/main
    ServerName [site url]
    ServerAlias www.[site url]
</VirtualHost>

<VirtualHost *:80 *:443>
    DocumentRoot [local path]/api
    ServerName api.[site url]

    Header unset Cookie
    Header unset Set-Cookie
</VirtualHost>
```
