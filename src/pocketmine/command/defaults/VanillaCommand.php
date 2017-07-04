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
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;

abstract class VanillaCommand extends Command{
	const MAX_COORD = 30000000;
	const MIN_COORD = -30000000;

	const COMMAND_SELECTOR_CHAR = "@";

	public function __construct($name, $description = "", $usageMessage = null, array $aliases = []){
		parent::__construct($name, $description, $usageMessage, $aliases);
	}

	protected function getInteger(CommandSender $sender, $value, $min = self::MIN_COORD, $max = self::MAX_COORD){
		$i = (int) $value;

		if($i < $min){
			$i = $min;
		}elseif($i > $max){
			$i = $max;
		}

		return $i;
	}

	protected function getRelativeDouble($original, CommandSender $sender, $input, $min = self::MIN_COORD, $max = self::MAX_COORD){
		if($input{0} === "~"){
			$value = $this->getDouble($sender, substr($input, 1));

			return $original + $value;
		}

		return $this->getDouble($sender, $input, $min, $max);
	}

	protected function getDouble(CommandSender $sender, $value, $min = self::MIN_COORD, $max = self::MAX_COORD){
		$i = (double) $value;

		if($i < $min){
			$i = $min;
		}elseif($i > $max){
			$i = $max;
		}

		return $i;
	}

	protected function getCommandTargets(CommandSender $sender, string $arg) : array{
		if($arg{0} === self::COMMAND_SELECTOR_CHAR){
			$type = $arg{1};

			switch($type){
				case "a":
					//TODO: check extra args
					return $sender->getServer()->getOnlinePlayers();
				case "e":
					if($sender instanceof Player){
						return $sender->getLevel()->getEntities();
					}else{
						return $sender->getServer()->getDefaultLevel()->getEntities();
					}
				case "p":
					$pos = $sender instanceof Player ? $sender->asPosition() : new Position(0, 0, 0, $sender->getServer()->getDefaultLevel());

					$players = $pos->getLevel()->getPlayers();
					uasort($players, function(Player $a, Player $b) use ($pos){
						$aDist = $a->distanceSquared($pos);
						$bDist = $b->distanceSquared($pos);

						if($aDist === $bDist){
							return 0;
						}elseif($aDist < $bDist){
							return -1;
						}else{
							return 1;
						}
					});

					$count = 1; //TODO check count argument

					return array_slice($players, $count < 0 ? -$count : 0, $count);
				case "r":
					$level = $sender instanceof Player ? $sender->getLevel() : $sender->getServer()->getDefaultLevel();

					$count = 1;
					$results = [];

					for($i = 0; $i < $count; ++$i){
						$results[] = $level->getEntities()[array_rand($level->getEntities())];
					}

					return $results;
				default:
					return [];
			}
		}else{
			return [$sender->getServer()->getPlayer($arg)];
		}
	}
}