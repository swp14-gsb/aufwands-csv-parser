<?php
$valid = true;

$filename = 'xml/aufwand-' . time() . '.xml';

if (isset($_POST['gsb'])) {
    $gsb = true;
}

$names = Array(
    'Lukas' => 'LE',
    'Siggi' => 'SZ',
    'Christoph' => 'CS',
    'Elvira' => 'EA',
    'Felix' => 'FH',
    'Nico' => 'NG',
    'Nick' => 'NB',
);

asort($names);

function parseGermanDate($date, $row = 0)
{
    global $valid;
    if (preg_match('/^\d{2}\.\d{2}\.20\d{2}$/', $date) === 0) {
        echo 'Datum in ' . $row . ' nicht valide.<br>';
        $valid = false;
    }
    $date = explode('.', $date);
    return mktime(0, 0, 0, $date[1], $date[0], $date[2]);
}

function replaceNames($name)
{
    global $names;
    foreach ($names as $long => $short) {
        $name = str_replace($long, $short, $name);
    }
    return $name;
}

function parseName($name)
{
    global $names, $gsb;
    $name = str_replace('&', ',', preg_replace('/\s+/', '', $name));
    if ($gsb) {
        if (strpos($name, 'Alle') === 0) {
            $pos = strpos($name, '\\');
            if ($pos > 0) {
                $without = explode(',', replaceNames(substr($name, $pos + 1)));
                $name = implode(',', array_diff($names, $without));
            }
        } else {
            $name = replaceNames($name);
        }
    }
    return $name;
}

function parseTime($time)
{
    return str_replace(',', '.', $time);
}

function checkData($data, $row)
{
    $valid = true;
    if (preg_match('/^\w+(,\s*\w+)*$/', $data[0]) === 0) {
        echo 'Name in Zeile ' . $row . ' nicht valide<br>';
        $valid = false;
    }
    if ($data[3] > 5 || $data[3] < 1) {
        echo 'Angemessenheit (' . $data[2] . ') in Zeile ' . $row . ' nicht valide<br>';
        $valid = false;
    }
    if ($data[4] > 5 || $data[4] < 1) {
        echo 'Schwierigkeit (' . $data[2] . ') in Zeile ' . $row . ' nicht valide<br>';
        $valid = false;
    }
    if (!is_numeric($data[2])) {
        echo 'Zeit (' . $data[2] . ') in Zeile ' . $row . ' nicht valide<br>';
        $valid = false;
    }
    if (!$valid) {
        echo 'Zeile ' . $row . ':';
        var_dump($data);
        echo '<br>';
    }
    return $valid;
}

$von = parseGermanDate($_POST['von'], 'der Variable "von"');
$bis = parseGermanDate($_POST['bis'], 'der Variable "bis"');
$createdBy = $_POST['createdBy'];
$createdBy = ($gsb) ? 'LE' : $createdBy;
$createdAt = ($_POST['createdAt'] == '') ? time() : parseGermanDate($_POST['createdAt']);
$gruppe = $_POST['gruppe'];
$gruppe = ($gsb) ? 'swp14-gsb' : $gruppe;
$row = 0;

?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Parser für Aufwands XML (CSV -> XML)</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css">
    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700' rel='stylesheet' type='text/css'>
    <link href='css/datepicker.css' rel='stylesheet' type='text/css'>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->

    <style type="text/css">

        /* Global */

        html, body {
            height: 100%;
            font-family: 'Open Sans', sans-serif;
        }

        /* HEader */

        .navbar-header .navbar-brand {
            width: 100%;
            position: absolute
        }

        /* Content */

        #wrap {
            min-height: 100%;
            height: auto;
            margin: 0 auto -60px;
            padding: 0 0 60px;
        }

        #wrap > .container {
            padding-top: 80px;
        }

        .jumbotron {
            font-size: 18px;
        }

        /* Footer */
        #footer {
            height: 60px;
            background-color: #f5f5f5;
        }

        #footer .container p {
            margin: 20px 15px;
        }

    </style>

