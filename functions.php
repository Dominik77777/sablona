<?php
function getCSS(){
    $jsonStr = file_get_contents("data/datas.json");
    $data = json_decode($jsonStr, true);
    $stranka = basename($_SERVER['REQUEST_URI']);
    $stranka = explode(".", $stranka)[0];

    if (isset($data['stranky'][$stranka]) && is_array($data['stranky'][$stranka])) {
        $suboryCSS = $data['stranky'][$stranka];
        foreach ($suboryCSS as $subor) {
            echo "<link rel='stylesheet' href='$subor'>";
        }
    } else {
        echo "No CSS files found for the page.";
    }
}