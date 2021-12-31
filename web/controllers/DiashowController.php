<?php

    class DiashowController {
        public function delete()
        {
            $data = Request::validate(["id"]);
            DB::query("DELETE FROM `ourdogimages` WHERE `id` = :id", [":id" => $data["id"]]);
            Path::redirect("/index.php/admin/ourDog");
            exit;
        }

        public function create()
        {
            if (Request::method() == "GET") {
                echo Response::view("sites/admin/diashow/new");
            } else {
                $path = Path::ROOT() . "/" . Path::IMG_ROOT . "/";
                $target_file = $path . $_FILES["img"]["name"];

                move_uploaded_file($_FILES["img"]["tmp_name"], $target_file);

                DB::query("INSERT INTO `ourdogimages` (`name`, `num`) VALUES (:name, :num);", [":num" => count(DB::query("SELECT * FROM `ourdogimages`"))+1, ":name" => $_FILES['img']['name']]);

                Path::redirect("/index.php/admin/ourDog");
                exit;
            }
        }

        public function update()
        {
            $data = Request::validate(["name", "num"]);

            DB::query("UPDATE `ourdogimages` SET `num` = :num WHERE `name` = :name", [":name" => $data["name"], ":num" => $data["num"]]);

            echo json_encode(
                array(
                    "id" => $data["id"],
                    "name" => $data["name"],
                )
            );
            exit;
        }

        public function index()
        {
            $imgPaths = DB::query("SELECT * FROM `ourdogimages` ORDER BY `num` DESC;");

            for ($i=0; $i < count($imgPaths); $i++) {
                $contents = file_get_contents(Path::ROOT() . "/" . Path::IMG_ROOT . "/" . $imgPaths[$i]['name']);
                $base64 = base64_encode($contents);
                $imgPaths[$i]['img'] = "<img src='data:image/jpg;base64,$base64' alt='' class='rounded shadow img-cover' style='height: 60px;'>";
            }

            echo Response::view("sites/admin/diashow/index", [
                "imgPaths" => $imgPaths
            ]);
        }
    }


?>