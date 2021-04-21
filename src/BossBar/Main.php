<?php

namespace BossBar;

use pocketmine\plugin\PluginBase;
use BossBar\bossbar\BossBarAPI;

class Main extends PluginBase {

	public function createBossBar($health) {
		return new BossBarAPI($health);
	}

}
