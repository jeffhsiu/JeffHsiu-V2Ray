@extends('backpack::layout')

@section('header')
    <section class="content-header">
        <h1>
            <span class="text-capitalize">Servers</span>
            <small>{{ $ip }} server stats</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ url(config('backpack.base.route_prefix'), 'dashboard') }}">{{ trans('backpack::crud.admin') }}</a></li>
            <li><a href="{{ backpack_url('vps/server')}}" class="text-capitalize">Servers</a></li>
            <li class="active">Stats</li>
        </ol>
    </section>
@endsection

@section('content')
    <a href="{{ backpack_url('vps/server')}}" class="hidden-print"><i class="fa fa-angle-double-left"></i> {{ trans('backpack::crud.back_to_all') }} <span>Server</span></a>

    <a href="javascript: window.print();" class="pull-right hidden-print"><i class="fa fa-print"></i></a>
    <div class="row">
        <div class="col-md-10 col-md-offset-1">

            <!-- Default box -->
            <div class="m-t-20">
                <div class="box box-warning" style="border-top-width: 3px;">
                    <div class="box-header with-border">
                        <h4 class="box-title">
                            Server Stats
                        </h4>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div>
                    <div class="box-body no-border">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <td>
                                        <strong>IP</strong>
                                    </td>
                                    <td>
                                        {{ $ip }}
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Mem</strong></td>
                                    <td>
                                        -
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>CPU %</strong></td>
                                    <td>
                                        -
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>Rent Due Date</strong>
                                    </td>
                                    <td>
                                        {{ $end_date }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div><!-- /.box-body -->
                </div>
            </div><!-- /.box -->

            <!-- Default box -->
            <div class="m-t-20">
                <div class="box box-primary" style="border-top-width: 3px;">
                    <div class="box-header with-border">
                        <h4 class="box-title">
                            Docker Orders
                        </h4>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div>
                    <div class="box-body no-border">
                        <table id="docker_table" class="table table-striped table-hover responsive" style="width: 100%;">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Port</th>
                                <th>Status</th>
                                <th>Net I/O</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Customer</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($dockers as $docker)
                                <tr>
                                    <td style="min-width: 60px;">
                                        {{ $docker['name'] }}
                                    </td>
                                    <td>
                                        {{ $docker['port'] }}
                                    </td>
                                    <td>
                                        {{ explode(' ', $docker['status'])[0] }}
                                    </td>
                                    <td>
                                        {{ isset($docker['net']) ? $docker['net'] : '-' }}
                                    </td>
                                    <td>
                                        {{ isset($docker['start_date']) ? $docker['start_date'] : '-'  }}
                                    </td>
                                    <td>
                                        {{ isset($docker['end_date']) ? !empty($docker['end_date']) ? $docker['end_date'] : '~' : '-'  }}
                                    </td>
                                    <td>
                                        {!! isset($docker['customer']) ? $docker['customer'] : '-'  !!}
                                    </td>
                                    <td>
                                        @if(substr($docker['status'], 0, 2) == 'Up')
                                            <a href="{{ backpack_url('vps/server/docker/stop'.'?server_id='.$server_id.'&container_id='.$docker['container_id']) }}" class="btn btn-xs btn-default">
                                                <i class="fa fa-stop-circle"></i> Stop
                                            </a>
                                        @else
                                            <a href="{{ backpack_url('vps/server/docker/start'.'?server_id='.$server_id.'&container_id='.$docker['container_id']) }}" class="btn btn-xs btn-default">
                                                <i class="fa fa-play-circle"></i> Start
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div><!-- /.box-body -->
                </div>
            </div><!-- /.box -->

            <!-- Default box -->
            <div class="m-t-20">
                <div class="box box-success" style="border-top-width: 3px;">
                    <div class="box-header with-border">
                        <h4 class="box-title">
                            Docker Stats
                        </h4>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div>
                    <div class="box-body no-border">
                        <table id="docker_stats_table" class="table table-striped table-hover responsive" style="width: 100%;">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Port</th>
                                <th>Container ID</th>
                                <th>Created</th>
                                <th>Status</th>
                                <th>CPU %</th>
                                <th>Mem / Limit</th>
                                <th>Net I/O</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($dockers as $docker)
                                <tr>
                                    <td style="min-width: 60px;">
                                        {{ $docker['name'] }}
                                    </td>
                                    <td>
                                        {{ $docker['port'] }}
                                    </td>
                                    <td>
                                        {{ $docker['container_id'] }}
                                    </td>
                                    <td>
                                        {{ $docker['created'] }}
                                    </td>
                                    <td>
                                        {{ $docker['status'] }}
                                    </td>
                                    <td>
                                        {{ isset($docker['cpu']) ? $docker['cpu'] : '-' }}
                                    </td>
                                    <td>
                                        {{ isset($docker['mem']) ? $docker['mem'] : '-' }}
                                    </td>
                                    <td>
                                        {{ isset($docker['net']) ? $docker['net'] : '-' }}
                                    </td>

                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div><!-- /.box-body -->
                </div>
            </div><!-- /.box -->

        </div>
    </div>
@endsection

@section('after_styles')
    <!-- DATA TABLES -->
    <link href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.1/css/responsive.bootstrap.min.css">

    <link rel="stylesheet" href="{{ asset('vendor/backpack/crud/css/crud.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/backpack/crud/css/show.css') }}">
@endsection

@section('after_scripts')
    <!-- DATA TABLES SCRIPT -->
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.1/js/responsive.bootstrap.min.js"></script>

    <script src="{{ asset('vendor/backpack/crud/js/crud.js') }}"></script>
    <script src="{{ asset('vendor/backpack/crud/js/show.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('#docker_table').DataTable({
                paging: false,
                searching: false,
                info: false,
                columnDefs: [
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 2, targets: 3 },
                    { responsivePriority: 3, targets: -2 },
                    { responsivePriority: 4, targets: -1 },
                    { responsivePriority: 5, targets: 4 },
                ]
            });

            $('#docker_stats_table').DataTable({
                paging: false,
                searching: false,
                info: false,
                columnDefs: [
                    { responsivePriority: 1, targets: 0 },
                    { responsivePriority: 2, targets: -3 },
                    { responsivePriority: 3, targets: -2 },
                    { responsivePriority: 4, targets: -1 },
                ]
            });
        });
    </script>
@endsection