</head>
<body>
<div id="wrap">
    <header class="navbar navbar-inverse navbar-fixed-top bs-docs-nav" role="banner">
        <div class="container">
            <div class="navbar-header">
                <a href='#' class="navbar-brand">
                    Parser für Aufwands XML (CSV -> XML)
                </a>
            </div>
        </div>
    </header>
    <div class="container">
        <div class="jumbotron">

            <form class="form-inline" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="post"
                  enctype="multipart/form-data">

                <div class="form-group">
                    <label for="gsb">Bericht für swp14-gsb?&nbsp;</label>
                </div>
                <div class="form-group">
                    <input type="checkbox" id="gsb" name="gsb" <?php echo ($gsb)?'checked':''; ?>>
                </div>
                <div class="clearfix"></div>

                <div class="form-group">
                    <label for="gruppe">Bericht von&nbsp;</label>
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" id="gruppe" name="gruppe" placeholder="Gruppe" <?php echo ($gsb)?'disabled':''; ?> value="<?php echo ($gsb)?'':$group; ?>">
                </div>
                <div class="form-group">
                    <label for="createdAt">&nbsp;erstellt am&nbsp;</label>
                </div>
                <div class="form-group">
                    <div class="input-append date">
                        <input type="text" name="createdAt" id="createdAt" class="form-control"
                               value="<?php echo date('d.m.Y',$createdAt); ?>"><span class="add-on"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="gruppe">&nbsp;durch&nbsp;</label>
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" id="createdBy" name="createdBy"
                           placeholder="Verantwortlicher" <?php echo ($gsb)?'disabled':''; ?>  value="<?php echo ($gsb)?'':$createdBy; ?>">
                </div>
                <div class="clearfix"></div>
                <div class="form-group">
                    <label for="von">Umfasst den Zeitraum von&nbsp;</label>
                </div>
                <div class="form-group">
                    <div class="input-append date">
                        <input type="text" name="von" id="von" class="form-control" placeholder="Startdatum" value="<?php echo ($von>0)?date('d.m.Y',$von):''; ?>"><span
                            class="add-on"></span>
                    </div>
                </div>
                <div class="form-group">
                    <label for="gruppe">&nbsp;bis&nbsp;</label>
                </div>
                <div class="form-group">
                    <div class="input-append date">
                        <input type="text" name="bis" id="bis" class="form-control" placeholder="Enddatum" value="<?php echo ($bis>0)?date('d.m.Y',$bis):''; ?>"><span
                            class="add-on"></span>
                    </div>
                </div>
                <div class="clearfix"></div>

                <div class="form-group">
                    <label for="file">CSV-Datei</label>

                </div>
                <div class="form-group">
                    <input type="file" id="file" name="file">
                </div>
                <button type="submit" class="btn btn-default">Submit</button>
            </form>
<pre>
Status:
    <?php

    if (isset($_FILES["file"])) {

        //if there was an error uploading the file
        if ($_FILES["file"]["error"] > 0) {
            echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
        }
        if ($von > $bis) {
            echo 'Wie soll das Startdatum nach dem Enddatum liegen?';
        } else {
            $xml = new SimpleXMLElement("<Analyse></Analyse>");

            $xml->addAttribute('von', date('Y-m-d', $von));
            $xml->addAttribute('bis', date('Y-m-d', $bis));
            $xml->addAttribute('createdBy', $createdBy);
            $xml->addAttribute('createdAt', date('Y-m-d', $createdAt));
            $xml->addAttribute('gruppe', $gruppe);

            if (($handle = fopen($_FILES["file"]["tmp_name"], "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if ($row > 1) {
                        $date = parseGermanDate($data[1], $row);
                        if ($date >= $von && $date <= $bis) {
                            $data[0] = parseName($data[0]);
                            $data[2] = parseTime($data[2]);
                            $valid = $valid && checkData($data, $row);
                            $done = $xml->addChild('done', $data[5]);
                            $done->addAttribute('who', $data[0]);
                            $done->addAttribute('A', $data[3]);
                            $done->addAttribute('S', $data[4]);
                            $done->addAttribute('Zeit', $data[2]);
                        }
                    }
                    $row++;
                }
                fclose($handle);
            }

            if ($valid) {
                $xml->asXML( $filename);

                libxml_use_internal_errors(true);

                $dom = new DOMDocument();

                $dom->loadXML(file_get_contents( $filename));

                if (!$dom->schemaValidate('Aufwand.xsd')) {
                    $errors = libxml_get_errors();
                    var_dump($errors);
                    $valid=false;
                } else {
                    echo 'Alles in Ordnung!';
                }
            }
        }

    }

    ?>
</pre>
            <?php
            if ($valid) {
                echo '<a href="' . $filename . '" target="_blank" download="AufwandsXML" title="AufwandsXML">AufwandsXML downloaden</a>';
            }
            ?>
        </div>
        <!-- end .jumbotron -->
    </div>
    <!-- end .container -->
</div>
<!-- end #wrap -->


<div id="footer">
    <div class="container">
        <p class="text-muted">&copy; 2014: SWP14-GSB Team</p>
    </div>
</div>

<!-- Scripts belong to the bottom of the body. -->
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="//code.jquery.com/jquery-1.10.2.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.3/js/bootstrap.min.js"></script>
<script src="js/bootstrap-datepicker.js"></script>
<script src="js/locales/bootstrap-datepicker.de.js"></script>
<script>

    $('.input-append.date').datepicker({
        format: "dd.mm.yyyy",
        todayBtn: true,
        language: "de",
        autoclose: true,
        orientation: "bottom-right",
        todayHighlight: true
    });

    $('#gsb').click(function () {
        if ($('#gruppe, #createdBy').attr('disabled')) {
            $('#gruppe, #createdBy').removeAttr('disabled')
        } else {
            $('#gruppe, #createdBy').attr('disabled', 'disabled');
        }
    });

</script>
</body>
</html>
