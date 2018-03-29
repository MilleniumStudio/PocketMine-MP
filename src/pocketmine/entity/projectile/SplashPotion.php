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


namespace pocketmine\entity\projectile;

use pocketmine\block\Block;
use pocketmine\block\BlockIds;
use pocketmine\entity\Entity;
use pocketmine\level\Position;

class SplashPotion extends Entity
{
    public const NETWORK_ID = self::SPLASH_POTION;

    public $width = 0.98;
    public $height = 0.98;

    public $scale = 0.5;

    protected $baseOffset = 0.49;

    protected $gravity = 0.04;

    protected $fuse;

    private $originLaunchPoint;

    public $metaData = 0;

    protected function initEntity()
    {
        parent::initEntity();
    }

    public function setOriginLaunchPoint(Position $p_position)
    {
        $this->originLaunchPoint = $p_position;
    }

    public function saveNBT()
    {
        parent::saveNBT();
    }

    public function entityBaseTick(int $tickDiff = 1) : bool
    {
        if ($this->closed) {
            return false;
        }
        $hasUpdate = parent::entityBaseTick($tickDiff);

        if ($this->isCollided)
        {
            $centerPos = $this->getPosition();
            $angle = 2.2;

            if ($this->originLaunchPoint instanceof Position)
            {
                $angle = $this->originLaunchPoint->getAngleTo($centerPos);
            }

            $wallAngle = 0;

            if (abs($angle) < (pi() / 8.0))
            {
                $wallAngle = 0;
            }
            else if ($angle > 0)
            {
                if ($angle < (3.0 * pi() / 8.0))
                    $wallAngle = 1;
                else if ($angle < (5.0 * pi() / 8.0))
                    $wallAngle = 2;
                else if ($angle < (7.0 * pi() / 8.0))
                    $wallAngle = 3;
                else
                    $wallAngle = 4;
            }
            else
            {
                if (-$angle < (3.0 * pi() / 8.0))
                    $wallAngle = -1;
                else if (-$angle < (5.0 * pi() / 8.0))
                    $wallAngle = -2;
                else if (-$angle < (7.0 * pi() / 8.0))
                    $wallAngle = -3;
                else
                    $wallAngle = 4;
            }

            if ($this->metaData == 1)
                $this->stairFunction($wallAngle, $centerPos);

            if ($this->metaData == 0)
                $this->wallFunction($wallAngle, $centerPos);

            $this->flagForDespawn();
        }
        return $hasUpdate;
    }

    private function wallFunction(float $p_wallAngle, Position $p_centerPos)
    {
        $level = $p_centerPos->level;

        switch ($p_wallAngle)
        {
            case (0):
            case (4):
                $j = 0;
                while ($j < 3)
                {
                    $i = -2;
                    while ($i <= 2)
                    {
                        $level->setBlock(new Position(intval($p_centerPos->x + $i), intval($p_centerPos->y + $j), intval($p_centerPos->z)), new Block(BlockIds::PRISMARINE));
                        $i++;
                    }
                    $j++;
                }
                break;
            case (2):
            case (-2):
                $j = 0;
                while ($j < 3)
                {
                    $i = -2;
                    while ($i <= 2)
                    {
                        $level->setBlock(new Position(intval($p_centerPos->x), intval($p_centerPos->y + $j), intval($p_centerPos->z + $i)), new Block(BlockIds::PRISMARINE));
                        $i++;
                    }
                    $j++;
                }
                break;
            case (-1):
            case (3):
                $j = 0;
                while ($j < 3)
                {
                    $i = -1;
                    while ($i <= 1)
                    {
                        $level->setBlock(new Position(intval($p_centerPos->x + $i), intval($p_centerPos->y + $j), intval($p_centerPos->z + $i)), new Block(BlockIds::PRISMARINE));
                        $i++;
                    }
                    $j++;
                }
                break;
            case (1):
            case (-3):
                $j = 0;
                while ($j < 3)
                {
                    $i = -1;
                    while ($i <= 1)
                    {
                        $level->setBlock(new Position(intval($p_centerPos->x - $i), intval($p_centerPos->y + $j), intval($p_centerPos->z + $i)), new Block(BlockIds::PRISMARINE));
                        $i++;
                    }
                    $j++;
                }
                break;
        }
    }

