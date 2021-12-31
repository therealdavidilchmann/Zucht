<?php

    class NewsController {
        public function delete()
        {
            $data = Request::validate(["id"]);
            DB::query("DELETE FROM `news` WHERE `id` = :id", [":id" => $data["id"]]);
            Path::redirect("/index.php/admin/news");
            exit;
        }

        public function edit()
        {
            $generateRandomString = function($length = 5) {
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $charactersLength = strlen($characters);
                $randomString = '';
                for ($i = 0; $i < $length; $i++) {
                    $randomString .= $characters[rand(0, $charactersLength - 1)];
                }
                return $randomString;
            };
            
            if (Request::method() == "GET") {
                $data = Request::validate(["id"]);

                $news = DB::query("SELECT * FROM `news` WHERE `id` = :id", [":id" => $data["id"]]);

                if (count($news) == 0) {
                    echo Response::error(500, "Post doesn't exist.");
                }

                $news = $news[0];
                $news_content = DB::query("SELECT * FROM `news_content` WHERE `newsID` = :newsID;", [":newsID" => $news['id']]);

                $form_html = '<form method="post" action="/index.php/admin/news/edit" enctype="multipart/form-data"><input type="hidden" name="id" value="' . $news['id'] . '"><div class="form-group"><input type="text" class="form-control" id="title" name="title" placeholder="Titel" value="' . $news['title'] . '"></div><div id="all-content">';
                $format = "s";
                
                for ($i=0; $i < count($news_content); $i++) { 
                    $tfID = $generateRandomString();
                    if ($news_content[$i]['type'] == "text") {
                        $form_html .= '
                            <div class="form-group">
                                <button type="button" class="btn btn-danger mb-1" onclick="removeTextfield(this, `text-' . $tfID . '`)">Text löschen</button>
                                <textarea type="text" class="form-control" name="text-' . $tfID . '" id="text-' . $tfID . '" placeholder="Text">' . $news_content[$i]['value'] . '</textarea>
                                <input type="hidden" name="text-' . $tfID . '-id" value=' . $news_content[$i]['id'] . '>
                            </div>
                        ';
                        $format .= "->text-" . $tfID;
                    }
                }
                
                $form_html .= '</div><input type="hidden" name="format" value="' . $format . '" id="content-format"><div class="row justify-content-center"><button type="button" class="btn btn-success ml-2 mr-2" onclick="addTextfield(\'text\')">Text hinzufügen</button></div><button type="submit" class="btn btn-primary">Fertigstellen</button></form>';

                echo Response::view("sites/admin/news/edit", [
                    "form" => $form_html
                ]);

            } else {
                $data = Request::validate(["id", "format", "title"]);

                $linkToTextfield = explode('->', $data['format']);
                array_shift($linkToTextfield);

                DB::query("DELETE FROM `news_content` WHERE `newsID` = :newsID", [":newsID" => $data['id']]);

                for ($i=0; $i < count($linkToTextfield); $i++) { 
                    if (strpos($linkToTextfield[$i], "text") !== false) {
                        $text = Request::get($linkToTextfield[$i]) ?? "";
                        DB::query("INSERT INTO `news_content` (`newsID`, `type`, `value`) VALUES (:newsID, :type, :value);", [":newsID" => $data['id'], ":type" => "text", ":value" => $text]);
                    }
                }
                
                Path::redirect("/index.php/admin/news");
                exit;
            }
        }

        public function create()
        {
            $generateRandomString = function($length = 10) {
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $charactersLength = strlen($characters);
                $randomString = '';
                for ($i = 0; $i < $length; $i++) {
                    $randomString .= $characters[rand(0, $charactersLength - 1)];
                }
                return $randomString;
            };

            if (Request::method() == "GET") {
                echo Response::view("sites/admin/news/new");
            } else {
                $data = Request::validate(["format", "title"]);

                $date = date("Y/m/d H:i:s");
                DB::query("INSERT INTO `news` (`title`, `date`) VALUES (:title, :date);", [":title" => $data["title"] ?? "", ":date" => $date]);
                $newsID = DB::query("SELECT `id` FROM `news` WHERE `title` = :title AND `date` = :date;", [":title" => $data["title"], ":date" => $date])[0]['id'];

                $linkToTextfield = explode('->', $data['format']);
                array_shift($linkToTextfield);

                for ($i=0; $i < count($linkToTextfield); $i++) { 
                    if (strpos($linkToTextfield[$i], "text") !== false) {
                        $text = Request::get($linkToTextfield[$i]) ?? "";
                        DB::query("INSERT INTO `news_content` (`newsID`, `type`, `value`) VALUES (:newsID, :type, :value);", [":newsID" => $newsID, ":type" => "text", ":value" => $text]);
                    } else {
                        if (isset($_FILES[$linkToTextfield[$i]])) {
                            $path = Path::ROOT() . "/" . Path::IMG_ROOT . "/";
                            $imgName = "z_" . $generateRandomString() . "." . substr($_FILES[$linkToTextfield[$i]]['type'], strpos($_FILES[$linkToTextfield[$i]]['type'], "/")+1);
                            $target_file = $path . $imgName;
            
                            move_uploaded_file($_FILES[$linkToTextfield[$i]]["tmp_name"], $target_file);

                            DB::query("INSERT INTO `news_content` (`newsID`, `type`, `value`) VALUES (:newsID, :type, :value);", [":newsID" => $newsID, ":type" => "img", ":value" => $imgName]);
                        }
                    }
                }

                Path::redirect("/index.php/admin/news");
                exit;
            }
        }

        public function index()
        {
            $news = DB::query("SELECT * FROM `news` ORDER BY `date` DESC;");
            for ($i=0; $i < count($news); $i++) { 
                $news_content = DB::query("SELECT * FROM `news_content` WHERE `newsID` = :newsID;", [":newsID" => $news[$i]['id']]);
                $html = "";
                for ($j=0; $j < count($news_content); $j++) { 
                    if ($news_content[$j]['type'] == "text") {
                        $html .= "<p>" . $news_content[$j]['value'] . "</p>";
                    } else {
                        $contents = file_get_contents(Path::ROOT() . "/" . Path::IMG_ROOT . "/" . $news_content[$j]['value']);
                        $base64 = base64_encode($contents);
                        $html .= "<img src='data:image/jpg;base64,$base64' alt='' class='rounded shadow img-cover' style='height: 60px;'>";
                    }
                }
                $news[$i]['text'] = $html;
                $news[$i]['date'] = substr($news[$i]['date'], 0, 10);
                $year = substr($news[$i]['date'], 0, 4);
                $month = substr($news[$i]['date'], 5, 2);
                $day = substr($news[$i]['date'], 8, 2);
                $news[$i]['date'] = $day . "." . $month . "." . $year;
            }

            echo Response::view("sites/admin/news/index", [
                "news" => $news
            ]);
        }
    }


?>