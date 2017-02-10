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


namespace pocketmine\level\format\io;

use pocketmine\level\format\Chunk;
use pocketmine\Thread;
use pocketmine\utils\Binary;

abstract class ChunkIoThread extends Thread{

	protected $shutdown = false;

	protected $loadRequests;
	protected $chunkSaveBuffer;
	protected $chunkLoadBuffer;

	protected $logger;

	public function __construct(\ThreadedLogger $logger){
		$this->loadRequests = new \Threaded;
		$this->chunkSaveBuffer = new \Threaded;
		$this->chunkLoadBuffer = new \Threaded;
		$this->logger = $logger;
	}

	public function run(){
		while(!$this->shutdown){
			$this->processChunkLoadRequests();
			$this->processChunkSaveRequests();
		}

		if($this->chunkSaveBuffer->count() > 0){
			$this->logger->info("Still chunks in the buffer to save, saving everything before stopping");
			$this->processChunkSaveRequests();
		}

		$this->close();
	}

	/**
	 * Tells the thread to shutdown. All current save requests will be completed before the thread exits.
	 *
	 * This method should be called by Levels when they are unloaded.
	 */
	public function shutdown(){
		$this->shutdown = true;
	}

	/**
	 * Shifts a serialized chunk from the load buffer and returns it. This method should be called from the main thread.
	 * @return string|null
	 */
	public function getChunkFromBuffer(){
		return $this->chunkLoadBuffer->shift();
	}

	/**
	 * Encodes a chunk request to a binary string.
	 *
	 * @param int $x
	 * @param int $z
	 * @param int $dimension
	 *
	 * @return string
	 */
	protected static function chunkIndex(int $x, int $z, int $dimension = 0) : string{
		return Binary::writeInt($x) . Binary::writeInt($z) . Binary::writeInt($dimension);
	}

	/**
	 * Decodes a chunk request binary string.
	 *
	 * @param string $index
	 * @param int &$x
	 * @param int &$z
	 * @param int &$dimension
	 */
	protected static function decodeChunkIndex(string $index, &$x, &$z, &$dimension){
		$x = Binary::readInt(substr($index, 0, 4));
		$z = Binary::readInt(substr($index, 4, 4));
		$dimension = Binary::readInt(substr($index, 8, 4));
	}

	/**
	 * Submits a chunk load request. This method should be called from the main thread.
	 *
	 * @param int $x
	 * @param int $z
	 * @param int $dimension
	 */
	public function requestLoadChunk(int $x, int $z, int $dimension = 0){
		if($this->shutdown){
			throw new \InvalidStateException("Attempted to submit a chunk load request after thread quit");
		}

		$index = static::chunkIndex($x, $z, $dimension);
		if(isset($this->chunkLoadBuffer[$index])){
			//Chunk is already in the load buffer, do not submit another request
		}elseif(isset($this->chunkSaveBuffer[$index])){
			//Chunk is the save buffer, return that copy (buffered version will be more up to date than disk version)
			$this->chunkLoadBuffer[$index] = $this->chunkSaveBuffer[$index];
		}else{
			$this->loadRequests[] = $index;
		}
	}

	/**
	 * Submits a chunk to save to disk. This method should be called from the main thread.
	 *
	 * @param Chunk $chunk
	 */
	public function requestSaveChunk(Chunk $chunk){
		if($this->shutdown){
			throw new \InvalidStateException("Attempted to submit a chunk save request after thread quit");
		}

		//The same chunk may have already been submitted for saving, overwrite the buffered version with the new version
		$this->chunkSaveBuffer[static::chunkIndex($chunk->getX(), $chunk->getZ(), 0)] = $chunk->fastSerialize();
	}

	/**
	 * Processes chunk load requests and pushes chunks into the outgoing buffer.
	 */
	protected function processChunkLoadRequests(){
		while(($request = $this->loadRequests->shift()) !== null){
			try{
				$this->chunkLoadBuffer[$request] = $this->readChunk($request)->fastSerialize();
			}catch(\Throwable $e){
				$this->logger->critical("An exception occurred while attempting to load a chunk (corrupted?)");
				$this->logger->logException($e);
			}
		}
	}

	/**
	 * Processes chunk save requests from the incoming buffer.
	 */
	protected function processChunkSaveRequests(){
		while(($data = $this->chunkSaveBuffer->shift()) !== null){
			try{
				$this->writeChunk(Chunk::fastDeserialize($data));
			}catch(\Throwable $e){
				$this->logger->critical("An exception occurred while attempting to save a chunk");
				$this->logger->logException($e);
			}
		}
	}

	/**
	 * Reads a chunk from disk.
	 *
	 * @param string $index
	 *
	 * @return Chunk|null
	 *
	 * @throws \Throwable for chunk decode errors (corruption?)
	 */
	protected abstract function readChunk(string $index);

	/**
	 * Writes a chunk to disk.
	 *
	 * @param Chunk $chunk
	 */
	protected abstract function writeChunk(Chunk $chunk);

	/**
	 * Performs necessary cleanup and resource freeing when the thread is stopped.
	 */
	protected abstract function close();
}