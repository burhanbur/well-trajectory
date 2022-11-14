@extends('layouts.main')

@section('title', 'Well Trajectory - Build Hold Drop')

@section('css')
<style>
    .text-center {
        text-align: center;
    }

    .form-control-custom {
        width: 70%;
        height: calc(1.5em + 0.75rem + 2px);
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
    }
</style>
@endsection

@section('js')
<script>
    var xValues = <?= json_encode($mdChartValue) ?>; // md
    var yValues = <?= json_encode($tvdChartValue) ?>; // tvd

    var myChart = new Chart("myChart", {
      type: "line",
      data: {
        labels: xValues,
        datasets: [{
          fill: false,
          // lineTension: 0,
          backgroundColor: "rgba(0,0,255,1.0)",
          borderColor: "rgba(0,0,255,1.0)",
          borderWidth: 8,
          data: yValues
        }]
      },
      options: {
        legend: {
            display: false,
        },
        scales: {
            yAxes: [
                {
                    ticks: {
                        reverse: true
                    },
                    stacked: true
                }
            ],
            xAxes: [
                {
                    stacked: true
                }
            ],
        },
        elements: {
            point: {
                radius: 0
            }
        }
      }
    });
</script>

@endsection

@section('container')
<div class="container">
	<br>

	<h3 class="text-center"> Build Hold Drop </h3><br>
    <div class="row">
        <div class="col-md-4">
            <div class="inputArea">
                <form method="GET" action="">
                    <label for="kop">Kick of Point (V1):</label><br />
                    <input type="number" step="any" id="kop" name="kop" class="form-control-custom" onkeypress="nextfield('target')" required value="{{ $request->get('kop') }}" /> ft<br />
                    <label for="target">End of Drop (Target):</label><br />
                    <input type="number" step="any" id="target" name="target" class="form-control-custom" onkeypress="nextfield('n')" required value="{{ $request->get('target') }}" /> ft<br />
                    <label for="n">Northing:</label><br />
                    <input type="number" step="any" id="n" name="n" class="form-control-custom" onkeypress="nextfield('e')"  required value="{{ $request->get('n') }}" /> ft<br />
                    <label for="e">Easting:</label><br />
                    <input type="number" step="any" id="e" name="e" class="form-control-custom" onkeypress="nextfield('bur')" required value="{{ $request->get('e') }}"/> ft<br />
                    <label for="bur">Build Up Rate (BUR):</label><br />
                    <input type="number" step="any" id="bur" name="bur" class="form-control-custom" onkeypress="nextfield('dor')" required value="{{ $request->get('bur') }}"/> deg/100ft
                    <label for="dor">Drop Off Rate (DOR):</label><br />
                    <input type="number" step="any" id="dor" name="dor" class="form-control-custom" onkeypress="nextfield('calculate')" required value="{{ $request->get('dor') }}"/> deg/100ft
                    <br><br>
                    <button type="submit" class="btn btn-primary" id="calculate"> <i class="fa fa-calculator"></i> Calculate </button>
                </form>
            </div>
        </div>
        <div class="col-md-8">
            <div class="grafikArea">
                <canvas id="myChart" style="width:100%; max-width:700px;"></canvas>
            </div>
        </div>
    </div>

    <div class="output-area">
        <div class="row">
            <div class="col-md-4">
                <div class="endOfBuild">
                    <br />
                    <h3 class="text-center">End of Build</h3>
                    <table class="table table-striped" id="eob-table">
                    <tr>
                        <th>MD</th>
                        <td>{{ round($eob_md, 4) }}</td>
                    </tr>
                    <tr>
                        <th>VD</th>
                        <td>{{ round($eob_vd, 4) }}</td>
                    </tr>
                    <tr>
                        <th>Displacement</th>
                        <td>{{ round($eob_displacement, 4) }}</td>
                    </tr>
                    </table>
                    <br />

                    <h3 class="text-center">Start of Drop</h3>
                    <table class="table table-striped" id="target-table">
                    <tr>
                        <th>MD</th>
                        <td>{{ round($sod_md, 4) }}</td>
                    </tr>
                    <tr>
                        <th>VD</th>
                        <td>{{ round($sod_vd, 4) }}</td>
                    </tr>
                    <tr>
                        <th>Displacement</th>
                        <td>{{ round($sod_displacement, 4) }}</td>
                    </tr>
                    </table>
                    <br />

                    <h3 class="text-center">End of Drop</h3>
                    <table class="table table-striped" id="target-table">
                    <tr>
                        <th>MD</th>
                        <td>{{ round($eod_md, 4) }}</td>
                    </tr>
                    <tr>
                        <th>VD</th>
                        <td>{{ round($eod_vd, 4) }}</td>
                    </tr>
                    <tr>
                        <th>Displacement</th>
                        <td>{{ round($eod_displacement, 4) }}</td>
                    </tr>
                    </table>
                </div>
            </div>

            <div class="col-md-8">
                <div class="tabelKedalaman">
                    <br />
                    <h3 class="text-center">Depth Table</h3>
                    <table class="table table-striped" id="depth-table">
                    <tr>
                        <th class="text-center">MD (ft) </th>
                        <th class="text-center">Inclination (deg)</th>
                        <th class="text-center">TVD (ft) </th>
                        <th class="text-center">Total Departure (ft)</th>
                        <th class="text-center">Status</th>
                    </tr>
                    @foreach($depth as $row)
                        <tr>
                            <td class="text-center">{{ round($row['md'], 3) }}</td>
                            <td class="text-center">@if ($row['status'] == 'Target') {{ round($row['inclination'], 6) }} @else {{ $row['inclination'] }} @endif</td>
                            <td class="text-center">{{ round($row['tvd'], 3) }}</td>
                            <td class="text-center">{{ round($row['total_departure'], 6) }}</td>
                            <td class="text-center">{{ $row['status'] }}</td>
                        </tr>
                    @endforeach
                    </table>
                </div>
            </div>
            
        </div>
    </div>

	<!-- <div class="text-center">
		<img src="{{ asset('underconstruction.png') }}">
		<br>
		<h3 class="center">Mohon maaf, halaman ini masih dalam tahap pengembangan.</h3>
	</div> -->
</div>
@endsection