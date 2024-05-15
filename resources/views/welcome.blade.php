<!DOCTYPE html>
<html lang="en">
<head>
  <title>Football</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.datatables.net/2.0.2/js/dataTables.js" type="text/javascript"></script>
  <script src="https://cdn.datatables.net/2.0.2/js/dataTables.bootstrap5.js" type="text/javascript"></script>
  <script src="https://cdn.datatables.net/select/2.0.0/js/dataTables.select.js" type="text/javascript"></script>
  <script src="https://cdn.datatables.net/select/2.0.0/js/select.bootstrap5.js" type="text/javascript"></script>
  <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script> -->

  <style>

  </style>
</head>
<body>

<script type="text/javascript">
  $(document).ready(function() {
    reloadParticipants(0,0);
    reloadListTeams(0,0);

    $('#pilihTeam').multiselect({
      includeSelectAllOption: false,
      maxHeight: 450
    });

    $('#save').on('click',function(e){
      e.preventDefault();
      Swal.fire({
        title: "Are you sure?",
        text: "Are you sure to Save Data?",
        icon: 'warning',
        inputAttributes: {
          autocapitalize: 'off'
        },
        showCancelButton: true,
        confirmButtonText: "Yes",
        cancelButtonText: "Cancel",
        allowOutsideClick: false
      }).then(function(x) {
        if(x.value === true){
          $.ajax({
            url         : "{{ url('/addTeamToDivision') }}",
            method      : "POST",
            data        : {
              "year"     : document.getElementById("pilihTahun").value,
              "division" : document.getElementById("pilihDivisi").value,
              "team"     : $("#pilihTeam").val(),
            },
            headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            success     : function (data) {
              if(data){
                swal.fire("Info!",data, "info");
              }else{
                swal.fire("Success!","Your data is successfully saved.","success");
                reloadParticipants(document.getElementById("pilihTahun").value,document.getElementById("pilihDivisi").value);
                reloadListTeams(document.getElementById("pilihTahun").value,document.getElementById("pilihDivisi").value);
                selectTeam(1);
              }
            }
          });
        }
      });
    });

  });

  function reloadStandings(){
    $.ajax({
      type    : 'POST',
      dataType: 'JSON',
      url   	: "{{ url('/standings') }}",
      data    : {
        "year"     : document.getElementById("selectTahun").value,
        "division" : document.getElementById("selectDivisi").value
      },
      headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
      success : function(result){

        $('#tblStandingsHTML').html('');
        $('#tblStandingsHTML').append(''+

        '<div class="table-responsive">'+
          '<table class="table" style="width:100%" id="tblStandings">'+
            '<thead>'+
              '<tr>'+
                '<th>#</th>'+
                '<th>Team</th>'+
                '<th>Game</th>'+        
                '<th>Win</th>'+
                '<th>Draw</th>'+      
                '<th>Lose</th>'+             
                '<th>GS</th>'+   
                '<th>GC</th>'+ 
                '<th>Goal</th>'+ 
                '<th>Poin</th>'+ 
              '</tr>'+
            '</thead>'+
            '<tbody>');

        var i = 0;
        $.each(result, function(x, y) {
          i++; 
          $('#tblStandings').append(''+
          '<tr>'+
            '<td style="background-color:'+y.COLOR+'; color:'+y.FONT_COLOR+';" width="2%">'+i+'</td>'+
            '<td width="34%">'+y.NAME+'</td>'+
            '<td width="8%">'+y.GAME+'</td>'+
            '<td width="8%">'+y.WIN+'</td>'+
            '<td width="8%">'+y.DRAW+'</td>'+
            '<td width="8%">'+y.LOSE+'</td>'+
            '<td width="8%">'+y.GOAL_SCORED+'</td>'+
            '<td width="8%">'+y.GOAL_CONCEDED+'</td>'+
            '<td width="8%">'+y.TOTAL_GOAL+'</td>'+
            '<td width="8%">'+y.POIN+'</td>'+
          '</tr>'+
          '');
         
        });

        $('#tblStandings').append(''+
        '</tbody></table></div>'+ 
        '');
      },
      error : function(xhr){

      }
    });
  }

  function calculate(){
    $.ajax({
      type    : 'POST',
      dataType: 'JSON',
      url   	: "{{ url('/calculate') }}",
      data    : { 
        "year"     : document.getElementById("fixturesTahun").value,
        "division" : document.getElementById("fixturesDivisi").value
      },
      headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
      success : function(result){
        if(data){
          swal.fire("Info!",data, "info");
        }else{
          swal.fire("Success!","Your data is successfully submitted.","success");
        }
        viewFixtures();
      }
    });
  }

  function viewFixtures(){
    $.ajax({
      type    : 'POST',
      dataType: 'JSON',
      url   	: "{{ url('/fixtures') }}",
      data    : {
        "year"     : document.getElementById("fixturesTahun").value,
        "division" : document.getElementById("fixturesDivisi").value,
        "day" : document.getElementById("fixturesMD").value
      },
      headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
      success : function(result){
        $('#tblFixturesHTML').html('');
        $('#tblFixturesHTML').append(''+

        '<div class="table-responsive">'+
          '<table class="table" style="width:100%" id="tblFixtures">'+
            '<thead>'+
              '<tr>'+
                '<th>ID Match</th>'+
                '<th class="text-center">Home</th>'+
                '<th class="text-center">Score</th>'+        
                '<th class="text-center">VS</th>'+
                '<th class="text-center">Score</th>'+      
                '<th class="text-center">Away</th>'+             
                '<th class="text-center">Action</th>'+
              '</tr>'+
            '</thead>'+
            '<tbody>');

        $day = 0;
        var i = 0;
        $.each(result, function(x, y) {
          i++; 
          if($day == y.MATCHDAY){
            if(y.STATUS == "200"){
              $('#tblFixtures').append(''+
              '<tr>'+
                '<td width="6%">'+y.ID+'</td>'+
                '<td width="30%" class="text-left">'+y.HOME+'</td>'+
                '<td width="1%" class="text-center">'+y.SCORE_TEAM_HOME+'</td>'+
                '<td width="2%" class="text-center">VS</td>'+
                '<td width="1%" class="text-center">'+y.SCORE_TEAM_AWAY+'</td>'+
                '<td width="30%" class="text-right">'+y.AWAY+'</td>'+
                '<td width="20%" class="text-center">COMPLETE</td>'+
              '</tr>'+
              '');
            }else{
              $scoredHome = 0;
              $scoredAway = 0;
              if(y.ID_DIVISION != 9){
                $scoredHome = Math.floor(Math.random() * 6);
                $scoredAway = Math.floor(Math.random() * 4);
              }

              $('#tblFixtures').append(''+
              '<tr>'+
                '<td width="6%">'+y.ID+'</td>'+
                '<td width="30%" class="text-left">'+y.HOME+'</td>'+
                '<td width="1%" class="text-center"><input type="number" id="'+y.ID+'_scoreHome" value="'+$scoredHome+'"></td>'+
                '<td width="2%" class="text-center">VS</td>'+
                '<td width="1%" class="text-center"><input type="number" id="'+y.ID+'_scoreAway" value="'+$scoredAway+'"></td>'+
                '<td width="30%" class="text-right">'+y.AWAY+'</td>'+
                '<td width="20%" class="text-center"><button type="button" class="btn btn-primary" id="calculate" onclick="submitScore('+y.ID+')">SUBMIT</button></td>'+
              '</tr>'+
              '');
            }
          }else{
            if(y.STATUS == "200"){
              $('#tblFixtures').append(''+
              '<tr>'+
                '<td colspan="7">MATCH DAY '+y.MATCHDAY+'</td>'+
              '</tr>'+
              '<tr>'+
                '<td width="6%">'+y.ID+'</td>'+
                '<td width="30%" class="text-left">'+y.HOME+'</td>'+
                '<td width="1%" class="text-center">'+y.SCORE_TEAM_HOME+'</td>'+
                '<td width="2%" class="text-center">VS</td>'+
                '<td width="1%" class="text-center">'+y.SCORE_TEAM_AWAY+'</td>'+
                '<td width="30%" class="text-right">'+y.AWAY+'</td>'+
                '<td width="20%" class="text-center">COMPLETE</td>'+
              '</tr>'+
              '');
            }else{
              $scoredHome = 0;
              $scoredAway = 0;
              if(y.ID_DIVISION != 9){
                $scoredHome = Math.floor(Math.random() * 6);
                $scoredAway = Math.floor(Math.random() * 4);
              }
              //test
              $('#tblFixtures').append(''+
              '<tr>'+
                '<td colspan="7">MATCH DAY '+y.MATCHDAY+'</td>'+
              '</tr>'+
              '<tr>'+
                '<td width="6%">'+y.ID+'</td>'+
                '<td width="30%" class="text-left">'+y.HOME+'</td>'+
                '<td width="3%" class="text-center"><input type="number" id="'+y.ID+'_scoreHome" value="'+$scoredHome+'"></td>'+
                '<td width="3%" class="text-center">VS</td>'+
                '<td width="3%" class="text-center"><input type="number" id="'+y.ID+'_scoreAway" value="'+$scoredAway+'"></td>'+
                '<td width="30%" class="text-right">'+y.AWAY+'</td>'+
                '<td width="10%" class="text-center"><button type="button" class="btn btn-primary" id="calculate" onclick="submitScore('+y.ID+')">SUBMIT</button></td>'+
              '</tr>'+
              '');
            }
          }
          $day = y.MATCHDAY;
         
        });

        $('#tblFixtures').append(''+
        '</tbody></table></div>'+ 
        '');
      },
      error : function(xhr){

      }
    });
  }

  function submitScore($id){
    ScoreHome = document.getElementById($id+"_scoreHome").value;
    ScoreAway = document.getElementById($id+"_scoreAway").value;

    $.ajax({
      url         : "{{ url('/scoreSubmit') }}",
      method      : "POST",
      data        : {
        "year"      : document.getElementById("fixturesTahun").value,
        "division"  : document.getElementById("fixturesDivisi").value,
        "id"        : $id,
        "ScoreHome" : ScoreHome,
        "ScoreAway" : ScoreAway,
      },
      headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
      success     : function (data) {
        if(data){
          swal.fire("Info!",data, "info");
        }else{
          swal.fire("Success!","Your data is successfully submitted.","success");
        }

        reloadStandings();
        viewFixtures();
      }
    });
  }

  function selectTeam($var){
    $.ajax({
      url         : "{{ url('/getTeam') }}",
      method      : "POST",
      data        : {
        "year"      : document.getElementById("pilihTahun").value,
        "division"  : document.getElementById("pilihDivisi").value
      },
      headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
      success     : function (data) {
        console.log(data);
        $('#pilihTeam').multiselect('destroy'); 
        $('#pilihTeam').empty();  
        $.each(data, function(key, value) {
          $('#pilihTeam')
          .append($("<option></option>")
                      .attr("value", value)
                      .text(key)); 
        });

        $('#pilihTeam').multiselect({
          includeSelectAllOption: true,
          maxHeight: 450
        });
        
        $('#pilihTeam').multiselect('rebuild'); 
        reloadParticipants(document.getElementById("pilihTahun").value, document.getElementById("pilihDivisi").value);
        reloadListTeams(document.getElementById("pilihTahun").value, document.getElementById("pilihDivisi").value);
      }
    });
  }

  function reloadParticipants($year, $divisi){
    $.ajax({
      type    : 'POST',
      dataType: 'JSON',
      url   	: "{{ url('/participants') }}",
      data    : {
        "year"     : $year,
        "division" : $divisi
      },
      headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
      success : function(result){

        $('#tblParticipantsHTML').html('');
        $('#tblParticipantsHTML').append(''+

        '<div class="table-responsive">'+
          '<table class="table" style="width:100%" id="tblParticipants">'+
            '<thead>'+
              '<tr>'+
                '<th width="5%">#</th>'+
                '<th width="90%">Team</th>'+
                '<th width="5%">Serie</th>'+
              '</tr>'+
            '</thead>'+
            '<tbody>');

        var i = 0;
        $.each(result, function(x, y) {
          i++; 
          $('#tblParticipants').append(''+
          '<tr>'+
            '<td >'+i+'</td>'+
            '<td >'+y.NAME+'</td>'+
            '<td >'+y.SERIE+'</td>'+
          '</tr>'+
          '');
         
        });

        $('#tblParticipants').append(''+
        '</tbody></table></div>'+ 
        '');
      },
      error : function(xhr){

      }
    });
  }

  function reloadListTeams($year, $divisi){
    $.ajax({
      type    : 'POST',
      dataType: 'JSON',
      url   	: "{{ url('/list_teams') }}",
      data    : {
        "year"     : $year,
        "division" : $divisi
      },
      headers : { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
      success : function(result){

        $('#tblAvailableTeams').html('');
        $('#tblAvailableTeams').append(''+

        '<div class="table-responsive">'+
          '<table class="table" style="width:100%" id="tblListTeams">'+
            '<thead>'+
              '<tr>'+
                '<th width="5%">#</th>'+
                '<th width="90%">Team</th>'+
                '<th width="5%">Serie</th>'+
              '</tr>'+
            '</thead>'+
            '<tbody>');

        var i = 0;
        $.each(result, function(x, y) {
          i++; 
          $('#tblListTeams').append(''+
          '<tr>'+
            '<td >'+i+'</td>'+
            '<td >'+y.NAME+'</td>'+
            '<td >'+y.SERIE+'</td>'+
          '</tr>'+
          '');
         
        });

        $('#tblListTeams').append(''+
        '</tbody></table></div>'+ 
        '');
      },
      error : function(xhr){

      }
    });
  }
</script>

<div class="container">
  <ul class="nav nav-pills">
    <li class="active"><a data-toggle="pill" href="#home">Standings</a></li>
    <li><a data-toggle="pill" href="#menu1">Fixtures</a></li>
    <li><a data-toggle="pill" href="#menu2">Settings</a></li>
    <li><a data-toggle="pill" href="#menu3">Menu 3</a></li>
  </ul>
  
  <div class="tab-content">
    <div id="home" class="tab-pane fade in active">
      <h3>STANDINGS</h3>
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="mb-3">
              <label for="selectTahun" class="col-sm-2 col-form-label">Year</label>
              <label for="selectTahun" class="col-sm-1 col-form-label">:</label>
              <select id="selectTahun" class="form-select form-select-sm col-sm-3">
                <option selected>-- Select one --</option>
                <option value="2034">2034</option>
                <option value="2035">2035</option>
                <option value="2036">2036</option>
                <option value="2037">2037</option>
                <option value="2038">2038</option>
                <option value="2039">2039</option>
                <option value="2040">2040</option>
              </select>
            </div>
          </div>
          <div class="row">
            <div class="mb-3">
              <label for="selectDivisi" class="col-sm-2 col-form-label">Select</label>
              <label for="selectDivisi" class="col-sm-1 col-form-label">:</label>
              <select id="selectDivisi" class="form-select form-select-sm col-sm-3">
                <option selected>-- Select one --</option>
                @foreach ($dataDiv as $item)
                  <option value="{{ $item->ID }}">{{ $item->NAME }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-2">
              <button type="button" class="btn btn-primary" id="select" onclick="reloadStandings()">SELECT</button>
            </div>
          </div>
          <br/>
          <hr></hr>
        </div>
      </div>
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col-md-12">
              <div id="tblStandingsHTML">
                <!-- javascript -->
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div id="menu1" class="tab-pane fade">
      <h3>FIXTURES</h3>
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="mb-3">
              <label for="fixturesTahun" class="col-sm-2 col-form-label">Year</label>
              <label for="fixturesTahun" class="col-sm-1 col-form-label">:</label>
              <select id="fixturesTahun" class="form-select form-select-sm col-sm-3">
                <option selected>-- Select one --</option>
                <option value="2034">2034</option>
                <option value="2035">2035</option>
                <option value="2036">2036</option>
                <option value="2037">2037</option>
                <option value="2038">2038</option>
                <option value="2039">2039</option>
                <option value="2040">2040</option>
              </select>
            </div>
          </div>
          <div class="row">
            <div class="mb-3">
              <label for="fixturesDivisi" class="col-sm-2 col-form-label">Select</label>
              <label for="fixturesDivisi" class="col-sm-1 col-form-label">:</label>
              <select id="fixturesDivisi" class="form-select form-select-sm col-sm-3">
                <option selected>-- Select one --</option>
                @foreach ($dataDiv as $item)
                  <option value="{{ $item->ID }}">{{ $item->NAME }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="row">
            <div class="mb-3">
              <label for="fixturesMD" class="col-sm-2 col-form-label">Match Day</label>
              <label for="fixturesMD" class="col-sm-1 col-form-label">:</label>
              <select id="fixturesMD" class="form-select form-select-sm col-sm-3">
                <option value="0">ALL</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6</option>
                <option value="7">7</option>
                <option value="8">8</option>
                <option value="9">9</option>
                <option value="10">10</option>
                <option value="11">11</option>
                <option value="12">12</option>
                <option value="13">13</option>
                <option value="14">14</option>
                <option value="15">15</option>
                <option value="16">16</option>
                <option value="17">17</option>
                <option value="18">18</option>
                <option value="19">19</option>
                <option value="20">20</option>
                <option value="21">21</option>
                <option value="22">22</option>
                <option value="23">23</option>
                <option value="24">24</option>
                <option value="25">25</option>
                <option value="26">26</option>
                <option value="27">27</option>
                <option value="28">28</option>
                <option value="29">29</option>
                <option value="30">30</option>
              </select>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-9">
              <button type="button" class="btn btn-primary" id="calculate" onclick="viewFixtures()">VIEW</button>
              <button type="button" class="btn btn-primary" id="calculate" onclick="calculate()">CALCULATE</button>
            </div>
          </div>
          <br/>
          <hr></hr>
          <div class="card">
            <div class="card-body">
              <div class="row">
                <div class="col-md-12">
                  <div id="tblFixturesHTML">
                    <!-- javascript -->
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div id="menu2" class="tab-pane fade">
      <div class="card">
        <div class="card-body">
          <div class="row">
            <div class="col-sm-2">
              <h4>Divisions</h4>
            </div>
          </div>
          <div class="row">
            <div class="mb-3">
              <label for="pilihTahun" class="col-sm-2 col-form-label">Year</label>
              <label for="pilihTahun" class="col-sm-1 col-form-label">:</label>
              <select id="pilihTahun" class="form-select form-select-sm col-sm-3" onchange="selectTeam(this.value)">
                <option value="0" selected>-- Select one --</option>
                <option value="2034">2034</option>
                <option value="2035">2035</option>
                <option value="2036">2036</option>
                <option value="2037">2037</option>
                <option value="2038">2038</option>
                <option value="2039">2039</option>
                <option value="2040">2040</option>
              </select>
            </div>
          </div>
          <div class="row">
            <div class="mb-3">
              <label for="pilihDivisi" class="col-sm-2 col-form-label">Select</label>
              <label for="pilihDivisi" class="col-sm-1 col-form-label">:</label>
              <select id="pilihDivisi" class="form-select form-select-sm col-sm-3" onchange="selectTeam(this.value)">
                <option value="0" selected>-- Select one --</option>
                @foreach ($dataDiv as $item)
                  <option value="{{ $item->ID }}">{{ $item->NAME }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="row">
            <div class="col-4">
              <label for="pilihTeam" class="col-sm-2 col-form-label">Teams</label>
              <label for="pilihTeam" class="col-sm-1 col-form-label">:</label>
              <select id="pilihTeam" name="namaTeam[]" class="form-select form-select-sm col-sm-3" multiple="multiple">
              </select>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-2">
              <button type="button" class="btn btn-primary" id="save">SAVE</button>
            </div>
          </div>
          <br/>
          <hr></hr>
          <div class="row">
            <div class="col-md-6">
              <h4>List Participant(s)</h4>
              <div id="tblParticipantsHTML">
                <!-- javascript -->
              </div>
            </div>
            <div class="col-md-6">
             <h4>List Team(s)</h4>
              <div id="tblAvailableTeams">
                <!-- javascript -->
              </div>
            </div>
          </div>
        </div>
      </div>  
    </div>
    <div id="menu3" class="tab-pane fade">
      <h3>Menu 3</h3>
      <p>Eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.</p>
    </div>
  </div>
</div>

</body>
</html>

