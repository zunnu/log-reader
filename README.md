# LogReader plugin for CakePHP

Log reader helps you quickly and clearly see individual log entries of your cakePHP application.
With log reader you no longer need to read raw log files from the server. Log reader allows you to read logs straight from the UI.

Log readers API allows you to create your own custom application to help you get head of errors and provide you with useful information.

## Documentation
See the API documentation of [Log Reader](https://github.com/zunnu/log-reader/wiki)

## Requirements
* CakePHP 3.x
* PHP 7.2 >

## Installing Using [Composer][composer]

`cd` to the root of your app folder (where the `composer.json` file is) and run the following command:

```
composer require zunnu/log-reader:1.3
```

Then load the plugin by using CakePHP's console:

```
./bin/cake plugin load LogReader
```

## Usage
You can see the logs by going to
http://app-address/log-reader
<img src="https://i.imgur.com/8sCwLBh.png" alt="logs">

## License

Licensed under [The MIT License][mit].

[cakephp]:http://cakephp.org
[composer]:http://getcomposer.org
[mit]:http://www.opensource.org/licenses/mit-license.php
