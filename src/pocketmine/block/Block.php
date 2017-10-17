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

/**
 * All Block classes are in here
 */
namespace pocketmine\block;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\Tool;
use pocketmine\level\Level;
use pocketmine\level\MovingObjectPosition;
use pocketmine\level\Position;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\metadata\Metadatable;
use pocketmine\metadata\MetadataValue;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

class Block extends Position implements BlockIds, Metadatable{

	/**
	 * Returns a new Block instance with the specified ID, meta and position.
	 *
	 * This function redirects to {@link BlockFactory#get}.
	 *
	 * @param int           $id
	 * @param int           $meta
	 * @param Position|null $pos
	 *
	 * @return Block
	 */
	public static function get(int $id, int $meta = 0, Position $pos = null) : Block{
		return BlockFactory::get($id, $meta, $pos);
	}

	/** @var int */
	protected $id;
	/** @var int */
	protected $meta = 0;
	/** @var string|null */
	protected $fallbackName;
	/** @var int|null */
	protected $itemId;

	/** @var AxisAlignedBB */
	public $boundingBox = null;


	/** @var AxisAlignedBB[]|null */
	protected $collisionBoxes = null;

	/**
	 * @param int         $id     The block type's ID, 0-255
	 * @param int         $meta   Meta value of the block type
	 * @param string|null $name   English name of the block type (TODO: implement translations)
	 * @param int         $itemId The item ID of the block type, used for block picking and dropping items.
	 */
	public function __construct(int $id, int $meta = 0, string $name = null, int $itemId = null){
		$this->id = $id;
		$this->meta = $meta;
		$this->fallbackName = $name;
		$this->itemId = $itemId;
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return $this->fallbackName ?? "Unknown";
	}

	/**
	 * @return int
	 */
	final public function getId() : int{
		return $this->id;
	}

	/**
	 * Returns the ID of the item form of the block.
	 * Used for drops for blocks (some blocks such as doors have a different item ID).
	 *
	 * @return int
	 */
	public function getItemId() : int{
		return $this->itemId ?? $this->getId();
	}

	/**
	 * @return int
	 */
	final public function getDamage() : int{
		return $this->meta;
	}

	/**
	 * @param int $meta
	 */
	final public function setDamage(int $meta) : void{
		if($meta < 0 or $meta > 0xf){
			throw new \InvalidArgumentException("Block damage values must be 0-15, not $meta");
		}
		$this->meta = $meta;
	}

	/**
	 * Bitmask to use to remove superfluous information from block meta when getting its item form or name.
	 * This defaults to -1 (don't remove any data). Used to remove rotation data and bitflags from block drops.
	 *
	 * If your block should not have any meta value when it's dropped as an item, override this to return 0 in
	 * descendent classes.
	 *
	 * @return int
	 */
	public function getVariantBitmask() : int{
		return -1;
	}

	/**
	 * Returns the block meta, stripped of non-variant flags.
	 * @return int
	 */
	public function getVariant() : int{
		return $this->meta & $this->getVariantBitmask();
	}


	/**
	 * AKA: Block->isPlaceable
	 * @return bool
	 */
	public function canBePlaced() : bool{
		return true;
	}

	/**
	 * @return bool
	 */
	public function canBeReplaced() : bool{
		return false;
	}

	public function canBePlacedAt(Block $blockReplace, Vector3 $clickVector) : bool{
		return $blockReplace->canBeReplaced();
	}

