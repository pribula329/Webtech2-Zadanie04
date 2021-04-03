<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Zadanie 4</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
    <script src="js/jquery-3.5.1.min.js"></script>
    <script src="js/jquery.dataTables.min.js"></script>
    <script src="js/script.js"></script>
    <link rel="stylesheet" href="css/dizajn.css">
</head>
<body class="container">

<?php
include ("subory.php");
include ("erorrs.php");
include ("loginDB.php");
// nacitanie csv
$vysledky = curlStranka();
$conn = pokusLogin();
// prehladanie csv
prehladavanieCsv($conn,$vysledky);
$data = statistika($conn);
$poleLudi = ludia($conn);
$navstevy = ucastNaPrednaskach($conn,$poleLudi);
$casNaPrednaske=minutyNaPrednske($conn,$poleLudi);
?>
<h1>Dochádzka</h1>
<div class="tabulka">
    <table class="table table-striped" id="table">
    <thead class="thead-dark">
        <tr>
            <th scope="col">Meno</th>
            <th scope="col">Priezvisko</td>
            <?php
            $cislo=1;
            foreach ($vysledky[0] as  $pocet){
                echo '<th scope="col"> P '.$cislo.'</th>';
                $cislo=$cislo+1;
            }
            ?>

            <th scope="col">PUnP</th>
            <th scope="col">PMnP</th>
        </tr>
    </thead>
    <tbody>
    <?php
    vytvorenieHlavnejTabulky($conn);
    $index = 0;
    // tabulka
    foreach ($poleLudi as $clovek){
        // mena a priezviska
        $meno =explode(" ", $clovek['meno']);
        echo '<tr>
                <td>'.$meno[0].'</td>';
        if (count($meno)==3){
            echo '<td>'.$meno[1].' '.$meno[2].'</td>';
        }
        elseif (count($meno)==2){
            echo '<td>'.$meno[1].'</td>';
        }
        //prednasky
        $kolo =0;
        foreach ($vysledky[0] as  $pocet){
            if ($casNaPrednaske[$index][$kolo]<200){
                echo '<td>'.$casNaPrednaske[$index][$kolo].'</td>';
            }
            else{
                echo '<td class="farebne">'.$casNaPrednaske[$index][$kolo].'</td>';
            }
            $kolo=$kolo+1;
        }
        //ucast na prednaskach
        echo '<td>'.$navstevy[$index].'</td>';
        // minuty na prednaskach
        echo '<td>'.casSpolu($casNaPrednaske[$index]).'</td>';

        echo '</tr>';
        $index=$index+1;
    }

    ?>
    </tbody>

</table>
</div>
<br>
<section>
    <h2>Grafické znázornenie dochádzky</h2>
    <div id="graf"></div>
</section>

<script type="text/javascript">
        var ludia = <?php echo json_encode($data); ?>;
graf(ludia);</script>

</body>
</html>
