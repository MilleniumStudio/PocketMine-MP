<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\math\Vector3;

class ConvertMap extends VanillaCommand
{

	public function __construct(string $name)
	{
		parent::__construct(
			$name,
			"%pocketmine.command.convertmap.description",
			"%pocketmine.command.convertmap.usage"
		);
		$this->setPermission("pocketmine.command.give");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if (!$this->testPermission($sender)) {
			return true;
		}

		$player = $sender->getServer()->getPlayer($sender->getName());


		$x1 = (int)$args[0];
		$y1 = (int)$args[1];
		$z1 = (int)$args[2];
		$x2 = (int)$args[4];
		$y2 = (int)$args[5];
		$z2 = (int)$args[6];

		Command::broadcastCommandMessage($sender, new TranslationContainer("convert map from : " .
																				strval($x1) . " " .
																				strval($y1) . " " .
																				strval($z1) . " to " .
																				strval($x2) . " " .
																				strval($y2) . " " .
																				strval($z2)));

		$minX = 0;
		$minY = 0;
		$minZ = 0;

		$maxX = 0;
		$maxY = 0;
		$maxZ = 0;

		if ($x1 < $x2) {
			$minX = $x1;
			$maxX = $x2;
		} else {
			$minX = $x2;
			$maxX = $x1;
		}

		if ($y1 < $y2) {
			$minY = $y1;
			$maxY = $y2;
		} else {
			$minY = $y2;
			$maxY = $y1;
		}

		if ($z1 < $z2) {
			$minZ = $z1;
			$maxZ = $z2;
		} else {
			$minZ = $z2;
			$maxZ = $z1;
		}

		$totalBlocks = ($maxX - $minX) * ($maxY - $minY) * ($maxZ - $minZ);
		Command::broadcastCommandMessage($sender, new TranslationContainer("Total block to check : " . strval($totalBlocks)));

		$count = 0;
		$time = time();
		for ($x = $minX; $x <= $maxX; $x++) {
			for ($y = $minY; $y <= $maxY; $y++) {
				for ($z = $minZ; $z <= $maxZ; $z++) {
					$this->switchBlockIfNecessary($x, $y, $z, $player);
					$count++;
					if ($count % 100000 == 0)
					{
						Command::broadcastCommandMessage($sender, new TranslationContainer(strval($count) . " blocks"));
					}
				}
			}

		}

		Command::broadcastCommandMessage($sender, new TranslationContainer("Convert map success"));
		return true;
	}

	private function switchBlockIfNecessary($lX, $lY, $lZ, $player)
	{
		$blockId = $player->level->getBlock(new Vector3($lX, $lY, $lZ))->getId();
		switch ($blockId)
		{
			case 44:
				if ($player->level->getBlockDataAt($lX, $lY, $lZ) == 6)
					$player->level->setBlockDataAt($lX, $lY, $lZ, 7);
				if ($player->level->getBlockDataAt($lX, $lY, $lZ) == 7)
					$player->level->setBlockDataAt($lX, $lY, $lZ, 6);
				break;
			case 95: // replace colored glass block by regular glass block
				$player->level->setBlockIdAt($lX, $lY, $lZ, 20);
				$player->level->setBlockDataAt($lX, $lY, $lZ, 6);
				break;
			case 126:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 158);
				break;
			case 157:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 126);
				break;
			case 160: // replace colored glass_pane by regular glass_pane
				$player->level->setBlockIdAt($lX, $lY, $lZ, 102);
				break;
			case 188:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 85);
				$player->level->setBlockDataAt($lX, $lY, $lZ, 1);
				break;
			case 189:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 85);
				$player->level->setBlockDataAt($lX, $lY, $lZ, 2);
				break;
			case 190:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 85);
				$player->level->setBlockDataAt($lX, $lY, $lZ, 3);
				break;
			case 191:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 85);
				$player->level->setBlockDataAt($lX, $lY, $lZ, 5);
				break;
			case 192:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 85);
				$player->level->setBlockDataAt($lX, $lY, $lZ, 4);
				break;
			case 198:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 208);
				break;
			case 202:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 201);
				$player->level->setBlockDataAt($lX, $lY, $lZ, 2);
				break;
			case 208:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 198);
				break;
			case 219:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 218);
				$player->level->setBlockDataAt($lX, $lY, $lZ, 0);
				break;
			case 220:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 218);
				$player->level->setBlockDataAt($lX, $lY, $lZ, 1);
				break;
			case 221:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 218);
				$player->level->setBlockDataAt($lX, $lY, $lZ, 2);
				break;
			case 222:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 218);
				$player->level->setBlockDataAt($lX, $lY, $lZ, 3);
				break;
			case 223:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 218);
				$player->level->setBlockDataAt($lX, $lY, $lZ, 4);
				break;
			case 224:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 218);
				$player->level->setBlockDataAt($lX, $lY, $lZ, 5);
				break;
			case 225:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 218);
				$player->level->setBlockDataAt($lX, $lY, $lZ, 6);
				break;
			case 226:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 218);
				$player->level->setBlockDataAt($lX, $lY, $lZ, 7);
				break;
			case 227:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 218);
				$player->level->setBlockDataAt($lX, $lY, $lZ, 8);
				break;
			case 228:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 218);
				$player->level->setBlockDataAt($lX, $lY, $lZ, 9);
				break;
			case 229:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 218);
				$player->level->setBlockDataAt($lX, $lY, $lZ, 10);
				break;
			case 230:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 218);
				$player->level->setBlockDataAt($lX, $lY, $lZ, 11);
				break;
			case 231:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 218);
				$player->level->setBlockDataAt($lX, $lY, $lZ, 12);
				break;
			case 232:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 218);
				$player->level->setBlockDataAt($lX, $lY, $lZ, 13);
				break;
			case 233:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 218);
				$player->level->setBlockDataAt($lX, $lY, $lZ, 14);
				break;
			case 234:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 218);
				$player->level->setBlockDataAt($lX, $lY, $lZ, 15);
				break;
			case 235:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 220);
				break;
			case 236:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 221);
				break;
			case 237:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 222);
				break;
			case 238:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 223);
				break;
			case 239:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 224);
				break;
			case 240:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 225);
				break;
			case 241:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 226);
				break;
			case 242:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 227);
				break;
			case 243:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 228);
				break;
			case 244:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 229);
				break;
			case 245:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 219);
				break;
			case 246:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 231);
				break;
			case 247:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 232);
				break;
			case 248:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 233);
				break;
			case 249:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 234);
				break;
			case 250:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 235);
				break;
			case 251:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 236);
				break;
			case 252:
				$player->level->setBlockIdAt($lX, $lY, $lZ, 237);
				break;
		}
    }
}