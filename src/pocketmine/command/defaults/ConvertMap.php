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

class ConvertMap extends VanillaCommand{

    public function __construct(string $name){
        parent::__construct(
            $name,
            "%pocketmine.command.convertmap.description",
            "%pocketmine.command.convertmap.usage"
        );
        $this->setPermission("pocketmine.command.give");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender)){
            return true;
        }

        $player = $sender->getServer()->getPlayer($sender->getName());

        $playerX = $player->getLocation()->getFloorX();
        $playerY = $player->getLocation()->getFloorY();
        $playerZ = $player->getLocation()->getFloorZ();

        for ($lX = $playerX - 20; $lX <= $playerX + 20; $lX++) {
            for ($lY = $playerY - 20; $lY <= $playerY + 20; $lY++) {
                for ($lZ = $playerZ - 20; $lZ <= $playerZ + 20; $lZ++) {
                    $blockId = $player->level->getBlock(new Vector3($lX, $lY, $lZ))->getId();
                    switch ($blockId)
                    {

                        case 95: // replace colored glass block by regular glass block
                            $player->level->setBlockIdAt($lX, $lY, $lZ, 20);
                            $player->level->setBlockDataAt($lX, $lY, $lZ, 6);
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

        }

        Command::broadcastCommandMessage($sender, new TranslationContainer("Convert map success"));
        return true;
    }
}