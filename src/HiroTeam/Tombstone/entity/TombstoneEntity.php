<?php
/**
 * ██╗░░██╗██╗██████╗░░█████╗░████████╗███████╗░█████╗░███╗░░░███╗
 * ██║░░██║██║██╔══██╗██╔══██╗╚══██╔══╝██╔════╝██╔══██╗████╗░████║
 * ███████║██║██████╔╝██║░░██║░░░██║░░░█████╗░░███████║██╔████╔██║
 * ██╔══██║██║██╔══██╗██║░░██║░░░██║░░░██╔══╝░░██╔══██║██║╚██╔╝██║
 * ██║░░██║██║██║░░██║╚█████╔╝░░░██║░░░███████╗██║░░██║██║░╚═╝░██║
 * ╚═╝░░╚═╝╚═╝╚═╝░░╚═╝░╚════╝░░░░╚═╝░░░╚══════╝╚═╝░░╚═╝╚═╝░░░░░╚═╝
 * Tombstone-HiroTeam By WillyDuGang
 *
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://www.gnu.org/licenses/
 *
 *
 * GitHub: https://github.com/HiroshimaTeam/Tombstone-HiroTeam
 */

namespace HiroTeam\Tombstone\entity;

use HiroTeam\Tombstone\skin\SkinFactory;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Level;
use pocketmine\level\particle\HugeExplodeSeedParticle;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;

class TombstoneEntity extends Human{

	private int $loop = 1;

	public function __construct(?Level $level = null, ?CompoundTag $nbt = null){
		if($level && $nbt){
			parent::__construct($level, $nbt);
		}
	}

	public function initialize(Player $player){
		$nbt = Entity::createBaseNBT($player, null, $player->yaw, $player->pitch);
		$skin = (new SkinFactory())->getTombstoneSkin();
		$nbt->setTag(new CompoundTag("Skin", [
			new StringTag("Name", $skin->getSkinId()),
			new ByteArrayTag("Data", $skin->getSkinData()),
			new ByteArrayTag("CapeData", $skin->getCapeData()),
			new StringTag("GeometryName", $skin->getGeometryName()),
			new ByteArrayTag("GeometryData", $skin->getGeometryData())
		]));
		parent::__construct($player->getLevel(), $nbt);
		$this->spawnToAll();

		$despawnTime = $player->getServer()->getPluginManager()->getPlugin('Tombstone-HiroTeam')->getConfig()->get('despawnTime');
		$this->namedtag->setInt('timeToDespawn', time() + $despawnTime * 60);

		$this->inventory->setContents($player->getInventory()->getContents());
		$this->armorInventory->setContents($player->getArmorInventory()->getContents());
	}

	public function attack(EntityDamageEvent $source) : void{
		if(in_array($source->getCause(), [7, 11])){
			parent::attack($source);
			return;
		}
		if(!($source instanceof EntityDamageByEntityEvent)) return;
		$damager = $source->getDamager();
		if($damager instanceof Player){
			Entity::kill();
			$this->onDeath();
			$this->despawnFromAll();
			$this->level->addParticle(new HugeExplodeSeedParticle($this));
			$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_BLAST);
		}
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		if(!($this->loop % (20 * 60))){
			if($this->namedtag->getInt('timeToDespawn', 0) < time()){
				$this->flagForDespawn();
			}
			$this->loop = 1;
		}
		$this->loop++;
		return parent::entityBaseTick($tickDiff);
	}
}