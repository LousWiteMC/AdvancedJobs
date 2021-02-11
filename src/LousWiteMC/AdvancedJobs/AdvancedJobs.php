<?php
namespace LousWiteMC\AdvancedJobs;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\Player;
use LousWiteMC\AdvancedJobs\event\EventListener;
use pocketmine\utils\Config;
use pocketmine\command\{CommandSender, Command, ConsoleCommandSender};
use LousWiteMC\AdvancedJobs\libs\dktapps\pmforms\{MenuForm, CustomForm, CustomFormResponse, ModalForm, MenuOption};

class AdvancedJobs extends PluginBase{

	public $data;

	public $jobs;

	public $settings;

	public $money;

	public $st;

	public function onEnable() : void{
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$this->load();
	}

	public function load() : void{
		$this->data = new Config($this->getDataFolder() . "PlayerData.yml", Config::YAML);
		$this->saveResource("Jobs.yml");
		$this->saveResource("Settings.yml");
		$this->saveResource("Language.yml");
		$this->jobs = new Config($this->getDataFolder() . "Jobs.yml", Config::YAML);
		$this->st = new Config($this->getDataFolder() . "Settings.yml", Config::YAML);
		$this->settings = new Config($this->getDataFolder() . "Language.yml");
		$this->money = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
		if(is_null($this->money)){
			$this->getServer()->getPluginManager()->disablePlugin($this);
			$this->getServer()->getLogger()->notice("[AdvancedJobs] This plugin required EconomyAPI plugin!\n[AdvancedJobs] You can install it at https://poggit.pmmp.io/p/EconomyAPI/5.7.2");
		}
	}

	public function debug(Player $player) : string {
		$level = $player->getLevel()->getName();
		$debuglevel = array($this->st->get("Debug-Worlds"));
		var_dump($level);
		foreach($debuglevel as $dblv){
			var_dump($dblv);
			if(!(in_array($level, $dblv))){
				return "true";
			}
			return "false";
		}
	}

	public function joinJob(Player $player, int $idJob){
		$name = strtolower($player->getName());
		$jobName = $this->jobs->get($idJob)["Name"];
		$jobSalary = $this->jobs->get($idJob)["Current-Salary"];
		if($this->getJobType($idJob) == "killer"){
			$this->data->set($name, ["JobID" => "{$idJob}", "JobName" => "{$jobName}", "Progress" => 0, "Default-Next-Progress" => 0, "Salary" => $jobSalary]);
			$this->data->save();
		}else{
			$this->data->set($name, ["JobID" => "{$idJob}", "JobName" => "{$jobName}", "Progress" => 0, "Default-Next-Progress" => 1, "Salary" => $jobSalary]);
			$this->data->save();
		}
	}

	public function getJobName(int $idJob){
		return $this->jobs->get($idJob)["Name"];
	}

	public function getJobID(Player $player){
		$name = strtolower($player->getName());
		if($this->data->exists($name)){
			return strval($this->data->get($name)["JobID"]);
		}else{
			return null;
		}
	}

	public function getJobType(int $jobId){
		return $this->jobs->get($jobId)["Job-Type"];
	}

	public function getJobInformation(int $jobId){
		return $this->jobs->get($jobId)["Information"];
	}

	public function hasJob(Player $player){
		return $this->data->exists(strtolower($player->getName()));
	}

	public function getJob(Player $player){
		$name = strtolower($player->getName());
		$jobid = $this->getJobID($player);
		return $this->getJobType($jobid);
	}

	public function outJob(Player $player){
		$name = strtolower($player->getName());
		$this->data->remove($name);
		$this->data->save();
	}

	public function getDefaultNextProgress(Player $player){
		$name = strtolower($player->getName());
		return $this->data->get($name)["Default-Next-Progress"];
	}

	public function setNextDefaultProgress(Player $player, int $progress){
		$money = $this->getSalary($player);
		$name = strtolower($player->getName());
		$this->data->set($name, ["JobID" => $this->getJobID($player), "JobName" => $this->getJobName($this->getJobID($player)), "Progress" => $this->getProgress($player), "Default-Next-Progress" => $progress, "Salary" => $money]);
		$this->data->save();
	}

	public function setProgress(Player $player, int $progress){
		$money = $this->getSalary($player);
		$name = strtolower($player->getName());
		$this->data->set($name, ["JobID" => $this->getJobID($player), "JobName" => $this->getJobName($this->getJobID($player)), "Progress" => $progress, "Default-Next-Progress" => $this->getDefaultNextProgress($player), "Salary" => $money]);
		$this->data->save();
	}

	public function setNextProgress(Player $player, int $progress){
		$money = $this->getSalary($player);
		$name = strtolower($player->getName());
		$this->data->set($name, ["JobID" => $this->getJobID($player), "JobName" => $this->getJobName($this->getJobID($player)), "Progress" => $this->getProgress($player), "Default-Next-Progress" => $progress, "Salary" => $money]);
		$this->data->save();
	}

	public function getProgress(Player $player){
		$name = strtolower($player->getName());
		return $this->data->get($name)["Progress"];
	}

	public function getSalary(Player $player) : int{
		$name = strtolower($player->getName());
		return $this->data->get($name)["Salary"];
	}

