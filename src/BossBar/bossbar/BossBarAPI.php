<?php

namespace BossBar\bossbar;

use pocketmine\math\Vector3;
use pocketmine\network\protocol\v120\BossEventPacket;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\network\protocol\SetEntityDataPacket;
use pocketmine\network\protocol\UpdateAttributesPacket;
use pocketmine\Player;
use BossBar\entity\BossBarEntity as Entity;

class BossBarAPI extends Vector3 {

	const SHULKER = 54;

	private $metadata;
	private $eid;
	private $healthPercent;
	private $maxHealthPercent;
	private $viewers = [];

	public function __construct($health = 1) {
		parent::__construct(0, 255);
		$this->metadata = [
			Entity::DATA_LEAD_HOLDER => [Entity::DATA_TYPE_LONG, -1],
			Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, 0 ^ 1 << Entity::DATA_FLAG_SILENT ^ 1 << Entity::DATA_FLAG_INVISIBLE ^ 1 << Entity::DATA_FLAG_IMMOBILE],
			Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0],
			Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, ''],
			Entity::DATA_BOUNDING_BOX_WIDTH => [Entity::DATA_TYPE_FLOAT, 0],
			Entity::DATA_BOUNDING_BOX_HEIGHT => [Entity::DATA_TYPE_FLOAT, 0]
		];
		$this->eid = Entity::$entityCount++;
		$this->maxHealthPercent = 600;
		$this->setHealthPercent($health);
	}

	public function showTo(Player $player, string $title, bool $isViewer = true) {
		$pk = new AddEntityPacket();
		$pk->eid = $this->eid;
		$pk->type = self::SHULKER;
		$pk->attributes[] = [
			'min' => 1,
			'max' => 600,
			'value' => max(1, min([$this->healthPercent, 100])) / 100 * 600,
			'name' => 'minecraft:health',
			'default' => 100
		];
		$pk->metadata = [
			Entity::DATA_LEAD_HOLDER => [Entity::DATA_TYPE_LONG, -1],
			Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, 0 ^ 1 << Entity::DATA_FLAG_SILENT ^ 1 << Entity::DATA_FLAG_INVISIBLE ^ 1 << Entity::DATA_FLAG_IMMOBILE], Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0],
			Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $title],
			Entity::DATA_BOUNDING_BOX_WIDTH => [Entity::DATA_TYPE_FLOAT, 0],
			Entity::DATA_BOUNDING_BOX_HEIGHT => [Entity::DATA_TYPE_FLOAT, 0]
		];
		$pk->x = $player->x;
		$pk->y = $player->y;
		$pk->z = $player->z;
		$player->dataPacket($pk);
		$pk = new BossEventPacket();
		$pk->eid = $this->eid;
		$pk->darkenScreen = 11;
		$pk->eventType = BossEventPacket::EVENT_TYPE_ADD;
		$pk->bossName = $title;
		$pk->healthPercent = (float) $this->healthPercent / 100;
		$pk->playerID = 0;
		$pk->color = 0;
		$pk->overlay = 0;
		$player->dataPacket($pk);
		if ($isViewer) {
			$this->viewers[$player->getUniqueId()] = $player;
		}
	}

	public function updateFor(Player $player, $title = "", $health = 1) {
		$pk = new UpdateAttributesPacket();
		$pk->entityId = $this->eid;
		$pk->attributes[] = ['min' => 1, 'max' => 600, 'value' => max(1, min([$hp, 100])) / 100 * 600, 'name' => 'minecraft:health', 'default' => 1];
		$player->dataPacket($pk);
		$pk = new BossEventPacket();
		$pk->eid = $this->eid;
		$pk->eventType = BossEventPacket::EVENT_TYPE_UPDATE_PERCENT;
		$pk->healthPercent = $health / 100;
		$player->dataPacket($pk);
		$pk = new SetEntityDataPacket();
		$pk->eid = $this->eid;
		$pk->metadata = [Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $title]];
		$player->dataPacket($pk);
		$pk = new BossEventPacket();
		$pk->eid = $this->eid;
		$pk->eventType = BossEventPacket::EVENT_TYPE_UPDATE_NAME;
		$pk->bossName = $title;
		$player->dataPacket($pk);
	}

	public function updateForAll() {
		foreach ($this->viewers as $viewer) {
			$this->updateFor($viewer);
		}
	}

	public function hideFrom(Player $player) {
		$pk = new RemoveEntityPacket();
		$pk->eid = $this->eid;
		$player->dataPacket($player);
	}

	public function getHealthPercent() {
		return $this->healthPercent;
	}

	public function setHealthPercent(?float $health = null, $update = true) {
		if ($health !== null) {
			if ($health > $this->maxHealthPercent) {
				$this->maxHealthPercent = $this->getMaxHealthPercent();
			}
			$this->healthPercent = $health;
		}
		if ($update) {
			$this->updateForAll();
		}
	}

	public function getMaxHealthPercent() {
		return $this->maxHealthPercent;
	}

	public function getMetadata($key) {
		return isset($this->metadata[$key]) ? $this->metadata[$key][1] : null;
	}

	public function getViewers() {
		return $this->viewers;
	}

}
