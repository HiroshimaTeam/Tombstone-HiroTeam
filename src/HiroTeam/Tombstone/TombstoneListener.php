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

namespace HiroTeam\Tombstone;


use HiroTeam\Tombstone\entity\TombstoneEntity;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\network\mcpe\protocol\types\GameMode;

class TombstoneListener implements Listener{

	/**
	 * @var TombstoneMain
	 */
	private TombstoneMain $main;

	public function __construct(TombstoneMain $main){
		$this->main = $main;
	}

	/**
	 * @param PlayerDeathEvent $event
	 * @priority HIGHEST
	 */
	public function onDeath(PlayerDeathEvent $event){
		$worlds = $this->main->getConfig()->get('worlds');
		$player = $event->getPlayer();
		if($event->getKeepInventory()) return;
		if(
			($worlds === "*" xor
			in_array($player->getLevel()->getName(), explode(", ", $worlds))) and
			$player->getGamemode() !== GameMode::CREATIVE
		){
			$tombstone = new TombstoneEntity();
			$tombstone->initialize($player);
			$event->setDrops([]);
		}
	}
}