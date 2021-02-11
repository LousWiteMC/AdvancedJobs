<?php

namespace LousWiteMC\AdvancedJobs\event;

use pocketmine\event\Listener;
use pocketmine\event\PlayerJoinEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Player;
use LousWiteMC\AdvancedJobs\AdvancedJobs;
use pocketmine\block\{Wood, Wood2, DiamondOre, Diamond, Iron, IronOre, Gold, GoldOre, Emerald, EmeraldOre, Stone, Cobblestone, Redstone, RedstoneOre, Coal, CoalOre, Lapis, LapisOre};
use pocketmine\entity\{Animal, Monster};

class EventListener implements Listener{

	public $plugin;

	public function __construct(AdvancedJobs $plugin){
		$this->plugin = $plugin;
	}

	public function onBreak(BlockBreakEvent $event){
		$player = $event->getPlayer();
		$block = $event->getBlock();
		if($block instanceof Wood or $block instanceof Wood2){
			$debug = $this->plugin->debug($player);
			var_dump($debug);
			if($debug == "true"){
				if($this->plugin->hasJob($player)){
					if($this->plugin->getJob($player) == "wood-cutter"){
						$this->plugin->addProgress($player);
					}
				}
			}
		}elseif($block instanceof Diamond or $block instanceof DiamondOre or $block instanceof Iron or $block instanceof IronOre or $block instanceof Gold or $block instanceof GoldOre or $block instanceof Emerald or $block instanceof EmeraldOre or $block instanceof Stone or $block instanceof Cobblestone or $block instanceof Redstone or $block instanceof RedstoneOre or $block instanceof Coal or $block instanceof CoalOre or $block instanceof Lapis or $block instanceof LapisOre){
			$debug = $this->plugin->debug($player);
			if($debug == "true"){
				if($this->plugin->hasJob($player)){
					if($this->plugin->getJob($player) == "miner"){
						$this->plugin->addProgress($player);
					}
				}
			}
		}
	}

	public function onPlace(BlockPlaceEvent $event){
		$player = $event->getPlayer();
		if($this->plugin->hasJob($player)){
			$debug = $this->plugin->debug($player);
			if($debug == "true"){
				if($this->plugin->getJob($player) == "builder"){
					$this->plugin->addProgress($player);
				}
			}
		}
	}
	public function onKill(PlayerDeathEvent $event){
		$entity = $event->getEntity();
		$cause = $entity->getLastDamageCause();
		if($cause instanceof EntityDamageByEntityEvent){
			$player = $cause->getDamager();
			if($player instanceof Player){
				if($this->plugin->hasJob($player)){
					if($entity instanceof Player){
						$debug = $this->plugin->debug($player);
						if($debug == "true"){
							if($this->plugin->getJob($player) == "killer"){
								$jobId = $this->plugin->getJobID($player);
								$money = $this->plugin->jobs->get($jobId)["Salary"];
								$player->sendPopup("+{$money}$ For Job!");
								$this->plugin->money->addMoney($player, $money);
							}
						}
					}
				}
			}
		}
	}
}
