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
            height: 640
        });
    });
</script>

@endsection

@section('container')
<div class="container" style="padding-top: 1rem;">
    <?php if (is_nan($eob_md) || is_nan($eob_vd) || is_nan($eob_displacement) || is_nan($sod_md) || is_nan($sod_vd) || is_nan($sod_displacement) || is_nan($eod_md) || is_nan($eod_vd) || is_nan($eod_displacement)): ?>
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

                    <label for="dor">Drop Off Rate (DOR):</label><br />
                    <input type="number" step="any" id="dor" name="dor" class="form-control-custom" required value="{{ $request->get('dor') }}"/> deg/100ft

                    <label for="kop">Kick Off Point (KOP):</label><br />
                    <input type="number" step="any" id="kop" name="kop" class="form-control-custom" required value="{{ $request->get('kop') }}" /> ft<br />

                    <label for="target">End Off Drop (Target):</label><br />
                    <input type="number" step="any" id="target" name="target" class="form-control-custom" required value="{{ $request->get('target') }}" /> ft<br />

                    <label for="n">Northing:</label><br />
                    <input type="number" step="any" id="n" name="n" class="form-control-custom" required value="{{ $request->get('n') }}" /> ft<br />

                    <label for="e">Easting:</label><br />
                    <input type="number" step="any" id="e" name="e" class="form-control-custom" required value="{{ $request->get('e') }}"/> ft<br />

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary" id="calculate"> <i class="fa fa-calculator"></i> Calculate </button>
                    </div>
                </form>

                <?php if ($kop || $bur || $dor || $target || $n || $e): ?>
                    <hr>

                    <div class="text-center">
                        <form method="POST" action="{{ route('download.result.build.hold.drop') }}">
                            @csrf
                            <input type="hidden" name="depth" value="{{ json_encode($depth) }}">
                            <button type="submit" class="btn btn-success"> <i class="fa fa-download"></i> &nbsp; Download Result </button>
                        </form>
                    </div>
                <?php endif ?>
            </div>
        </div>

        <div class="col-md-8">
            <h3 class="text-center"> Build Hold Drop </h3>

            <br>
            
            <div class="row">
                <div class="graph-area">

                <?php if (!is_nan($eob_md) || !is_nan($eob_vd) || !is_nan($eob_displacement) || !is_nan($sod_md) || !is_nan($sod_vd) || !is_nan($sod_displacement) || !is_nan($eod_md) || !is_nan($eod_vd) || !is_nan($eod_displacement)): ?>
                    <div id="plotly-chart"></div>
                <?php endif; ?>
                </div>            
            </div>

            <hr>

            <div class="row">
                <div class="col-md-4">
                    <div class="end-of-build">
                        <h3 class="text-center">End of Build</h3>
                        <table class="table table-striped" id="eob-table">
                            <tr>
                                <th>Measure Depth</th>
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
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="start-of-drop">
                        <h3 class="text-center">Start of Drop</h3>
                        <table class="table table-striped" id="start-of-drop">
                            <tr>
                                <th>Measure Depth</th>
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
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="end-of-drop">
                        <h3 class="text-center">End of Drop</h3>
                        <table class="table table-striped" id="end-of-drop">
                            <tr>
                                <th>Measure Depth</th>
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
                        <br />
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
                        @php $status = ['KOP', 'End of Build', 'Start of Drop', 'Target']; @endphp
                        <tr class="@if (in_array($row['status'], $status)) highlight @endif">
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
@endsection