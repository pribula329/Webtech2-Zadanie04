<?php

function curlStranka()
{
// create a new cURL resource
    $ch = curl_init();

// set URL and other appropriate options
    curl_setopt($ch, CURLOPT_URL, "https://github.com/apps4webte/curldata2021");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// grab URL and pass it to the browser
    $stranka = curl_exec($ch);

    preg_match_all("!main/[^\s]*?.csv!", $stranka, $vysledky);

// close cURL resource, and free up system resources
    curl_close($ch);

    return $vysledky;
}



///kontrola suborov vsetkych a vytvaranie tabuliek

function prehladavanieCsv($conn, $vysledky)
{



    $cislo = 0;


    foreach ($vysledky[0] as $prednaska) {

        $ch = curl_init();

// set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, "https://raw.githubusercontent.com/apps4webte/curldata2021/" . $vysledky[0][$cislo]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// grab URL and pass it to the browser
        $stranka = curl_exec($ch);
        $str = mb_convert_encoding($stranka, "UTF-8", "UTF-16");

//na pole subor
        $riadky = explode("\n", $str);
        $pole = array();
        foreach ($riadky as $jedno) {

            $udaje = explode("\t", $jedno);

            array_push($pole, $udaje);


        }
        curl_close($ch);
        vytvorTabulkuDB($conn,"Predanaska".$cislo,$pole);

        $cislo = $cislo + 1;
    }
}


function vytvorTabulkuDB($conn, $nazov,$poleVstupov){

    $existuje = kontrolaTabulky($conn,$nazov);
    if ($existuje==0){
        $sql = 'CREATE TABLE IF NOT EXISTS '.$nazov.' (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                meno VARCHAR(60) ,
                priezvisko VARCHAR(60) ,
                akcia VARCHAR(60),
                datum TIMESTAMP
                )';
        $conn->exec($sql);
        $nezapisuj = 0;
        $numItems = count($poleVstupov);
        $i = 1;
        foreach ($poleVstupov as $pole){
            if ($nezapisuj!=0 && ($numItems != $i)){
                if (str_contains($pole[2], 'AM')){
                    $timestamp = date('Y-m-d H:i:s',date_create_from_format('m/d/Y, H:i:s A',$pole[2])->getTimestamp());

                }
                else{
                    $timestamp = date('Y-m-d H:i:s',date_create_from_format('d/m/Y, H:i:s',$pole[2])->getTimestamp());

                }
                $stm = $conn->prepare('INSERT INTO '.$nazov.' (meno, priezvisko, akcia, datum) 
                                    VALUES (?,?,?,?)');
                $stm->bindValue(1,$pole[0]);
                $stm->bindValue(2, $pole[0]);
                $stm->bindValue(3, $pole[1]);
                $stm->bindValue(4, $timestamp);


                $stm->execute();
            }
            $i = $i+1;
            $nezapisuj = 1;

        }

    }



}
//kontrola ci existuje uz taka tabulka
function kontrolaTabulky($conn,$nazov): int
{

    $sql = "SHOW TABLES";
    //Prepare our SQL statement,
    $statement = $conn->prepare($sql);


    $statement->execute();
    $tables= $statement->fetchAll(PDO::FETCH_NUM);
    foreach($tables as $table){
        if ($table[0]==$nazov)
        {

            return 1;
        }

    }

    return 0;



}

function pocetTabuliek($conn): int
{
    $sql = "SHOW TABLES";
    //Prepare our SQL statement,
    $statement = $conn->prepare($sql);


    $statement->execute();
    $tables= $statement->fetchAll(PDO::FETCH_NUM);

    return count($tables);
}

