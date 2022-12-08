@extends('layouts.main')

@section('title', 'Well Trajectory - Horizontal Well')

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
    <?php if (\Session::get('error')): ?>
        <div class="alert alert-danger" role="alert">
            {{ \Session::get('error') }}
        </div>
    <?php endif ?>

    <div class="row">
        <div class="col-md-4">
            <div class="sidebar">
            	<form method="GET" action="">
                    <label for="kop">Kick Off Point (KOP):</label><br />
                    <input type="number" step="any" id="kop" name="kop" class="form-control-custom" required value="{{ $request->get('kop') }}" /> ft<br />

                    <label for="target">TVD (Target):</label><br />
                    <input type="number" step="any" id="target" name="target" class="form-control-custom" required value="{{ $request->get('target') }}" /> ft<br />

                    <label for="e">East:</label><br />
                    <input type="number" step="any" id="e" name="e" class="form-control-custom" required value="{{ $request->get('e') }}"/> ft<br />

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary" id="calculate"> <i class="fa fa-calculator"></i> Calculate </button>
                    </div>
                </form>

                <?php if ($kop || $target || $e): ?>
                    <hr>

                    <div class="text-center">
                        <form method="POST" action="{{ route('download.result.horizontal.well') }}">
                            @csrf
                            <input type="hidden" name="depth" value="{{ json_encode($depth) }}">
                            <button type="submit" class="btn btn-success"> <i class="fa fa-download"></i> &nbsp; Download Result </button>
                        </form>
                    </div>
                <?php endif ?>
            </div>
        </div>

        <div class="col-md-8">
            <h3 class="text-center"> Horizontal Well </h3>

            <br>
            
            <div class="row">
                <div class="graph-area">
	                <div id="plotly-chart"></div>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-6">
                    <div class="calculation-step">
                        <h3 class="text-center">Calculation</h3>
                        <table class="table table-striped" id="calculation-step">
	                        <tr>
	                            <th>Horizontal Disp</th>
	                            <td>{{ $horizontal_displacement }}</td>
	                        </tr>
	                        <tr>
	                            <th>Rb</th>
	                            <td>{{ $rb }}</td>
	                        </tr>
	                        <tr>
	                            <th>L</th>
	                            <td>{{ $l }}</td>
	                        </tr>
	                        <tr>
	                            <th>Measure Depth (MD)</th>
	                            <td>{{ $md }}</td>
	                        </tr>
	                        <tr>
	                            <th>Build Up Rate (BUR)</th>
	                            <td>{{ $bur }}</td>
	                        </tr>
                        </table>
                        <br />
                    </div>
                </div>

                <div class="col-md-6">
                	<div class="output">
                		<h3 class="text-center">Output</h3>
                		<table class="table table-striped" id="output">
	                        <tr>
	                            <th>Measure Depth (MD)</th>
	                            <td>{{ $md }}</td>
	                        </tr>
	                        <tr>
	                            <th>Build Up Rate (BUR)</th>
	                            <td>{{ $bur }}</td>
	                        </tr>
	                        <tr>
	                            <th>End of Build</th>
	                            <td>{{ $eob }}</td>
	                        </tr>
	                        <tr>
	                            <th>North</th>
	                            <td>{{ $n }}</td>
	                        </tr>
	                        <tr>
	                            <th>East</th>
	                            <td>{{ $e }}</td>
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
                        <th class="text-center">Measure Depth </th>
                        <th class="text-center">Inclination</th>
                        <th class="text-center">TVD </th>
                        <th class="text-center">Horizontal Departure</th>
                        <th class="text-center">Description</th>
                    </tr>
                    @foreach($depth as $row)
                        @php $status = ['KOP', 'End of Build', 'Target']; @endphp
                        <tr class="@if (in_array($row['status'], $status)) highlight @endif">
                            <td class="text-center">{{ round($row['md'], 3) }}</td>
                            <td class="text-center">{{ $row['inclination'] }}</td>
                            <td class="text-center">{{ round($row['tvd'], 3) }}</td>
                            <td class="text-center">{{ round($row['horizontal_departure'], 6) }}</td>
                            <td class="text-center">{{ $row['status'] }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        	
        </div>
    </div>


	<!-- <br>
	<div class="text-center">
		<img src="{{ asset('underconstruction.png') }}">
		<br>
		<h3 class="center">Mohon maaf, halaman ini masih dalam tahap pengembangan.</h3>
	</div> -->
</div>
@endsection