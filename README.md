<p align="center">
    <img src="https://andrey.one/images/instagram-data-formatter.png" alt="Image" width="352" height="82" />
</p>
<p align="center">
	<a href="https://scrutinizer-ci.com/g/Nekaravaev/instagram-data-viewer/?branch=master">
		<img src="https://scrutinizer-ci.com/g/Nekaravaev/instagram-data-viewer/badges/quality-score.png?b=master"
			 alt="Scrutinizer Code Quality">
	</a>

</p>


Data Formatter is a CLI application written on PHP, which uses your zip archive to make an HTML page with content from JSON-files.

### Installation

IDF require [PHP](http://php.net/) and [Composer](https://getcomposer.org/) to run.

_It would be great to drop here a link to request for data from Instagram?_ 

[OK, here we go](https://www.instagram.com/download/request/). ✨

Clone repository and install required dependencies

```sh
$ git clone git@github.com:Nekaravaev/InstagramDataFormatter.git idf
$ cd idf
$ composer install
```

Then pass to script zip-file name and username at Instagram

```sh
$ php instagram.php -z zipname.php -u username
```

### Todos

 - Write Tests
 - Add views for saved.json and settings.json
 - Fix some styles

License
----

MIT
