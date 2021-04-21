# BossBar
BossBar plugin for Steadfast2.

## API
```php
use BossBar\bossbar\BossBarAPI;

$health = 100;
$title = "BossBar title";
$bossbar = new BossBarAPI($health);
$bossbar->showTo($player, $title);
$updatedTitle = "Updated title";
$updatedHealth = 70;
$bossbar->updateFor($player, $updatedTitle, $updatedHealth); // modify the health and title
$bossbar->hideFrom($player); // hides bossbar from player
```

## Downloads
Download the latest build from **[Releases](https://github.com/Steadfast5/BossBar/releases)**.

## Wiki
Can't find what you're looking for? Try the **[wiki](https://github.com/Steadfast5/BossBar/)**!.