function vytvorenieHlavnejTabulky($conn)
{
    $pocet =pocetTabuliek($conn);
    $sql = 'CREATE TABLE IF NOT EXISTS osoby (
                id INT(6)  AUTO_INCREMENT PRIMARY KEY,
                meno VARCHAR(60) ,
                priezvisko VARCHAR(60) ,
                ucast INT(6) ,
                minuty INT(6)
    
                )';
    $conn->exec($sql);


    for ($i =1; $i<$pocet; $i++){
        //The name of the column that we want to create.
        $columnName = 'Prednaska'.$i;
        try{
            //Our SQL query that will alter the table and add the new column.
            $sql = 'ALTER TABLE  osoby ADD  '.$columnName.' varchar(60) ';

            //Execute the query.
            $conn->query($sql);
        }
        catch (PDOException $e){

        }

    }


}
//statistika pre graf
function statistika($conn){
    $pocet =pocetTabuliek($conn);
    $pole = array();
    for ($i =0; $i<$pocet-1; $i++){

        $db = "Predanaska".$i;
        $stm = $conn->prepare('select * from '.$db.'  ;');

        $stm->execute();
        $statistiky = $stm->fetchALl(PDO::FETCH_ASSOC);
        $osoby=array();

        foreach ($statistiky as $item) {
            $kontrola =0;
            foreach ($osoby as $osoba){
                if ($item['meno']== $osoba['meno']){
                    $kontrola = 1;
                }

            }
            if ($kontrola==0){
                array_push($osoby,$item);
            }


        }
        array_push($pole,count($osoby));

    }

    return $pole;

}

//ludia co boli aspon raz na prednaske
function ludia($conn){
    $pocet =pocetTabuliek($conn);
    $poleLudi = array();
    for ($i =0; $i<$pocet-1; $i++){

        $db = "Predanaska".$i;
        $stm = $conn->prepare('select * from '.$db.'  ;');

        $stm->execute();
        $statistiky = $stm->fetchALl(PDO::FETCH_ASSOC);


        foreach ($statistiky as $item) {
            $kontrola =0;
            foreach ($poleLudi as $osoba){
                if ($item['meno']== $osoba['meno']){
                    $kontrola = 1;
                }

            }
            if ($kontrola==0){
                array_push($poleLudi,$item);
            }


        }


    }

    return $poleLudi;

}
// kolko krat boli na prednaske
function ucastNaPrednaskach($conn,$poleLudi){



    $pocet =pocetTabuliek($conn);
    $poleLudiANavstevnosti = array();

    foreach ($poleLudi as $clovek){
        $navstevy =0;

        for ($i =0; $i<$pocet-1; $i++){

            $db = "Predanaska".$i;
            $stm = $conn->prepare('select * from '.$db.'  ;');

            $stm->execute();
            $statistiky = $stm->fetchALl(PDO::FETCH_ASSOC);


            foreach ($statistiky as $item) {

                if ($clovek['meno']==$item['meno'] && $item['akcia']=="Joined"){
                    $navstevy = $navstevy+1;
                    break;
                }

            }





        }

        array_push($poleLudiANavstevnosti,$navstevy);
    }

return $poleLudiANavstevnosti;

}


//pocet minut ktore bola osoba na prednaske
function minutyNaPrednske($conn,$poleLudi){



    $pocet =pocetTabuliek($conn);
    $minutyNaPrednaske = array();

    foreach ($poleLudi as $clovek){
        $osobaNaPrednaske=array();
        for ($i =0; $i<$pocet-1; $i++){

            $db = "Predanaska".$i;
            $meno='"'.$clovek['meno'].'"';
            $sql = 'select * from '.$db.' where meno='.$meno.' ;';
            $stm = $conn->prepare($sql);
            $stm->execute();
            $statistiky = $stm->fetchALl(PDO::FETCH_ASSOC);

            $cas =0;
            foreach ($statistiky as $item) {

                if ($item['akcia']=="Joined"){
                    $datumAcas = explode(' ', $item['datum']);
                    $cas = $cas + minutes($datumAcas[1]);
                }
                else{
                    $datumAcas = explode(' ', $item['datum']);
                    $cas = $cas - minutes($datumAcas[1]);
                }

            }
            $minuty = round(abs($cas),2);
            array_push($osobaNaPrednaske,$minuty);



        }

        array_push($minutyNaPrednaske,$osobaNaPrednaske);
    }

    return $minutyNaPrednaske;

}

function minutes($time){

    $time = explode(':', $time);
    return ($time[0]*60) + ($time[1]) + ($time[2]/60);
}


/// cas na prednaskach spolu
function casSpolu($pole){
    $dokopy=0;
    foreach ($pole as $item){
        $dokopy=$dokopy+$item;
    }

    return $dokopy;
}

?>