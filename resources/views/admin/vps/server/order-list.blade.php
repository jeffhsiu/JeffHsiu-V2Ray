@extends('backpack::layout')

@section('header')
    <section class="content-header">
        <h1>
            <span class="text-capitalize">Servers</span>
            <small>server order list</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ url(config('backpack.base.route_prefix'), 'dashboard') }}">{{ trans('backpack::crud.admin') }}</a></li>
            <li><a href="{{ backpack_url('vps/server')}}" class="text-capitalize">Servers</a></li>
            <li class="active">List</li>
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
                @foreach($servers as $server)
                <div class="box box-primary" style="border-top-width: 3px;">
                    <div class="box-header with-border">
                        <h4 class="box-title">
                            {{ $server['provider_string'] }} - {{ $server['ip'] }}
                        </h4>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div>
                    <div class="box-body no-border">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>IP</th>
                                    <th>Docker name</th>
                                    <th>Customer</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($server['dockers'] as $key => $docker)
                                <tr>
                                @if($key == 1)
                                    <td rowspan="10" style="text-align:center;vertical-align:middle;width: 115px;">{{ $server['ip'] }}</td>
                                @endif
                                    <td>{{ $docker['name'] }}
                                    <td>{!! is_null($docker['order']) ? '-' : '<a href="'.backpack_url('order/order/'.$docker['order']->id).'">'.$docker['order']->customer->name.'</a>' !!}</td>
                                    <td>{{ is_null($docker['order']) ? '-' : $docker['order']->start_date_notime }}</td>
                                    <td>{{ is_null($docker['order']) ? '-' : $docker['order']->end_date_notime }}</td>
                                    <td>{{ is_null($docker['order']) ? '-' : $docker['order']->type_string }}</td>
                                    <td>
                                        @if(is_null($docker['order']))
                                        <a href="{{ backpack_url('order/order/create'.'?server_id='.$server['id'].'&docker_name='.$docker['name']) }}" class="btn btn-xs btn-default">
                                            <i class="fa fa-first-order"></i> Create Order
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div><!-- /.box-body -->
                </div>
                @endforeach
            </div><!-- /.box -->
            {{ $paginate->links() }}
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
            $("#docker_table").DataTable({
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

            $("#docker_stats_table").DataTable({
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