	/**
	 * Places the Block, using block space and block target, and side. Returns if the block has been placed.
	 *
	 * @param Item        $item
	 * @param Block       $blockReplace
	 * @param Block       $blockClicked
	 * @param int         $face
	 * @param Vector3     $facePos
	 * @param Player|null $player
	 *
	 * @return bool
	 */
	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $facePos, Player $player = null) : bool{
		return $this->getLevel()->setBlock($this, $this, true, true);
	}

	/**
	 * Returns if the block can be broken with an specific Item
	 *
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function isBreakable(Item $item) : bool{
		return true;
	}

	public function canBeBrokenWith(Item $item) : bool{
		return $this->getHardness() !== -1;
	}

	/**
	 * Do the actions needed so the block is broken with the Item
	 *
	 * @param Item        $item
	 * @param Player|null $player
	 *
	 * @return bool
	 */
	public function onBreak(Item $item, Player $player = null) : bool{
		return $this->getLevel()->setBlock($this, BlockFactory::get(Block::AIR), true, true);
	}


	/**
	 * Returns the seconds that this block takes to be broken using an specific Item
	 *
	 * @param Item $item
	 *
	 * @return float
	 */
	public function getBreakTime(Item $item) : float{
		$base = $this->getHardness() * 1.5;
		if($this->canBeBrokenWith($item)){
			if($this->getToolType() === Tool::TYPE_SHEARS and $item->isShears()){
				$base /= 15;
			}elseif(
				($this->getToolType() === Tool::TYPE_PICKAXE and ($tier = $item->isPickaxe()) !== false) or
				($this->getToolType() === Tool::TYPE_AXE and ($tier = $item->isAxe()) !== false) or
				($this->getToolType() === Tool::TYPE_SHOVEL and ($tier = $item->isShovel()) !== false)
			){
				switch($tier){
					case Tool::TIER_WOODEN:
						$base /= 2;
						break;
					case Tool::TIER_STONE:
						$base /= 4;
						break;
					case Tool::TIER_IRON:
						$base /= 6;
						break;
					case Tool::TIER_DIAMOND:
						$base /= 8;
						break;
					case Tool::TIER_GOLD:
						$base /= 12;
						break;
				}
			}
		}else{
			$base *= 3.33;
		}

		if($item->isSword()){
			$base *= 0.5;
		}

		return $base;
	}


	/**
	 * Returns whether random block updates will be done on this block.
	 *
	 * @return bool
	 */
	public function ticksRandomly() : bool{
		return false;
	}

	/**
	 * Fires a block update on the Block
	 *
	 * @param int $type
	 *
	 * @return bool|int
	 */
	public function onUpdate(int $type){
		return false;
	}

	/**
	 * Do actions when activated by Item. Returns if it has done anything
	 *
	 * @param Item        $item
	 * @param Player|null $player
	 *
	 * @return bool
	 */
	public function onActivate(Item $item, Player $player = null) : bool{
		return false;
	}

	/**
	 * Returns a base value used to compute block break times.
	 * @return float
	 */
	public function getHardness() : float{
		return 10;
	}

	/**
	 * @deprecated
	 * @return float
	 */
	public function getResistance() : float{
		return $this->getBlastResistance();
	}

	/**
	 * Returns the block's resistance to explosions. Usually 5x hardness.
	 * @return float
	 */
	public function getBlastResistance() : float{
		return $this->getHardness() * 5;
	}

	/**
	 * @return int
	 */
	public function getToolType() : int{
		return Tool::TYPE_NONE;
	}

	/**
	 * @return float
	 */
	public function getFrictionFactor() : float{
		return 0.6;
	}

	/**
	 * @return int 0-15
	 */
	public function getLightLevel() : int{
		return 0;
	}

	/**
	 * Returns the amount of light this block will filter out when light passes through this block.
	 * This value is used in light spread calculation.
	 *
	 * @return int 0-15
	 */
	public function getLightFilter() : int{
		return 15;
	}

	/**
	 * Returns whether this block will diffuse sky light passing through it vertically.
	 * Diffusion means that full-strength sky light passing through this block will not be reduced, but will start being filtered below the block.
	 * Examples of this behaviour include leaves and cobwebs.
	 *
	 * Light-diffusing blocks are included by the heightmap.
	 *
	 * @return bool
	 */
	public function diffusesSkyLight() : bool{
		return false;
	}

	/**
	 * @return bool
	 */
	public function isTransparent() : bool{
		return false;
	}

	public function isSolid() : bool{
		return true;
	}

	/**
	 * AKA: Block->isFlowable
	 * @return bool
	 */
	public function canBeFlowedInto() : bool{
		return false;
	}

	public function hasEntityCollision() : bool{
		return false;
	}

	public function canPassThrough() : bool{
		return false;
	}

	/**
	 * Returns whether entities can climb up this block.
	 * @return bool
	 */
	public function canClimb() : bool{
		return false;
	}


	public function addVelocityToEntity(Entity $entity, Vector3 $vector) : void{

	}

	/**
	 * Sets the block position to a new Position object
	 *
	 * @param Position $v
	 */
	final public function position(Position $v) : void{
		$this->x = (int) $v->x;
		$this->y = (int) $v->y;
		$this->z = (int) $v->z;
		$this->level = $v->level;
		$this->boundingBox = null;
	}

	/**
	 * Returns an array of Item objects to be dropped
	 *
	 * @param Item $item
	 *
	 * @return Item[]
	 */
	public function getDrops(Item $item) : array{
		return [
			ItemFactory::get($this->getItemId(), $this->getVariant(), 1)
		];
	}

	/**
	 * Returns the time in ticks which the block will fuel a furnace for.
	 * @return int
	 */
	public function getFuelTime() : int{
		return 0;
	}

	/**
	 * Returns the Block on the side $side, works like Vector3::side()
	 *
	 * @param int $side
	 * @param int $step
	 *
	 * @return Block
	 */
	public function getSide($side, $step = 1){
		if($this->isValid()){
			return $this->getLevel()->getBlock(Vector3::getSide($side, $step));
		}

		return BlockFactory::get(Block::AIR, 0, Position::fromObject(Vector3::getSide($side, $step)));
	}

	/**
	 * Returns the 4 blocks on the horizontal axes around the block (north, south, east, west)
	 *
	 * @return Block[]
	 */
	public function getHorizontalSides() : array{
		return [
			$this->getSide(Vector3::SIDE_NORTH),
			$this->getSide(Vector3::SIDE_SOUTH),
			$this->getSide(Vector3::SIDE_WEST),
			$this->getSide(Vector3::SIDE_EAST)
		];
	}

	/**
	 * Returns the six blocks around this block.
	 *
	 * @return Block[]
	 */
	public function getAllSides() : array{
		return array_merge(
			[
				$this->getSide(Vector3::SIDE_DOWN),
				$this->getSide(Vector3::SIDE_UP)
			],
			$this->getHorizontalSides()
		);
	}

	/**
	 * @return string
	 */
	public function __toString(){
		return "Block[" . $this->getName() . "] (" . $this->getId() . ":" . $this->getDamage() . ")";
	}

	/**
	 * Checks for collision against an AxisAlignedBB
	 *
	 * @param AxisAlignedBB $bb
	 *
	 * @return bool
	 */
	public function collidesWithBB(AxisAlignedBB $bb) : bool{
		$bbs = $this->getCollisionBoxes();

		foreach($bbs as $bb2){
			if($bb->intersectsWith($bb2)){
				return true;
			}
		}

		return false;
	}

	/**
	 * @param Entity $entity
	 */
	public function onEntityCollide(Entity $entity) : void{

	}

	/**
	 * @return AxisAlignedBB[]
	 */
	public function getCollisionBoxes() : array{
		if($this->collisionBoxes === null){
			$this->collisionBoxes = $this->recalculateCollisionBoxes();
		}

		return $this->collisionBoxes;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		if($bb = $this->recalculateBoundingBox()){
			return [$bb];
		}

		return [];
	}

	/**
	 * @return AxisAlignedBB|null
	 */
	public function getBoundingBox() : ?AxisAlignedBB{
		if($this->boundingBox === null){
			$this->boundingBox = $this->recalculateBoundingBox();
		}
		return $this->boundingBox;
	}

	/**
	 * @return AxisAlignedBB|null
	 */
	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		return new AxisAlignedBB(
			(float)$this->x,
			(float)$this->y,
			(float)$this->z,
			(float)$this->x + 1,
			(float)$this->y + 1,
			(float)$this->z + 1
		);
	}

	/**
	 * Clears any cached precomputed bounding boxes. This is called on block neighbour update and when the block is set
	 * into the world to remove any outdated precomputed AABBs and force recalculation.
	 */
	public function clearBoundingBoxes() : void{
		$this->boundingBox = null;
		$this->collisionBoxes = null;
	}

	/**
	 * @param Vector3 $pos1
	 * @param Vector3 $pos2
	 *
	 * @return MovingObjectPosition|null
	 */
	public function calculateIntercept(Vector3 $pos1, Vector3 $pos2) : ?MovingObjectPosition{
		$bbs = $this->getCollisionBoxes();
		if(empty($bbs)){
			return null;
		}

		/** @var MovingObjectPosition|null $currentHit */
		$currentHit = null;
		/** @var int|float $currentDistance */
		$currentDistance = PHP_INT_MAX;

		foreach($bbs as $bb){
			$nextHit = $bb->calculateIntercept($pos1, $pos2);
			if($nextHit === null){
				continue;
			}

			$nextDistance = $nextHit->hitVector->distanceSquared($pos1);
			if($nextDistance < $currentDistance){
				$currentHit = $nextHit;
				$currentDistance = $nextDistance;
			}
		}

		if($currentHit !== null){
			$currentHit->blockX = $this->x;
			$currentHit->blockY = $this->y;
			$currentHit->blockZ = $this->z;
		}

		return $currentHit;
	}

	public function setMetadata(string $metadataKey, MetadataValue $newMetadataValue){
		if($this->getLevel() instanceof Level){
			$this->getLevel()->getBlockMetadata()->setMetadata($this, $metadataKey, $newMetadataValue);
		}
	}

	public function getMetadata(string $metadataKey){
		if($this->getLevel() instanceof Level){
			return $this->getLevel()->getBlockMetadata()->getMetadata($this, $metadataKey);
		}

		return null;
	}

	public function hasMetadata(string $metadataKey) : bool{
		if($this->getLevel() instanceof Level){
			return $this->getLevel()->getBlockMetadata()->hasMetadata($this, $metadataKey);
		}

		return false;
	}

	public function removeMetadata(string $metadataKey, Plugin $owningPlugin){
		if($this->getLevel() instanceof Level){
			$this->getLevel()->getBlockMetadata()->removeMetadata($this, $metadataKey, $owningPlugin);
		}
	}
}