	public function setSalary(Player $player, int $money){
		$name = strtolower($player->getName());
		$this->data->set($name, ["JobID" => $this->getJobID($player), "JobName" => $this->getJobName($this->getJobID($player)), "Progress" => $this->getProgress($player), "Default-Next-Progress" => $this->getDefaultNextProgress($player), "Salary" => $money]);
		$this->data->save();
	}

	public function getNextProgress(Player $player){
		$name = strtolower($player->getName());
		$jobID = $this->getJobID($player);
		$jobType = $this->getJobType($jobID);
		$jobProgress = $this->getDefaultNextProgress($player);
		if($jobType == "killer"){
			return $jobProgress*3;
		}elseif($jobType == "wood-cutter"){
			return $jobProgress*4;
		}elseif($jobType == "miner"){
			return $jobProgress*4;
		}elseif($jobType == "builder"){
			return $jobProgress*4;
		}
	}

	public function addProgress(Player $player){
		$this->setProgress($player, $this->getProgress($player) + 1);
		$jobProgress = $this->getProgress($player);
		$jobNextProgress = $this->getNextProgress($player);
		$popup = str_replace(["{Progress}", "{NextProgress}"], [$jobProgress, $jobNextProgress], $this->settings->get("Working-Popup"));
		$player->sendPopup($popup);
		$jobId = $this->getJobID($player);
		if($jobProgress >= $jobNextProgress){
			$this->doneJob($player, $jobId);
		}
	}

	public function doneJob(Player $player, int $jobId){
		$name = strtolower($player->getName());
		$moresalary = $this->jobs->get($jobId)["More-Money-Per-Level"];
		$jobSalary = $this->data->get($name)["Salary"];
		$nextSalary = $moresalary + $jobSalary;
		$this->setProgress($player, 0);
		$this->setSalary($player, $nextSalary);
		$this->setNextDefaultProgress($player, $this->getDefaultNextProgress($player) +1);
		$this->money->addMoney($player, $jobSalary);
		$msg = str_replace(["\n", "{Salary}"], ["\n", $jobSalary], $this->settings->get("Done-Progress-Message"));
		$player->sendMessage($msg);
	}

	public function onCommand(CommandSender $player, Command $cmd, string $label, array $args) : bool{
		if($cmd->getName() == "job"){
			if($player instanceof Player){
				if($this->hasJob($player)){
					$this->JobManagerForm($player);
				}else{
					$this->IntroduceForm($player);
					return true;
				}
			}else{
				$player->sendMessage("Please use this in-game");
				return true;
			}
		}
		return true;
	}

	public function IntroduceForm(Player $player){
		$form = new MenuForm(
			$this->settings->get("IntroForm-Title"),
			$this->settings->get("IntroForm-Content"),
			[
				new MenuOption($this->settings->get("IntroForm-ChooseJob-Button"))
			],
			function(Player $submitter, int $selected) : void{
				if($selected == 0){
					$this->ChooseJob($submitter);
				}
			}
		);
		$player->sendForm($form);
	}

	public function JobManagerForm(Player $player){
		$name = strtolower($player->getName());
		$jobID = $this->getJobID($player);
		$jobName = $this->getJobName($jobID);
		$jobInf = $this->getJobInformation($jobID);
		$jobProgress = $this->getProgress($player);
		$jobNextProgress = $this->getNextProgress($player);
		$salary = $this->getSalary($player);
		$content = str_replace(["{JobName}", "{JobInformation}", "{JobProgress}", "{JobNextProgress}", "\n", "{Salary}"], [$jobName, $jobInf, $jobProgress, $jobNextProgress, "\n", $salary], $this->settings->get("ManagerForm-Content"));
		$form = new MenuForm(
			$this->settings->get("ManagerForm-Title"),
			$content,
			[
				new MenuOption($this->settings->get("ManagerForm-QuitJob-Button"))
			],
			function(Player $submitter, int $selected) use ($jobID) : void{
				$this->ConfirmOutJob($submitter, $jobID);
			}
		);
		$player->sendForm($form);
	}

	public function getButtons() : array{
		$buttons = [];
		foreach($this->jobs->getAll() as $all){
			$buttons[] = new MenuOption($all["Name"]);
		}
		return $buttons;
	}

	public function ChooseJob(Player $player){
			$form = new MenuForm(
				$this->settings->get("ChooseJobForm-Title"),
				$this->settings->get("ChooseJobForm-Content"),
				$this->getButtons(),
				function(Player $submitter, int $selected) : void{
					$jobId = $selected +1;
					$this->joinJob($submitter, $jobId);
					$jobName = $this->getJobName($jobId);
					$msg = str_replace("{JobName}", $jobName, $this->settings->get("ChoosedJob-Message"));
					$submitter->sendMessage($msg);
				}
			);
			$player->sendForm($form);
	}

	public function ConfirmOutJob(Player $player, int $jobId){
		$jobName = $this->jobs->get($jobId)["Name"];
		$content = str_replace("{JobName}", $jobName, $this->settings->get("OutJobForm-Content"));
		$form = new ModalForm(
		$this->settings->get("OutJobForm-Title"),
		$content,
		function(Player $submitter, bool $selected) :void{
			switch($selected){
				case true:
				$this->outJob($submitter);
				$submitter->sendMessage($this->settings->get("Success-OutJob-Message"));
				return;
				case false:
				return;
			}
		},
		$this->settings->get("Yes-Button"),
		$this->settings->get("No-Button"));
		$player->sendForm($form);
	}
	public function onDisable(){
	}
}
