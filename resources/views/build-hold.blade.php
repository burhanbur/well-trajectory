@extends('layouts.main')

@section('title', 'Well Trajectory - Build Hold')

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
        margin-bottom: 0.75rem;
    }

    .sidebar {
        position: fixed;
        width:25%;
    }

    .highlight {
        background-color: rgba(189, 0, 0, 0.8);
        color: white;
    }
</style>
@endsection

@section('js')
{{-- <script>
    var xValues = <?= json_encode($mdChartValue) ?>; // md // total departure
    var yValues = <?= json_encode($tvdChartValue) ?>; // tvd

    var myChart = new Chart("myChart", {
      type: "line",
      data: {
        labels: xValues,
        datasets: [{
          fill: false,
          lineTension: 0,
          backgroundColor: "rgba(8, 73, 153, 1.0)",
          borderColor: "rgba(8, 73, 153, 1.0)",
          borderWidth: 8,
          data: yValues,
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
                    stacked: true,
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
</script> --}}

<script>
    var data = <?= json_encode($chart); ?>

    d3.csv('', function(err, rows) {
        function unpack(rows, key) {
            return rows.map(function(row)
            { 
                return row[key]; 
            }); 
        }

        rows = data;

        var x = unpack(rows , 'x');
        var y = unpack(rows , 'y');
        var z = unpack(rows , 'z');
        var c = unpack(rows , 'color');

        Plotly.newPlot('plotly-chart', [{
            type: 'scatter3d',
            mode: 'lines',
            x: x,
            y: y,
            z: z,
            opacity: 1,
            line: {
            width: 6,
            color: c,
            reversescale: false
            }
        }], 
        {
            height: 640,
            scene: {
                xaxis:{title: 'Northing'},
                yaxis:{title: 'Depth (feet)'},
                zaxis:{title: 'Easting'},
            },
        });
    });
</script>
@endsection

@section('container')
<div class="container" style="padding-top: 1rem;">
    <?php if (is_nan($target_md) || is_nan($target_displacement) || is_nan($eob_md) || is_nan($eob_vd) || is_nan($eob_displacement)): ?>
        <div class="alert alert-danger" role="alert">
            Invalid floating point opertaion.
        </div>
    <?php endif ?>

    <?php if (\Session::get('error')): ?>
        <div class="alert alert-danger" role="alert">
            {{ \Session::get('error') }}
        </div>
    <?php endif ?>

    <div class="row">
        <div class="col-md-4">
            <div class="sidebar">
                <form method="GET" action="">
                    <label for="bur">Build Up Rate (BUR):</label><br />
                    <input type="number" step="any" id="bur" name="bur" class="form-control-custom" required value="{{ $request->get('bur') }}"/> deg/100ft

                    <br>

                    <label for="kop">Kick Off Point (KOP):</label><br />
                    <input type="number" step="any" id="kop" name="kop" class="form-control-custom" required value="{{ $request->get('kop') }}" /> ft

                    <br>

                    <label for="target">Target (TVD):</label><br />
                    <input type="number" step="any" id="target" name="target" class="form-control-custom" required value="{{ $request->get('target') }}" /> ft

                    <br>

                    <label for="n">Northing:</label><br />
                    <input type="number" step="any" id="n" name="n" class="form-control-custom" required value="{{ $request->get('n') }}" /> ft

                    <br>

                    <label for="e">Easting:</label><br />
                    <input type="number" step="any" id="e" name="e" class="form-control-custom" required value="{{ $request->get('e') }}"/> ft

                    <br>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary" id="calculate"> <i class="fa fa-calculator"></i> &nbsp; Calculate </button>
                    </div>
                </form>

                <?php if ($bur && $target && $n && $e && $kop): ?>
                    <hr>

                    <div class="text-center">
                        <form method="POST" action="{{ route('download.result.build.hold') }}">
                            @csrf
                            <input type="hidden" name="depth" value="{{ json_encode($depth) }}">
                            <button type="submit" class="btn btn-success"> <i class="fa fa-download"></i> &nbsp; Download Result </button>
                        </form>
                    </div>
                <?php endif ?>
            </div>
        </div>

        <div class="col-md-8">
            <h3 class="text-center"> Build Hold </h3>

            <br>

            <div class="row">
                <div class="graph-area">

                <?php if (!is_nan($target_md) || !is_nan($target_displacement) || !is_nan($eob_md) || !is_nan($eob_vd) || !is_nan($eob_displacement)): ?>
                    <!-- <canvas id="myChart" style="width:100%;"></canvas> -->
                    <div id="plotly-chart"></div>
                <?php endif; ?>
                </div>            
            </div>

            <hr>

            <div class="row">
                <div class="col-md-6">
                    <div class="end-of-build">
                        <h3 class="text-center">End of Build</h3>
                        <table class="table table-striped" id="eob-table">
                            <tr>
                                <th>Measure Depth</th>
                                <td>{{ round($eob_md, 3) }}</td>
                            </tr>
                            <tr>
                                <th>VD</th>
                                <td>{{ round($eob_vd, 3) }}</td>
                            </tr>
                            <tr>
                                <th>Displacement</th>
                                <td>{{ round($eob_displacement, 3) }}</td>
                            </tr>
                        </table>
                        <br />                            
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="target">
                        <h3 class="text-center">Target</h3>
                        <table class="table table-striped" id="target-table">
                            <tr>
                                <th>Measure Depth</th>
                                <td>{{ round($target_md, 3) }}</td>
                            </tr>
                            <tr>
                                <th>Displacement</th>
                                <td>{{ round($target_displacement, 3) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <hr>

            <div class="depth-table">
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
                        @php $status = ['KOP', 'End of Build', 'Target']; @endphp
                        <tr class="@if (in_array($row['status'], $status)) highlight @endif">
                            <td class="text-center">{{ round($row['md'], 2) }}</td>
                            <td class="text-center">{{ round($row['inclination'], 2) }}</td>
                            <td class="text-center">{{ round($row['tvd'], 2) }}</td>
                            <td class="text-center">{{ round($row['total_departure'], 2) }}</td>
                            <td class="text-center">{{ $row['status'] }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
</div>
@endsection