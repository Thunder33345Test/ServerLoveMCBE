<?php

namespace ServerLoveMCBE;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\level\Level;
use pocketmine\level\particle\HeartParticle;
use pocketmine\level\particle\AngryVillagerParticle;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;


class Main extends PluginBase implements Listener{

    /** @var $nolove Config */
    private $nolove;
    /** @var $config Config */
    private $config;

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        // Make plugin data folder
        if (!is_dir($this->getDataFolder())) {
            @mkdir($this->getDataFolder());
        }

        $this->getLogger()->info("Loading configs...");

        $this->nolove = new Config($this->getDataFolder() . "nolove.yml", Config::YAML);
        $this->config = new Config($this->getDataFolder() . "love.yml", Config::YAML);
    }


    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch ($command->getName()) {

            case "love":
                if (!(isset($args[0]))) {
                    return false;
                }
                if (!($sender instanceof Player)) {
                    $sender->sendMessage("§5[<3] YOU MUST USE THIS COMMAND IN GAME. SORRY.");
                    return true;
                }

                $loved = array_shift($args);
                $lovedPlayer = $this->getServer()->getPlayer($loved);

                if ($lovedPlayer !== null and $lovedPlayer->isOnline()) {
                    $loved = $lovedPlayer->getName();

                    if ($this->nolove->exists(strtolower($loved))) {
                        $sender->sendMessage("§5[<3]Sorry, " . $loved . "§5 is not looking to love anyone right now.");
                        return true;
                    } else {
                        if ($lovedPlayer === $sender) {
                            $sender->sendMessage("§5[<3]You can't love yourself!");

                        } else {

                            $loves = $this->config->get('players.' . strtolower($lovedPlayer->getName()), []);
                            if ($loves === null) $loves = [];
                            $lovenum = count($loves);
                            if (in_array(strtolower($sender->getName()), $loves, false)) {
                                $sender->sendMessage(TextFormat::RED . 'You already love that player.');
                                return true;
                            }
                            if (count($loves) >= 1) {
                                $sender->sendMessage(TextFormat::RED . 'You can\'t love another player!');
                                return true;
                            }
                            $loves[] = strtolower($sender->getName());
                            $this->config->set('players.' . strtolower($lovedPlayer->getName()), $loves);
                            $this->config->save();


                            if (isset($args[0])) {
                                $lovedPlayer->sendMessage("Reason: " . implode(" ", $args));
                            }
                            $this->getServer()->broadcastMessage("§a" . $sender->getName() . " §dis in love with §a" . $loved . "§d.");
                            for ($i = 10; $i >= 1; $i--) {
                                $pos1 = clone $sender->getPosition();
                                $pos1->x += mt_rand(-10, 10) / 10;
                                $pos1->y += $i;
                                $pos1->z += mt_rand(-10, 10) / 10;
                                $pos1->level->addParticle(new HeartParticle($pos1));
                                $pos2 = clone $lovedPlayer->getPosition();
                                $pos2->x += mt_rand(-10, 10) / 10;
                                $pos2->y += $i;
                                $pos2->z += mt_rand(-10, 10) / 10;
                                $pos2->level->addParticle(new HeartParticle($pos2));
                            }
                            return true;
                        }
                    }
                } else {
                    $sender->sendMessage("§a" . $loved . "§5 is not available for love. §a" . $loved . "§5 does not exist, or is not online.");
                    return true;
                }
                break;


            case "breakup":
                if (!(isset($args[0]))) {
                    return false;
                }
                if (!($sender instanceof Player)) {
                    $sender->sendMessage("§5[<3] YOU MUST USE THIS COMMAND IN GAME. SORRY.");
                    return true;
                }
                $loved = array_shift($args);
                $lovedPlayer = $this->getServer()->getPlayer($loved);
                if ($lovedPlayer !== null and $lovedPlayer->isOnline()) {
                    $loved = $lovedPlayer->getName();
                    $loves = $this->config->get('players.' . strtolower($lovedPlayer->getName()), []);
                    if ($loves === null) $loves = [];
                    if (!in_array(strtolower($sender->getName()), $loves, false)) {
                        $sender->sendMessage(TextFormat::RED . 'You don\'t love that player.');
                        return true;
                    }

                    foreach ($loves as $key => $value) {
                        if ($value === strtolower($sender->getName())) {
                            unset($loves[$key]);
                        }
                    }

                    if (count($loves) === 0) {
                        $this->config->remove('players.' . strtolower($lovedPlayer->getName()));
                    } else {
                        $this->config->set('players.' . strtolower($lovedPlayer->getName()), $loves);
                    }
                    $this->config->save();

                    if (isset($args[0])) {
                        $lovedPlayer->sendMessage("Reason: " . implode(" ", $args));
                    }
                    $this->getServer()->broadcastMessage("§a" . $sender->getName() . " §dhas broken up with §a" . $loved . "§d.");
                    for ($i = 10; $i >= -5; $i--) {
                        $pos1 = clone $sender->getPosition();
                        $pos1->x += mt_rand(-10, 10) / 10;
                        $pos1->y += $i;
                        $pos1->z += mt_rand(-10, 10) / 10;
                        $pos1->level->addParticle(new AngryVillagerParticle($pos1));
                        $pos2 = clone $lovedPlayer->getPosition();
                        $pos2->x += mt_rand(-10, 10) / 10;
                        $pos2->y += $i;
                        $pos2->z += mt_rand(-10, 10) / 10;
                        $pos2->level->addParticle(new AngryVillagerParticle($pos2));
                    }
                    return true;
                } else {
                    $sender->sendMessage($loved . "§5 is not available for a breakup. Basically, §a" . $loved . "§5 does not exist, or is not online.");
                    return true;
                }
                break;


            case "nolove":
                if (!(isset($args[0]))) {
                    return false;
                }
                if (!($sender instanceof Player)) {
                    $sender->sendMessage("§5[<3] YOU MUST USE THIS COMMAND IN GAME. SORRY.");
                    return true;
                }
                if ($args[0] == "nolove") {
                    $this->nolove->set(strtolower($sender->getName()));
                    $sender->sendMessage("§5[<3] You will no longer be loved. §e#ForEverAlone");
                    $this->nolove->save();
                    return true;
                } elseif ($args[0] == "love") {
                    $this->nolove->remove(strtolower($sender->getName()));
                    $sender->sendMessage("§5[<3] You will now be loved again! §e#GetInThere");
                    $this->nolove->save();
                    return true;
                } else {
                    return false;
                }
                break;


            case "serverlove":
                $sender->sendMessage("§d[<3] §rServerLoveMCBE Commands: ");
                $sender->sendMessage("§d[<3] §rUsage: /love <playerName>");
                $sender->sendMessage("§d[<3] §rUsage: /breakup <playerName>");
                $sender->sendMessage("§d[<3] §rUsage: /nolove <nolove | love> ");
                $sender->sendMessage("§d[<3] §rHappy Loving!");
                return true;
                break;
            default:
                return false;
        }
        return false;
    }
}
