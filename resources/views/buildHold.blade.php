@extends('layouts.main')

@section('title', 'Well Trajectory - Build Hold')

@section('css')
<style>
    .text-center {
        text-align: center;
    }

    .form-control-custom {
        width: 60%;
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

@section('container')
<div class="container"><br>
    <h3> Build Hold </h3><br>
    <div class="row">
        <div class="col-sm-4">
            <div class="inputArea">
                <form method="GET" action="">
                    <label for="kop">Kick Of Point (V1):</label><br />
                    <input type="number" step="any" id="kop" name="kop" class="form-control-custom" onkeypress="nextfield('target')" value="{{ $request->get('kop') }}" /> ft<br />
                    <label for="target">Target (V3):</label><br />
                    <input type="number" step="any" id="target" name="target" class="form-control-custom" onkeypress="nextfield('n')" value="{{ $request->get('target') }}" /> ft<br />
                    <label for="n">Northing:</label><br />
                    <input type="number" step="any" id="n" name="n" class="form-control-custom" onkeypress="nextfield('e')" value="{{ $request->get('e') }}" /> ft<br />
                    <label for="e">Easting:</label><br />
                    <input type="number" step="any" id="e" name="e" class="form-control-custom" onkeypress="nextfield('bur')" value="{{ $request->get('e') }}"/> ft<br />
                    <label for="bur">Build Up Rate (BUR):</label><br />
                    <input type="number" step="any" id="bur" name="bur" class="form-control-custom" onkeypress="nextfield('calculate')" value="{{ $request->get('bur') }}"/> deg/100ft<br /><br />
                    <button type="submit" class="btn btn-primary" id="calculate"> <i class="fa fa-calculator"></i> Calculate </button>
                </form>
            </div>
        </div>
        <div class="col-sm-8">
            <div class="grafikArea">
                <canvas id="myChart" style="width:100%;max-width:700px"></canvas>
            </div>
        </div>
    </div>
    <div class="output-area">
        <div class="endOfBuild">
            <br />
            <h3 class="text-center">End Of Build (EOB)</h3>
            <table class="table table-striped" id="eob-table">
            <tr>
                <th>MD</th>
                <th>VD</th>
                <th>Displacement</th>
            </tr>
            <tr>
                <td>{{$eob_md}}</td>
                <td>{{$eob_vd}}</td>
                <td>{{$eob_displacement}}</td>
            </tr>
            </table>
            <br />

            <h3 class="text-center">Target</h3>
            <table class="table table-striped" id="target-table">
            <tr>
                <th>MD</th>
                <th>Displacement</th>
            </tr>
            <tr>
                <td>{{$target_md}}</td>
                <td>{{$target_displacement}}</td>
            </tr>
            </table>
        </div>

        <div class="tabelKedalaman">
            <br />
            <h3 class="text-center">Depth Table</h3>
            <table class="table table-striped" id="depth-table">
            <tr>
                <th>MD (ft) </th>
                <th>Inclination (deg)</th>
                <th>TVD (ft) </th>
                <th>Total Departure (ft)</th>
                <th>Status</th>
            </tr>
            <tr>
                <td>0</td>
                <td></td>
                <td>0</td>
                <td>0</td>
                <td>Vertical</td>
            </tr>
            </table>
        </div>
    </div>
</div>
@endsection