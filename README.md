<p align="center">
    <img src="https://github.com/LLoadout/assets/blob/master/LLoadout_microsoftgraph.png" width="500" title="LLoadout logo">
</p>

# Laravel package for using Microsoft mail, OneDrive, Teams and Excel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/lloadout/microsoftgraph.svg?style=flat-square)](https://packagist.org/packages/lloadout/microsoftgraph)
[![Total Downloads](https://img.shields.io/packagist/dt/lloadout/microsoftgraph.svg?style=flat-square)](https://packagist.org/packages/lloadout/microsoftgraph)

This package makes a wrapper around the Microsoft Graph API.

1. It provides a [Mail](#mail-usage) driver (send mails via office365)
2. It provides a storage driver (use [OneDrive](#storage-usage) as a Laravel disk).
3. It also gives the ability to easily interact with Microsoft [Teams](#teams-usage).
4. This package also provides the possibility to work with [Excel](#excel-usage) files loaded from Onedrive, making it possible to write and read Excel files.
5. It allows you to manage [calendar](#calendar-usage) events.
6. It allows you to manage contacts [contact](#contact-usage).

You need to register an app in the Microsoft Azure Portal to use this package. Follow the steps in the Microsoft docs:
https://docs.microsoft.com/en-us/graph/auth-register-app-v2

## Installation

You can install the package via composer:

```bash
composer require lloadout/microsoftgraph
```

Add this to your .env file and fill it with the values you specified in Microsoft Azure Portal app registration.

```
MS_TENANT_ID=
MS_CLIENT_ID=
MS_CLIENT_SECRET=
MS_GRAPH_API_VERSION=v1.0
MS_REDIRECT_URL=https://your-url.com/microsoft/callback
```

## Connect your account

The package uses OAuth and provides two routes

The first redirects you to the consent screen of Microsoft

```
https://your-url.com/microsoft/connect
```

The second is the callback url you need to specify in Microsoft Azure Portal app registration as redirect uri

```
https://your-url.com/microsoft/callback
```

The callback will fire an MicrosoftGraphCallbackReceived event, this will automatically store your token in the session. You can add your token store logic in a listener for this event, for example:

```
Event::listen(function (MicrosoftGraphCallbackReceived $event) {
    $user = Auth::user();
    $user->accessdata = $event->accessData;
    $user->save();
});
```

The package will search for a session variable name `microsoftgraph-access-data` for establishing the connection. So please provide this variable with your accessData as value when logging in.
For example:  On login, you get your accesData from the database and store it into the session variable `microsoftgraph-access-data`.

## Mail usage

### Configuration

You have to provide this API permissions: `Mail.send`

Set the environment variable MAIL_MAILER in your .env file

```
MAIL_MAILER=microsoftgraph
```

note: make sure your from address is the address you gave the consent to

### Usage

```php
Mail::send(new YourMailable());

Mail::raw('The body of my first test message', function($message) {
    $message->to('john@doe.com', 'John Doe')->subject('A mail send via lloadout/microsoftgraph');
});
```

## Storage usage

### Configuration

You have to provide this API permissions: `Files.ReadWrite.all`

add the onedrive root to your .env file:

```
MS_ONEDRIVE_ROOT="me/drive/root"
```

### Usage

The package created a disk called `onedrive`. This means that you can use all the methods as described in the Laravel docs: https://laravel.com/docs/8.x/filesystem#configuration

```php
$disk = Storage::disk('onedrive');
#create a dir
$disk->makeDirectory('Test folder');
#storing files
$disk->put('Test folder/file1.txt','Content of file 1');
$disk->put('Test folder/file2.txt','Content of file 2');
#getting files
Storage::disk('onedrive')->get('Test folder/file1.txt');
```

## Teams usage

### Configuration

You have to provide this API permissions: `Chat.ReadWrite`

### Usage

Get all the teams you are a member of ( additional permissions needed: `Group.Read.All` )

First instantiate the Teams class

```php
$teamsClass = new Teams();
```php

```php
$joinedTeams = $teamsClass->getJoinedTeams();
```

Get alle the channels for a team ( additional permissions needed: `Group.Read.All` )

```php
$channels = $teamsClass->getChannels($team);
```

Get all the chats for a user ( additional permissions needed: `Chat.Read.All` )

```php
$chats = $teamsClass->getChats(); 
```

Get all the members in a channel ( additional permissions needed: `ChannelMessage.Read.All` )

```php
$members = $teamsClass->getMembersInChat($chat));
````

Send a message to a channel ( additional permissions needed: `ChannelMessage.Send` )

```php 
$teamsClass->send($teamOrChat,'Hello world!');
```

## Excel usage

### Configuration

You have to provide this API permissions: `Files.ReadWrite.all`

### Usage

Load a file from OneDrive

First instantiate the Excel class

```php
$excelClass = new Excel();
```

```php
$excelClass->loadFile('Test folder/file1.xlsx');
```          

Load a file by its id

```php
$excelClass->loadFileById($fileId);
```

Set cell values of a range

```php
$values = ['B1' => null, 'B2' => '01.01.23', 'B3' => 3, 'B4' => '250', 'B5' => '120', 'B6' => '30 cm', 'B7' => null, 'B8' => null, 'B9' => null, 'B10' => null, 'B11' => null, 'B12' => 2];
$excelClass->setCellValues('B1:B12', $values);
$excelClass->getCellValues('H1:H20');
```

## Calendar usage

### Configuration

You have to provide this API permissions: `Calendars.ReadWrite`

### Usage

Get all the calendars

First instantiate the Calendar class

```php
$calendarClass = new Calendar();
```

```php
$calendars = $calendarClass->getCalendars();
``` 

Get all the events for a calendar

```php
$events = $calendarClass->getCalendarEvents($calendar);
```

Save an event to a calendar, the event object is a MicrosoftGraphEvent object
We made a helper function to create an event object `Calendar::makeEvent(string $starttime, string $endtime, string $timezone, string $subject, string $body, array $attendees = [], bool $isOnlineMeeting = false)`

```php
$calendarClass->saveEvent($calendar, $event);
```

## Contacts usage

### Configuration

You have to provide this API permissions: `Contacts.ReadWrite`

### Usage

Get all the contacts

First instantiate the Contacts class

```php
$contactsClass = new Contacts();
```

```php
$contacts = $contactsClass->getContacts();
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Dieter Coopman](https://github.com/LLoadout)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
