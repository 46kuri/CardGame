<?php

namespace CardGame;

use pocketmine\item\Item;

use pocketmine\block\Block;

use pocketmine\event\Listener;

use pocketmine\{Player,server};

use pocketmine\plugin\PluginBase;

use pocketmine\command\{Command,CommandSender,CommandExecutor};

use pocketmine\item\enchantment\{Enchantment,EnchantmentInstance};

use pocketmine\event\entity\{EntityDamageEvent,EntityDamageByEntityEvent};

use pocketmine\event\player\{PlayerInteractEvent,PlayerJoinEvent,PlayerQuitEvent,PlayerItemHeldEvent};

class main extends PluginBase implements Listener{
	
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->notice("CardGameプラグインは使用可能です。お楽しみください 製作者:ghost_46");
    }

    public function onJoin(PlayerJoinEvent $event){
        $player = $event->getPlayer();
        $player->setMaxHealth(100);
        $player->setHealth(100);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
        switch(strtolower($command->getName())){
            case "cardgame":
            /*
                ストライクと竜騎士の反撃は同じデッキに入れない。ナイフは竜騎士の反撃と同じデッキに入れる。疑似治療は適当

                Nカード   

                疑似治療　
                    相手にハート5個分回復。たまに失敗し5個分のダメージ

                ナイフ　　　
                    相手にハート3個分のダメージ


                Rカード



                SRカード　 

                ストライク　
                    相手にハート10個分のダメージ。自分の手持ちに疑似治療のカードがある場合自分にハート10個分のダメージ

                竜騎士の反撃　
                    自分の手持ちにあればナイフのカードでダメージを受けても相手に50ダメージを与える


                URカード　 

                大天使の祈り 
                    使った際HPが70より多ければ40ダメージ。少なければ全回復

                堕天使の暗示 
                    HPを1にして相手を確殺する。しかし相手の所持カードにNカードが2枚ある場合自分が死んでしまう。
            */
                if($sender->isOp()){
                    $players = $this->getServer()->getOnlinePlayers();
                    foreach ($players as $player){
                        $math = mt_rand(1,2);
                        switch ($math) {
                            case "1":
                                $player->getInventory()->addItem(Item::get(339, 0, 5)->setCustomName("疑似治療"));
                                $player->getInventory()->addItem(Item::get(339, 0, 2)->setCustomName("ストライク"));
                                $player->getInventory()->addItem(Item::get(339, 0, 1)->setCustomName("大天使の祈り"));
                                $player->sendMessage("カード配布されました");
                            break;

                            case "2":
                                $player->getInventory()->addItem(Item::get(339, 0, 5)->setCustomName("ナイフ"));
                                $player->getInventory()->addItem(Item::get(339, 0, 2)->setCustomName("竜騎士の反撃"));
                                $player->getInventory()->addItem(Item::get(339, 0, 1)->setCustomName("堕天使の暗示"));
                                $player->sendMessage("カード配布されました");
                            break;
                        }
                    }
                }else{
                    $sender->sendMessage("§b権限者のみが使用できます");
                }
            return true;
        }
    }

    public function onTouch(PlayerInteractEvent $event){
        $id = $event->getItem()->getId();
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();
        $block = $event->getBlock();
        if($player->getInventory()->getItemInHand()->getCustomName() == "疑似治療"){
            $math = mt_rand(1,4);
            switch ($math) {
                case "1":
                    $health = $player->getHealth();
                    $player->setHealth($health + 10);
                break;

                case "2":
                    $health = $player->getHealth();
                    $player->setHealth($health + 10);
                break;

                case "3":
                    $health = $player->getHealth();
                    $player->setHealth($health + 10);
                break;

                case "4":
                    $health = $player->getHealth();
                    $player->setHealth($health - 10);
                break;
            }
            $player->getInventory()->removeItem($item);
        }

        if($player->getInventory()->getItemInHand()->getCustomName() == "大天使の祈り"){
            $health = $player->getHealth();
            if($health > 70){
                $player->setHealth($health - 40);
                $player->getInventory()->removeItem($item);
            }else{
                $player->setHealth($health + 100);
                $player->getInventory()->removeItem($item);
            }
        }

        if($player->getInventory()->getItemInHand()->getCustomName() == "ラブドール"){
            $player->getInventory()->addItem(Item::get(339, 0, 1)->setCustomName("癒し愛"));
            $player->getInventory()->removeItem($item);
        }

        if($player->getInventory()->getItemInHand()->getCustomName() == "癒し愛"){
            $health = $player->getHealth();
            $player->setHealth($health + 20);
            $player->getInventory()->removeItem($item);
        }
    }

    public function onEntityDamageByEntity(EntityDamageEvent $event){
        if($event instanceof EntityDamageByEntityEvent){
            $damager = $event->getDamager();
            $player = $event->getEntity();
            $item = $damager->getInventory()->getItemInHand()->getCustomName();
            if($item == "ストライク"){
                if($player->getInventory()->getItemInHand()->getCustomName() == "疑似治療"){
                    $health = $damager->getHealth();
                    $damager->setHealth($health - 20);
                    $damager->getInventory()->removeItem($item);
                }else{
                    $health = $player->getHealth();
                    $player->setHealth($health - 20);
                    $damager->getInventory()->removeItem($item);
                }
            }
            if($item == "ナイフ"){
                $health = $player->getHealth();
                $player->setHealth($health - 6);
                $damager->getInventory()->removeItem($item);
                if($player->getInventory()->getItemInHand()->getCustomName() == "竜騎士の反撃"){
                    $health = $damager->getHealth();
                    $damager->setHealth($health - 50);
                    $damager->getInventory()->removeItem($item);
                }
            }

            if($item == "堕天使の暗示"){
                if($player->getInventory()->getItemInHand()->getCustomName() == "疑似治療"){
                    $damager->setMaxHealth(2);
                    $damager->setHealth(2);
                    $damager->getInventory()->removeItem($item);
                    $player->kill();
                    if($player->getInventory()->getItemInHand()->getCustomName() == "ナイフ"){
                        $damager->kill();
                    }else{
                        $damager->setMaxHealth(2);
                        $damager->setHealth(2);
                        $damager->getInventory()->removeItem($item);
                        $player->kill(); 
                    }
                }else{
                    $damager->setMaxHealth(2);
                    $damager->setHealth(2);
                    $damager->getInventory()->removeItem($item);
                    $player->kill();
                }
            }
        }
    }
}