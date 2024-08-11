Easily integrate your PHP excimer profiles from long-running PSR Request APP with https://github.com/Warxcell/excimer-ui-server.


Just decorate your app handler. (Example from Symfony APP)

```php
$services->set(\Warxcell\ExcimerPsrHandler\ExcimerRequestHandler::class)
    ->decorate(AppHandler::class)
    ->args([
        '$handler' => service('.inner'),
        '$speedscopeDataSender' => inline_service(\Warxcell\ExcimerPsrHandler\SpeedscopeDataSender::class)->autowire()->args([
            '$url' => 'https://your-on-premise-installation.com/profile',
        ]),
    ]);
```
