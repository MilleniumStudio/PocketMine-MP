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
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class GamemodeCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.gamemode.description",
			"%commands.gamemode.usage"
		);
		$this->setPermission("pocketmine.command.gamemode");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) === 0){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));

			return false;
		}

		$gameMode = Server::getGamemodeFromString($args[0]);

		if($gameMode === -1){
			$sender->sendMessage("Unknown game mode");

			return true;
		}

		if(isset($args[1])){
			$targets = array_values($this->getCommandTargets($sender, $args[1]));
			if(count($targets) === 0){
				$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));

				return true;
			}
		}elseif($sender instanceof Player){
			$targets = [$sender];
		}else{
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));

			return true;
		}

		$success = [];
		foreach($targets as $target){
			if(!($target instanceof Player)){
				continue;
			}

			$target->setGamemode($gameMode);
			if($gameMode !== $target->getGamemode()){
				$sender->sendMessage("Game mode change for " . $target->getName() . " failed!");
			}else{
				$success[] = $target->getName();
				if($target !== $sender){
					$target->sendMessage(new TranslationContainer("gameMode.changed"));
				}
			}
		}

		if(count($targets) === 1 and $targets[0] === $sender){
			Command::broadcastCommandMessage($sender, new TranslationContainer("commands.gamemode.success.self", ['blame', 'mojang', Server::getGamemodeString($gameMode)]));
		}else{
			Command::broadcastCommandMessage($sender, new TranslationContainer("commands.gamemode.success.other", ['blame mojang', implode(", ", $success), Server::getGamemodeString($gameMode)]));
		}

		return true;
	}
}