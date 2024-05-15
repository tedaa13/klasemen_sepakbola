<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class generalController extends Controller
{
  public function index(){
    $q = "SELECT * FROM TBL_DIVISIONS";
    $dataDiv = DB::select($q);


    $q = "SELECT * FROM TBL_TEAMS as t WHERE t.ID NOT IN (SELECT ID_TEAM FROM TBL_STANDINGS WHERE YEAR = '0') ORDER BY t.NAME ASC";
    $dataTeam = DB::select($q);

    return view("welcome", compact('dataDiv','dataTeam'));
  }

  public function getTeam(Request $r){
    if($r->division == '5'){
      $q = "SELECT * FROM TBL_TEAMS as t WHERE t.ID NOT IN (SELECT ID_TEAM FROM TBL_COPPA WHERE YEAR = '".$r->year."') AND t.ID IN (SELECT ID_TEAM FROM TBL_STANDINGS WHERE YEAR = '".$r->year."' AND ID_DIVISION IN ('1','2')) ORDER BY t.NAME ASC";
    }elseif($r->division == '6'){
      $q = "SELECT * FROM TBL_TEAMS as t WHERE t.ID NOT IN (SELECT ID_TEAM FROM TBL_CAMPO WHERE YEAR = '".$r->year."') ORDER BY t.NAME ASC";
    }else{
      $q = "SELECT * FROM TBL_TEAMS as t WHERE t.ID NOT IN (SELECT ID_TEAM FROM TBL_STANDINGS WHERE YEAR = '".$r->year."') ORDER BY t.NAME ASC";
    }
    
    $dataTeam = DB::select($q);
    $data = [];
    foreach($dataTeam as $item){
      $data[$item->NAME] = $item->ID;
    }
    return $data;
  }

  public function addTeamToDivision(Request $r){
    // dd($r->all());
    $q = "SELECT COUNT(*) as JMLH_TEAM FROM TBL_STANDINGS WHERE YEAR = '".$r->year."' AND ID_DIVISION = '".$r->division."'";
    $dataLeague = DB::select($q);

    if($r->division == '5' || $r->division == '6' || $r->division == '7'){
      
      for($x=0;$x<count($r->team);$x++){

        $flag = 0;
        do {
          $rand_id_match = rand(1,32);
          $query  = "SELECT * FROM TBL_COPPA WHERE YEAR = '".$r->year."' AND ID_MATCH = '".$rand_id_match."'";
          $data   = DB::select($query);
          if($data){
            $flag = 0;
          }else{
            $flag = 1;
          }
        } while ($flag < 1);
        
        DB::table('TBL_COPPA')->insert(
          [
            'YEAR'          => $r->year,
            'ID_MATCH'      => $rand_id_match,
            'ID_TEAM'       => $r->team[$x]
          ]
        );
      }

      return "";
    }else{
      if($dataLeague[0]->JMLH_TEAM < 17){
        for($x=0;$x<count($r->team);$x++){
          DB::table('TBL_STANDINGS')->insert(
            [
              'YEAR'          => $r->year,
              'ID_DIVISION'   => $r->division,
              'ID_TEAM'       => $r->team[$x],
              'GAME'          => 0,
              'WIN'           => 0,
              'DRAW'          => 0,
              'LOSE'          => 0,
              'GOAL_SCORED'   => 0,
              'GOAL_CONCEDED' => 0,
              'TOTAL_GOAL'    => 0,
              'POIN'          => 0
            ]
          );
        }
      }else{
        return "Jumlah Team pada Divisi yang dipilih sudah melebihi batas Team. (16)";
      }
    }
  }

  public function standings(Request $r){
    $q = "SELECT x.*, p.COLOR, p.FONT_COLOR 
          FROM(
            SELECT hmm.[YEAR]
                , hmm.ID_DIVISION
                , d.[NAME] as LEAGUE
                , ROW_NUMBER() OVER (Order by (SUM(WIN) * 3) + (SUM(DRAW) * 1) + (SUM(LOSE) * 0) DESC
                                              , SUM(GOAL) - SUM(CONCEDED) DESC
                                              , SUM(GOAL) DESC
                                              , t.[NAME] ASC) AS POSITION
                , t.ID as ID_TEAM
                , t.[NAME]
                , SUM(GAME) as GAME
                , SUM(WIN) as WIN
                , SUM(DRAW) as DRAW
                , SUM(LOSE) as LOSE
                , SUM(GOAL) as GOAL_SCORED
                , SUM(CONCEDED) as GOAL_CONCEDED
                , SUM(GOAL) - SUM(CONCEDED) as TOTAL_GOAL
                , (SUM(WIN) * 3) + (SUM(DRAW) * 1) + (SUM(LOSE) * 0) as POIN
            FROM(
              SELECT [YEAR]
                  , ID_DIVISION
                  , ID_TEAM_HOME as ID_TEAM
                  , SUM(CASE WHEN STATUS = '200' THEN 1 ELSE 0 END) as GAME
                  , SUM(CASE WHEN TEAM_WIN IS NOT NULL AND TEAM_WIN = ID_TEAM_HOME THEN 1 ELSE 0 END) as WIN
                  , SUM(CASE WHEN TEAM_WIN IS NULL AND TEAM_LOSE IS NULL AND STATUS = '200' THEN 1 ELSE 0 END) as DRAW
                  , SUM(CASE WHEN TEAM_LOSE IS NOT NULL AND TEAM_LOSE = ID_TEAM_HOME THEN 1 ELSE 0 END) as LOSE
                  , SUM(SCORE_TEAM_HOME) as GOAL
                  , SUM(SCORE_TEAM_AWAY) as CONCEDED
              FROM TBL_FIXTURES 
              WHERE YEAR = '".$r->year."' AND ID_DIVISION = '".$r->division."'
              GROUP BY [YEAR], ID_DIVISION, ID_TEAM_HOME

              UNION ALL

              SELECT [YEAR]
                  , ID_DIVISION
                  , ID_TEAM_AWAY as ID_TEAM
                  , SUM(CASE WHEN STATUS = '200' THEN 1 ELSE 0 END) as GAME
                  , SUM(CASE WHEN TEAM_WIN IS NOT NULL AND TEAM_WIN = ID_TEAM_AWAY THEN 1 ELSE 0 END) as WIN
                  , SUM(CASE WHEN TEAM_WIN IS NULL AND TEAM_LOSE IS NULL AND STATUS = '200' THEN 1 ELSE 0 END) as DRAW
                  , SUM(CASE WHEN TEAM_LOSE IS NOT NULL AND TEAM_LOSE = ID_TEAM_AWAY THEN 1 ELSE 0 END) as LOSE
                  , SUM(SCORE_TEAM_AWAY) as GOAL
                  , SUM(SCORE_TEAM_HOME) as CONCEDED
              FROM TBL_FIXTURES 
              WHERE YEAR = '".$r->year."' AND ID_DIVISION = '".$r->division."'
              GROUP BY [YEAR], ID_DIVISION, ID_TEAM_AWAY
            )hmm
            INNER JOIN TBL_TEAMS as t ON t.ID = hmm.ID_TEAM
            INNER JOIN TBL_DIVISIONS as d ON d.ID = hmm.ID_DIVISION
            GROUP BY hmm.[YEAR], hmm.ID_DIVISION, d.[NAME], t.ID, t.[NAME]
          )x
          LEFT JOIN TBL_PATHS as p ON p.ID = x.POSITION AND p.ID_DIIVISION = x.ID_DIVISION
          ORDER BY x.POIN DESC
              , x.TOTAL_GOAL DESC
              , x.GOAL_SCORED DESC
              , x.[NAME] ASC";
    $data = DB::select($q);

    return $data;
  }

  public function calculateSchedule(Request $r){

    if($r->division == '5' || $r->division == '6' || $r->division == '7' ){
      return "For COPPA, COPPA, SUPER ITALY can't calculate.";
    }else{
      $q = "SELECT ID_TEAM
            FROM TBL_STANDINGS
            WHERE YEAR = '".$r->year."' AND ID_DIVISION = '".$r->division."'
            ORDER BY NEWID ();";
      $data = DB::select($q);

      $part_1 = count($data) / 2; //BAGAN PERTANDINGAN PART SATU
      $last = count($data) - 1;
      for($p1=0;$p1<$part_1;$p1++){
        $baganKiri[$p1] = $data[$p1]->ID_TEAM;
        $baganKanan[$p1] = $data[$last]->ID_TEAM;

        $last = $last - 1;
      }

      $day = 1;
      $this->kalkulasiBagan($part_1, $baganKiri, $baganKanan, $day, $r->year, $r->division);

      // for($p1=0;$p1<$part_1;$p1++){
      //   $jumlah = 0;
      //   for($x=0;$x<$part_1;$x++){
      //     $index = $p1+$x;
      //     if(!array_key_exists($index,$baganKanan)){
      //       $index  = 0 + $jumlah;
      //       $jumlah = $jumlah + 1;
      //     }
      //     if($day % 2 == 0){
      //       DB::table('TBL_TST')->insert(
      //         [
      //           'DAY'      => $day,
      //           'HOME'     => $baganKiri[$x],
      //           'VERSUS'   => 'VS',
      //           'AWAY'     => $baganKanan[$index],
      //         ]
      //       );
      //     }else{
      //       DB::table('TBL_TST')->insert(
      //         [
      //           'DAY'      => $day,
      //           'HOME'     => $baganKanan[$index],
      //           'VERSUS'   => 'VS',
      //           'AWAY'     => $baganKiri[$x],
      //         ]
      //       );
      //     }
      //   }
      //   $day = $day + 1;
      // }

      $this->bagianKedua($baganKiri,$baganKanan,9, $r->year, $r->division);
      return "";
    }
  }

  function bagianKedua($arrKiri, $arrKanan, $day, $year, $division){
    $day2 = $day;
    $part_2 = count($arrKiri) / 2; //BAGAN PERTANDINGAN PART DUA
    $last = count($arrKiri) - 1;
    for($p2=0;$p2<$part_2;$p2++){
      $baganKiri_1[$p2] = $arrKiri[$p2];
      $baganKiri_2[$p2] = $arrKiri[$last];

      $baganKanan_1[$p2] = $arrKanan[$p2];
      $baganKanan_2[$p2] = $arrKanan[$last];

      $last = $last - 1;
    }

    $this->kalkulasiBagan($part_2, $baganKiri_1, $baganKiri_2, $day, $year, $division);
    $this->kalkulasiBagan($part_2, $baganKanan_1, $baganKanan_2, $day, $year, $division);

    // for($p2=0;$p2<$part_2;$p2++){
    //   $jumlah = 0;
    //   for($x=0;$x<$part_2;$x++){
    //     $index = $p2+$x;
    //     if(!array_key_exists($index,$baganKiri_2)){
    //       $index  = 0 + $jumlah;
    //       $jumlah = $jumlah + 1;
    //     }
    //     if($day % 2 == 0){
    //       DB::table('TBL_TST')->insert(
    //         [
    //           'DAY'      => $day,
    //           'HOME'     => $baganKiri_1[$x],
    //           'VERSUS'   => 'VS',
    //           'AWAY'     => $baganKiri_2[$index],
    //         ]
    //       );
    //     }else{
    //       DB::table('TBL_TST')->insert(
    //         [
    //           'DAY'      => $day,
    //           'HOME'     => $baganKiri_2[$index],
    //           'VERSUS'   => 'VS',
    //           'AWAY'     => $baganKiri_1[$x],
    //         ]
    //       );
    //     }
    //   }
    //   $day = $day + 1;
    // }

    // $day = $day2;
    // for($p2=0;$p2<$part_2;$p2++){
    //   $jumlah = 0;
    //   for($x=0;$x<$part_2;$x++){
    //     $index = $p2+$x;
    //     if(!array_key_exists($index,$baganKanan_2)){
    //       $index  = 0 + $jumlah;
    //       $jumlah = $jumlah + 1;
    //     }
    //     if($day % 2 == 0){
    //       DB::table('TBL_TST')->insert(
    //         [
    //           'DAY'      => $day,
    //           'HOME'     => $baganKanan_1[$x],
    //           'VERSUS'   => 'VS',
    //           'AWAY'     => $baganKanan_2[$index],
    //         ]
    //       );
    //     }else{
    //       DB::table('TBL_TST')->insert(
    //         [
    //           'DAY'      => $day,
    //           'HOME'     => $baganKanan_2[$index],
    //           'VERSUS'   => 'VS',
    //           'AWAY'     => $baganKanan_1[$x],
    //         ]
    //       );
    //     }
    //   }
    //   $day = $day + 1;
    // }

    $this->bagianKetiga($baganKiri_1,$baganKiri_2,$baganKanan_1,$baganKanan_2,13, $year, $division);

    return "";
  }

  function bagianKetiga($arrKiri_1,$arrKiri_2,$arrKanan_1,$arrKanan_2,$day, $year, $division){
    $part_3 = count($arrKiri_1) / 2; //BAGAN PERTANDINGAN PART TIGA
    $last = count($arrKiri_1) - 1;
    for($p2=0;$p2<$part_3;$p2++){
      $baganKiri_1_1[$p2] = $arrKiri_1[$p2];
      $baganKiri_1_2[$p2] = $arrKiri_1[$last];

      $baganKiri_2_1[$p2] = $arrKiri_2[$p2];
      $baganKiri_2_2[$p2] = $arrKiri_2[$last];

      $baganKanan_1_1[$p2] = $arrKanan_1[$p2];
      $baganKanan_1_2[$p2] = $arrKanan_1[$last];

      $baganKanan_2_1[$p2] = $arrKanan_2[$p2];
      $baganKanan_2_2[$p2] = $arrKanan_2[$last];

      $last = $last - 1;
    }

    $this->kalkulasiBagan($part_3, $baganKiri_1_1, $baganKiri_1_2, $day, $year, $division);
    $this->kalkulasiBagan($part_3, $baganKiri_2_1, $baganKiri_2_2, $day, $year, $division);
    $this->kalkulasiBagan($part_3, $baganKanan_1_1, $baganKanan_1_2, $day, $year, $division);
    $this->kalkulasiBagan($part_3, $baganKanan_2_1, $baganKanan_2_2, $day, $year, $division);

    $this->bagianKeempat($baganKiri_1_1,$baganKiri_1_2,$baganKiri_2_1,$baganKiri_2_2,$baganKanan_1_1,$baganKanan_1_2,$baganKanan_2_1,$baganKanan_2_2, 15, $year, $division);

    return "";
  }

  function bagianKeempat($arrKiri_1_1,$arrKiri_1_2,$arrKiri_2_1,$arrKiri_2_2,$arrKanan_1_1,$arrKanan_1_2,$arrKanan_2_1,$arrKanan_2_2,$day,$year, $division){
    $part_4 = count($arrKiri_1_1) / 2; //BAGAN PERTANDINGAN PART EMPAT
    $last = count($arrKiri_1_1) - 1;
    for($p4=0;$p4<$part_4;$p4++){
      //KIRI
      $baganKiri_1_1_1[$p4] = $arrKiri_1_1[$p4];
      $baganKiri_1_1_2[$p4] = $arrKiri_1_1[$last];

      $baganKiri_1_2_1[$p4] = $arrKiri_1_2[$p4];
      $baganKiri_1_2_2[$p4] = $arrKiri_1_2[$last];

      $baganKiri_2_1_1[$p4] = $arrKiri_2_1[$p4];
      $baganKiri_2_1_2[$p4] = $arrKiri_2_1[$last];

      $baganKiri_2_2_1[$p4] = $arrKiri_2_2[$p4];
      $baganKiri_2_2_2[$p4] = $arrKiri_2_2[$last];

      //KANAN
      $baganKanan_1_1_1[$p4] = $arrKanan_1_1[$p4];
      $baganKanan_1_1_2[$p4] = $arrKanan_1_1[$last];

      $baganKanan_1_2_1[$p4] = $arrKanan_1_2[$p4];
      $baganKanan_1_2_2[$p4] = $arrKanan_1_2[$last];

      $baganKanan_2_1_1[$p4] = $arrKanan_2_1[$p4];
      $baganKanan_2_1_2[$p4] = $arrKanan_2_1[$last];

      $baganKanan_2_2_1[$p4] = $arrKanan_2_2[$p4];
      $baganKanan_2_2_2[$p4] = $arrKanan_2_2[$last];

      $last = $last - 1;
    }

    $this->kalkulasiBagan($part_4, $baganKiri_1_1_1, $baganKiri_1_1_2, $day, $year, $division);
    $this->kalkulasiBagan($part_4, $baganKiri_1_2_1, $baganKiri_1_2_2, $day, $year, $division);
    $this->kalkulasiBagan($part_4, $baganKiri_2_1_1, $baganKiri_2_1_2, $day, $year, $division);
    $this->kalkulasiBagan($part_4, $baganKiri_2_2_1, $baganKiri_2_2_2, $day, $year, $division);

    $this->kalkulasiBagan($part_4, $baganKanan_1_1_1, $baganKanan_1_1_2, $day, $year, $division);
    $this->kalkulasiBagan($part_4, $baganKanan_1_2_1, $baganKanan_1_2_2, $day, $year, $division);
    $this->kalkulasiBagan($part_4, $baganKanan_2_1_1, $baganKanan_2_1_2, $day, $year, $division);
    $this->kalkulasiBagan($part_4, $baganKanan_2_2_1, $baganKanan_2_2_2, $day, $year, $division);

    $this->copyMatch($year, $division);

    return "";
  }

  function kalkulasiBagan($loop, $arr1, $arr2, $day, $year, $division){
    $q = "SELECT TOP 1 ID
          FROM TBL_FIXTURES
          WHERE YEAR = '".$year."' AND ID_DIVISION = '".$division."'
          ORDER BY ID DESC;";
    $data = DB::select($q);

    $id_match = 1;
    if($data){
      $id_match = $data[0]->ID + 1;
    }

    for($p3=0;$p3<$loop;$p3++){
      $jumlah = 0;
      for($x=0;$x<$loop;$x++){
        $index = $p3+$x;
        if(!array_key_exists($index,$arr2)){
          $index  = 0 + $jumlah;
          $jumlah = $jumlah + 1;
        }
        if($day % 2 == 0){
          DB::table('TBL_FIXTURES')->insert(
            [
              'YEAR'            => $year,
              'ID_DIVISION'     => $division,
              'MATCHDAY'        => $day,
              'ID'              => $id_match,
              'ID_TEAM_HOME'    => $arr1[$x],
              'SCORE_TEAM_HOME' => 0,
              'SCORE_TEAM_AWAY' => 0,
              'ID_TEAM_AWAY'    => $arr2[$index],
              'TEAM_WIN'        => NULL,
              'TEAM_LOSE'       => NULL
            ]
          );
        }else{
          DB::table('TBL_FIXTURES')->insert(
            [
              'YEAR'            => $year,
              'ID_DIVISION'     => $division,
              'MATCHDAY'        => $day,
              'ID'              => $id_match,
              'ID_TEAM_HOME'    => $arr2[$index],
              'SCORE_TEAM_HOME' => 0,
              'SCORE_TEAM_AWAY' => 0,
              'ID_TEAM_AWAY'    => $arr1[$x],
              'TEAM_WIN'        => NULL,
              'TEAM_LOSE'       => NULL
            ]
          );
        }

        $id_match = $id_match + 1;
      }
      $day = $day + 1;
    }

    return "";
  }

  function copyMatch($year, $division){
    $q = "SELECT * 
          FROM TBL_FIXTURES
          OUTER APPLY(
            SELECT MAX(ID) as LAST_ID 
            FROM TBL_FIXTURES
            WHERE YEAR = '".$year."' AND ID_DIVISION = '".$division."'
          )hmm
          WHERE YEAR = '".$year."' AND ID_DIVISION = '".$division."'
          ORDER BY MATCHDAY ASC";
    $data = DB::select($q);

    $idMatch = $data[0]->LAST_ID + 1;

    for($x=0;$x<count($data);$x++){
      DB::table('TBL_FIXTURES')->insert(
        [
          'YEAR'            => $year,
          'ID_DIVISION'     => $division,
          'MATCHDAY'        => $data[$x]->MATCHDAY + 15,
          'ID'              => $idMatch,
          'ID_TEAM_HOME'    => $data[$x]->ID_TEAM_AWAY,
          'SCORE_TEAM_HOME' => 0,
          'SCORE_TEAM_AWAY' => 0,
          'ID_TEAM_AWAY'    => $data[$x]->ID_TEAM_HOME,
          'TEAM_WIN'        => NULL,
          'TEAM_LOSE'       => NULL
        ]
      );
      $idMatch = $idMatch + 1;
    }

    return "";
  }

  public function fixtures(Request $r){
    $q = "SELECT c.*, t.[NAME] as HOME , 'VS' as VERSUS, t2.[NAME] as AWAY, SCORE_TEAM_HOME, SCORE_TEAM_AWAY
          FROM [DB_ITALY].[dbo].[TBL_FIXTURES] as c
          INNER JOIN TBL_TEAMS as t ON t.ID = c.ID_TEAM_HOME
          INNER JOIN TBL_TEAMS as t2 ON t2.ID = c.ID_TEAM_AWAY
          WHERE c.YEAR = '".$r->year."' AND c.ID_DIVISION = '".$r->division."' AND (c.MATCHDAY = '".$r->day."' OR '".$r->day."' = 0)
          ORDER BY c.[MATCHDAY] ASC;";

    $data = DB::select($q);

    return $data;
  }

  public function scoreSubmit(Request $r){

    if($r->ScoreHome > $r->ScoreAway){
      DB::update('UPDATE TBL_FIXTURES 
                  SET SCORE_TEAM_HOME = ? 
                      , SCORE_TEAM_AWAY = ?  
                      , TEAM_WIN = ID_TEAM_HOME
                      , TEAM_LOSE = ID_TEAM_AWAY
                      , STATUS = ?
                  WHERE ID = ? AND YEAR = ? AND ID_DIVISION = ? ', [$r->ScoreHome , $r->ScoreAway, "200", $r->id, $r->year, $r->division]);
    }elseif($r->ScoreHome < $r->ScoreAway){
      DB::update('UPDATE TBL_FIXTURES 
                  SET SCORE_TEAM_HOME = ? 
                      , SCORE_TEAM_AWAY = ?  
                      , TEAM_WIN = ID_TEAM_AWAY
                      , TEAM_LOSE = ID_TEAM_HOME
                      , STATUS = ?
                  WHERE ID = ? AND YEAR = ? AND ID_DIVISION = ? ', [$r->ScoreHome , $r->ScoreAway, "200", $r->id, $r->year, $r->division]);
    }else{
      DB::update('UPDATE TBL_FIXTURES 
                  SET SCORE_TEAM_HOME = ? 
                      , SCORE_TEAM_AWAY = ?
                      , STATUS = ?
                  WHERE ID = ? AND YEAR = ? AND ID_DIVISION = ? ', [$r->ScoreHome , $r->ScoreAway, "200", $r->id, $r->year, $r->division]);
    }

    return "";
  }

  public function participants(Request $r){
    if($r->division == '5'){
      $q = "SELECT t.[NAME], RIGHT(d.[NAME],1) as SERIE
            FROM TBL_TEAMS as t
            INNER JOIN TBL_COPPA as c ON c.ID_TEAM = t.ID
            INNER JOIN TBL_STANDINGS as s ON s.ID_TEAM = c.ID_TEAM
            INNER JOIN TBL_DIVISIONS as d ON d.ID = s.ID_DIVISION
            WHERE s.YEAR = '".$r->year."' AND (s.ID_DIVISION = '".$r->division."' OR '5' = '".$r->division."')
            ORDER BY t.[NAME] ASC";
    }elseif($r->division == '6'){
      $q = "SELECT t.[NAME], RIGHT(d.[NAME],1) as SERIE
            FROM TBL_TEAMS as t
            INNER JOIN TBL_CAMPO as c ON c.ID_TEAM = t.ID
            INNER JOIN TBL_STANDINGS as s ON s.ID_TEAM = c.ID_TEAM
            INNER JOIN TBL_DIVISIONS as d ON d.ID = s.ID_DIVISION
            WHERE s.YEAR = '".$r->year."' AND (s.ID_DIVISION = '".$r->division."' OR '6' = '".$r->division."')
            ORDER BY t.[NAME] ASC";
    }else{
      $q = "SELECT t.[NAME], RIGHT(d.[NAME],1) as SERIE
            FROM TBL_TEAMS as t
            INNER JOIN TBL_STANDINGS as s ON s.ID_TEAM = t.ID
            INNER JOIN TBL_DIVISIONS as d ON d.ID = s.ID_DIVISION
            WHERE s.YEAR = '".$r->year."' AND s.ID_DIVISION = '".$r->division."'
            ORDER BY t.[NAME] ASC";
    }
    $data = DB::select($q);

    return $data;
  }

  public function list_teams(Request $r){
    $q = "SELECT t.[NAME], '-' as SERIE
          FROM TBL_TEAMS as t
          WHERE  ('".$r->division."' = '1' AND t.ID NOT IN (SELECT ID_TEAM FROM TBL_STANDINGS WHERE YEAR = '".$r->year."')) OR
                 ('".$r->division."' = '2' AND t.ID NOT IN (SELECT ID_TEAM FROM TBL_STANDINGS WHERE YEAR = '".$r->year."')) OR
                 ('".$r->division."' = '3' AND t.ID NOT IN (SELECT ID_TEAM FROM TBL_STANDINGS WHERE YEAR = '".$r->year."')) OR
                 ('".$r->division."' = '4' AND t.ID NOT IN (SELECT ID_TEAM FROM TBL_STANDINGS WHERE YEAR = '".$r->year."')) OR
                 ('".$r->division."' = '5' AND t.ID IN (SELECT ID_TEAM FROM TBL_STANDINGS WHERE YEAR = '".$r->year."' AND ID_DIVISION IN('1','2')) AND t.ID NOT IN (SELECT ID_TEAM FROM TBL_COPPA WHERE YEAR = '".$r->year."')) OR
                 ('".$r->division."' = '6' AND t.ID IN (SELECT ID_TEAM FROM TBL_STANDINGS WHERE YEAR = '".$r->year."') AND t.ID NOT IN (SELECT ID_TEAM FROM TBL_CAMPO WHERE YEAR = '".$r->year."'))
          ORDER BY t.[NAME] ASC";
    $data = DB::select($q);

    return $data;
  }
}
