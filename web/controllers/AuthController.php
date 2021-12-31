<?php

    class AuthController {
        public function login()
        {
            if (Request::method() == "GET") {
                $data = Request::get('formError');
                if ($data == null) {
                    $data = "";
                }
                echo Response::view('sites/auth/login', ['formError' => $data]);
            } else {
                $data = Request::validate(["username", "password"]);
                $user = DB::query("SELECT `id`, `password` FROM `users` WHERE `username` = :username;", [":username" => $data["username"]]);
                if (count($user) > 0) {
                    if (password_verify($data["password"], $user[0]["password"])) {
                        $token = bin2hex(random_bytes(25));
                        DB::query("INSERT INTO `tokens` (`userID`, `token`) VALUES (:userID, :token);", [":userID" => $user[0]["id"], ":token" => $token]);

                        setcookie("token", $token, time() + 60 * 60 * 24 * 30, "/");
                        Path::redirect("/index.php/admin");
                        exit;
                    }
                    Path::redirect("/index.php/login?formError=password");
                    exit;
                }
                Path::redirect("/index.php/login?formError=username");
                exit;
            }
        }

        public function register()
        {
            if (Request::method() == "GET") {
                $data = Request::get('formError');
                if ($data == null) {
                    $data = "";
                }
                echo Response::view('sites/auth/register', ['formError' => $data]);
            } else {
                $data = Request::validate(["username", "password"]);
                DB::query("INSERT INTO `users` (`username`, `password`) VALUES (:username, :pw)", [':username' => $data['username'], ':pw' => password_hash($data['password'], PASSWORD_DEFAULT)]);
                $user = DB::query("SELECT `id` FROM `users` WHERE `username` = :username;", [":username" => $data["username"]]);
                
                $token = bin2hex(random_bytes(25));
                DB::query("INSERT INTO `tokens` (`userID`, `token`) VALUES (:userID, :token);", [":userID" => $user[0]["id"], ":token" => $token]);

                setcookie("token", $token, time() + 60 * 60 * 24 * 30, "/");
                Path::redirect("/index.php/admin");
                exit;
            }
        }
    }

?>