<?php

    class MainController {
        public function index()
        {
            $news = DB::query("SELECT * FROM `news` ORDER BY `date` DESC LIMIT 3;");
            for ($i=0; $i < count($news); $i++) { 
                $news_content = DB::query("SELECT * FROM `news_content` WHERE `newsID` = :newsID LIMIT 1;", [":newsID" => $news[$i]['id']]);
                $html = "";
                $html .= (strlen($news_content[0]['value']) > 120 ? substr($news_content[0]['value'], 0, 120) . "..." : $news_content[0]['value']);

                
                $news_content2 = DB::query("SELECT * FROM `news_content` WHERE `newsID` = :newsID;", [":newsID" => $news[$i]['id']]);
                
                $htmlFull = "";
                for ($j=0; $j < count($news_content2); $j++) { 
                    if ($news_content2[$j]['type'] == "text") {
                        $htmlFull .= ("<p>" . $news_content2[$j]["value"] . "</p>");
                    } else {
                        $contents = file_get_contents(Path::ROOT() . "/" . Path::IMG_ROOT . "/" . $news_content2[$j]['value']);
                        $base64 = base64_encode($contents);
                        $htmlFull .= "<img src='data:image/jpg;base64,$base64' alt='' class='rounded shadow img-cover' style='height: 60px;'>";
                    }
                }
                $news[$i]['text'] = $html;
                $news[$i]['fullText'] = $htmlFull;
                $news[$i]['date'] = substr($news[$i]['date'], 0, 10);
                $year = substr($news[$i]['date'], 0, 4);
                $month = substr($news[$i]['date'], 5, 2);
                $day = substr($news[$i]['date'], 8, 2);
                $news[$i]['date'] = $day . "." . $month . "." . $year;
            }

            echo Response::view("dashboard", [
                "news" => $news
            ]);
        }

        public function news()
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

            echo Response::view("sites/news/all", [
                "news" => $news
            ]);
        }

        public function contact()
        {
            echo Response::view("sites/contact");
        }

        public function ourDog()
        {
            $id = Request::get("id") ?? 1;
            $arr = ["null"];
            $imgNames = DB::query("SELECT `name` FROM `ourdogimages` ORDER BY `num`");
            for ($i=0; $i < count($imgNames); $i++) { 
                array_push($arr, $imgNames[$i]);
            }
            $currentImages = [
                $arr[2*intval($id)-1],
                $arr[2*intval($id)] ?? null
            ];
            $lastPaginationID = ceil(count($imgNames) / 2);
            
            $convertImgNameToBase64 = function ($name) {
                $contents = file_get_contents(Path::ROOT() . "/" . Path::IMG_ROOT . "/" . $name);
                $base64 = base64_encode($contents);
                return "data:image/jpg;base64,$base64";
            };
            
            echo Response::view("sites/ourDog", [
                'paginationFirstImg' => $convertImgNameToBase64($currentImages[0]['name']),
                'paginationSecondImg' => $convertImgNameToBase64($currentImages[1]['name'] ?? $imgNames[0]['name']),
                'paginationIDBack' => ($id == 1 ? $lastPaginationID : $id-1),
                'paginationIDForward' => ($id == $lastPaginationID ? 1 : $id+1),
                'paginationID' => $id,
                'maxPaginationID' => $lastPaginationID
            ]);
        }

        public function information()
        {
            echo Response::view("sites/zuchtInformation");
        }

        public function links()
        {
            echo Response::view("sites/links");
        }

        public function impressum()
        {
            echo Response::view("sites/impressum");
        }

        public function privacy()
        {
            echo Response::view("sites/privacy");
        }

        public function breeds()
        {
            echo Response::view("sites/zucht");
        }
    }


?>