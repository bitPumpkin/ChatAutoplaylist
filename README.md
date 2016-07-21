# Chat AutoPlaylist

Chat AutoPlaylist - is a web application, that helps streamers to update stream playlists by 
automatically adding to it compositions and video clips, which users leaves links to in the chat.
The application works only with YouTube as a playlist service, so you must be authorised in your
Google Account and give rights to the application, so it's allowed to create and update your 
YouTube playlists. Chat Autoplaylist uses GoodGame as a streaming service for now, but support
of Twitch and YouTube Gaming will be added soon. 

## Usage

* Link your Google Account to the application; 
* Copy your stream url and click Start button;
* Have fun! 

## Installing
	
* Copy all the files to a directory on your server
* Install Google APIs Client Library for PHP - https://github.com/google/google-api-php-client
* Link the library to index.php
* Register your copy in the Google Developers Console
* Get OAuth 2.0 Client Id and Client Secret for a web-application
* Specify them in index.php

## Dependencies

* Google API Client Libraries - https://developers.google.com/api-client-library/php/
* SockJS - https://github.com/sockjs/sockjs-client
* GoodGame Chat API - https://github.com/GoodGame/API/blob/master/Chat/protocol.md

## Credits

* Thanks to Sam Herbert for nice SVG - http://goo.gl/7AJzbL
* Licensed under - Creative Commons Attribution 3.0 License
* 2016 | Chat AutoPlaylist by Andrey Esenin aka bitPumpkin