    private function stairFunction(float $p_wallAngle, Position $p_centerPos)
    {
        $level = $p_centerPos->level;

        switch ($p_wallAngle)
        {
            case (0):
                $j = 0;
                while ($j < 3)
                {
                    $i = -2;
                    while ($i <= 2)
                    {
                        $level->setBlock(new Position(intval($p_centerPos->x + $i), intval($p_centerPos->y + $j), intval($p_centerPos->z + $j)), new Block(BlockIds::PRISMARINE));
                        $i++;
                    }
                    $j++;
                }
                break;
            case (1):
                $max = 1;
                while ($max <= 3)
                {
                    $i = 0;
                    $level->setBlock(new Position(intval($p_centerPos->x + $max), intval($p_centerPos->y + $max -1), intval($p_centerPos->z + $i)), new Block(BlockIds::PRISMARINE));
                    while ($i < $max)
                    {
                        $level->setBlock(new Position(intval($p_centerPos->x + $i), intval($p_centerPos->y + $max -1), intval($p_centerPos->z + $max)), new Block(BlockIds::PRISMARINE));
                        $i++;
                        $level->setBlock(new Position(intval($p_centerPos->x + $max), intval($p_centerPos->y + $max -1), intval($p_centerPos->z + $i)), new Block(BlockIds::PRISMARINE));
                    }
                    $max++;
                }
                break;
            case (2):
                $j = 0;
                while ($j < 3)
                {
                    $i = -2;
                    while ($i <= 2)
                    {
                        $level->setBlock(new Position(intval($p_centerPos->x + $j), intval($p_centerPos->y + $j), intval($p_centerPos->z + $i)), new Block(BlockIds::PRISMARINE));
                        $i++;
                    }
                    $j++;
                }
                break;
            case (3):
                $max = 1;
                while ($max <= 3)
                {
                    $i = 0;
                    $level->setBlock(new Position(intval($p_centerPos->x + $i), intval($p_centerPos->y + $max -1), intval($p_centerPos->z - $max)), new Block(BlockIds::PRISMARINE));
                    while ($i < $max)
                    {
                        $level->setBlock(new Position(intval($p_centerPos->x + $max), intval($p_centerPos->y + $max -1), intval($p_centerPos->z - $i)), new Block(BlockIds::PRISMARINE));
                        $i++;
                        $level->setBlock(new Position(intval($p_centerPos->x + $i), intval($p_centerPos->y + $max -1), intval($p_centerPos->z - $max)), new Block(BlockIds::PRISMARINE));
                    }
                    $max++;
                }
                break;
            case (-1):
                $max = 1;
                while ($max <= 3)
                {
                    $i = 0;
                    $level->setBlock(new Position(intval($p_centerPos->x - $i), intval($p_centerPos->y + $max -1), intval($p_centerPos->z + $max)), new Block(BlockIds::PRISMARINE));
                    while ($i < $max)
                    {
                        $level->setBlock(new Position(intval($p_centerPos->x - $max), intval($p_centerPos->y + $max -1), intval($p_centerPos->z + $i)), new Block(BlockIds::PRISMARINE));
                        $i++;
                        $level->setBlock(new Position(intval($p_centerPos->x - $i), intval($p_centerPos->y + $max -1), intval($p_centerPos->z + $max)), new Block(BlockIds::PRISMARINE));
                    }
                    $max++;
                }
                break;
            case (-2):
                $j = 0;
                while ($j < 3)
                {
                    $i = -2;
                    while ($i <= 2)
                    {
                        $level->setBlock(new Position(intval($p_centerPos->x - $j), intval($p_centerPos->y + $j), intval($p_centerPos->z + $i)), new Block(BlockIds::PRISMARINE));
                        $i++;
                    }
                    $j++;
                }
                break;
            case (-3):
                $max = 1;
                while ($max <= 3)
                {
                    $i = 0;
                    $level->setBlock(new Position(intval($p_centerPos->x - $max), intval($p_centerPos->y + $max -1), intval($p_centerPos->z - $i)), new Block(BlockIds::PRISMARINE));
                    while ($i < $max)
                    {
                        $level->setBlock(new Position(intval($p_centerPos->x - $i), intval($p_centerPos->y + $max -1), intval($p_centerPos->z - $max)), new Block(BlockIds::PRISMARINE));
                        $i++;
                        $level->setBlock(new Position(intval($p_centerPos->x - $max), intval($p_centerPos->y + $max -1), intval($p_centerPos->z - $i)), new Block(BlockIds::PRISMARINE));
                    }
                    $max++;
                }
                break;
            case (4):
                $j = -2;
                while ($j <= 2)
                {
                    $i = 0;
                    while ($i < 3)
                    {
                        $level->setBlock(new Position(intval($p_centerPos->x - $j), intval($p_centerPos->y + $i), intval($p_centerPos->z - $i)), new Block(BlockIds::PRISMARINE));
                        $i++;
                    }
                    $j++;
                }
                break;

        }
    }
}