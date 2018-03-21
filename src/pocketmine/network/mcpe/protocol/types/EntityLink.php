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

namespace pocketmine\network\mcpe\protocol\types;

class EntityLink{

	/** @var int */
	public $fromEntityUniqueId;
	/** @var int */
	public $toEntityUniqueId;
	/** @var int */
	public $type;
	/** @var bool */
	public $bool1;

	public function __construct(int $fromEntityUniqueId = null, int $toEntityUniqueId = null, int $type = null, bool $bool1 = TRUE){
		$this->fromEntityUniqueId = $fromEntityUniqueId;
		$this->toEntityUniqueId = $toEntityUniqueId;
		$this->type = $type;
		$this->bool1 = $bool1;
	}
}