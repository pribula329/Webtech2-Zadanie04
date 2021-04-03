<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Zadanie 4</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
    <script src="js/script.js"></script>
</head>
<body >

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

<table class="table table-striped">
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
        //prednaska
        foreach ($vysledky[0] as  $pocet){
            echo '<td>'.$casNaPrednaske[0].' </td>';
        }
        //ucast na prednaskach
        echo '<td>'.$navstevy[$index].'</td>';
        // minuty na prednaskach
        echo '<td></td>';

        echo '</tr>';
        $index=$index+1;
    }

    ?>
    </tbody>

</table>
<div id="graf"></div>
<script type="text/javascript">
        var ludia = <?php echo json_encode($data); ?>;
graf(ludia);</script>;


</body>
</html>
