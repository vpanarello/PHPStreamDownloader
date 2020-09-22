# Pure PHP stream downloader

This script open a stream file downloader in Pure PHP
Its possible to edit the, per ex., Http response headers before streaming
Or implement some control access to the hosted files.

Streaming allow also large files to be downloaded without crash your server. :D

## Application example

In the example below was implementad a way to download hash named files.
But get it with right original file name.
It is done throught the URL query parameter "filename" Ex:

with URL:
```
https://site.mydomain.com/files/919596eb-e188-4346-a440-b42a8b1800fe.JPG?filename=example_image.jpg
```

Will download from public file: "919596eb-e188-4346-a440-b42a8b1800fe.JPG"
but on client size will get a file named: "example_image.jpg"

## Module Rewrite

Note: This script works together with mod_rewrite in a .htaccess file,
that rewrite all URLs to this index.php file. Ex. below:

```
# .htaccess files example:
RewriteEngine On
RewriteBase /
RewriteRule ^(.+)$ index.php
```

## Contributing

Send comments, suggestions and bug reports on this repository, Or fork the code on github

## Author

* **Vagner Panarello** - <<vpanarello@gmail.com>>

## License

This program is free software; you can redistribute it and/or modify.
