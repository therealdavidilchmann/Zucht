<?php

    class Auth {
        public static function isLoggedIn()
        {
            $token = $_COOKIE['token'];
            $user = $user = DB::query("SELECT COUNT(`users`.*) AS numUsers FROM `users` INNER JOIN `tokens` ON `users`.`id` = `tokens`.`userID` WHERE `tokens`.`token` = :token", [":token" => $token]);
            return $user[0]['numUsers'] > 0;
        }
    }

    class User {
        public $username = "";
        public $password = "";

        public function __construct($username, $password)
        {
            $this->username = $username;
            $this->password = $password;
        }
    }

?>