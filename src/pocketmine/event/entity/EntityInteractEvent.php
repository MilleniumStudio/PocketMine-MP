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

namespace pocketmine\event\entity;

use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;

class EntityInteractEvent extends EntityEvent implements Cancellable{
    public static $handlerList = null;

    /** @var Vehicle */
    private $target;

    private $cancelled = false;

    public function __construct(Entity $source, Entity $target){
            $this->entity = $source;
            $this->target = $target;
    }

    /**
     * @return Vehicle
     */
    public function getTarget() : Entity{
            return $this->target;
    }

    /**
	 * @return bool
	 */
	public function isCancelled() : bool
        {
            return $this->cancelled;
        }

	/**
	 * @param bool $value
	 */
	public function setCancelled(bool $value = true)
        {
            $this->cancelled = $value;
        }
}