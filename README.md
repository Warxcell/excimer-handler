Easily integrate your PHP excimer profiles from long-running PSR Request APP
with https://github.com/Warxcell/excimer-ui-server.

Just decorate your app handler. (Example from Symfony APP)

```php
$services->set(\\Warxcell\ExcimerHandler\SpeedscopeDataSender::class)->args([
   '$url' => 'https://your-on-premise-installation.com/profile',
]);
        
$services->set(\Warxcell\ExcimerHandler\ExcimerRequestHandler::class)
    ->decorate(AppHandler::class)
    ->args([
        '$handler' => service('.inner'),
    ]);
```

If you want to profile symfony commands, register following service.

```php
$services->set(\Warxcell\ExcimerHandler\ExcimerCommandHandler::class)->args([
    '$enabled' => env('PROFILE')->default('')->bool(),
]);
```
