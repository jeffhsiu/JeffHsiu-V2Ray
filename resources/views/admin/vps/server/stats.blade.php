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
                                        <strong>Provider</strong>
                                    </td>
                                    <td>
                                        {{ $provider }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>IP</strong>
                                    </td>
                                    <td>
                                        {{ $ip }}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>Host</strong>
                                    </td>
                                    <td>
                                        {{ $ws_host }}
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
                                <tr>
                                    <td>
                                        <strong>Remark</strong>
                                    </td>
                                    <td>
                                        {{ $remark }}
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
                                    <td class="{{ $docker['is_end'] ? 'text-red text-bold' : ''}}" style="min-width: 60px;">
                                        {{ $docker['name'] }}
                                    </td>
                                    <td>
                                        {{ explode(' ', $docker['status'])[0] }}
                                    </td>
                                    <td>
                                        {{ isset($docker['net']) ? $docker['net'] : '-' }}  {{ $docker['net_last'] ? '('.$docker['net_last'].')' : '' }}
                                    </td>
                                    <td>
                                        {{ $docker['order'] ? strstr($docker['order']->start_date, ' ', true) : '-'  }}
                                    </td>
                                    <td class="{{ $docker['is_end'] ? 'text-red text-bold' : ''}}">
                                        {{ $docker['order'] ? !empty($docker['order']->end_date) ? strstr($docker['order']->end_date, ' ', true) : '~' : '-'  }}
                                    </td>
                                    <td>
                                        {!! $docker['order'] ? '<a href="'.backpack_url('order/order/'.$docker['order']->id).'">'.$docker['order']->customer->name : '-'  !!}
                                    </td>
                                    <td>
                                        @if(auth()->user()->hasRole('Distributor')
                                        && ( !$docker['order'] OR $docker['order']->distributor_id != auth()->user()->distributor->id))
                                            -
                                        @else
                                            @if(substr($docker['status'], 0, 2) == 'Up')
                                                <a data-toggle="modal" data-target="#confirmModal"
                                                   data-action="stop" data-container="{{ $docker['container_id'] }}" data-docker-name="{{ $docker['name'] }}"
                                                   class="btn btn-xs btn-default confirm-modal">
                                                    <i class="fa fa-stop-circle"></i> Stop
                                                </a>
                                            @else
                                                <a data-toggle="modal" data-target="#confirmModal"
                                                   data-action="start" data-container="{{ $docker['container_id'] }}" data-docker-name="{{ $docker['name'] }}"
                                                   class="btn btn-xs btn-default confirm-modal">
                                                    <i class="fa fa-play-circle"></i> Start
                                                </a>
                                            @endif
                                            <a data-toggle="modal" data-target="#confirmModal"
                                               data-action="redo" data-container="{{ $docker['container_id'] }}" data-docker-name="{{ $docker['name'] }}"
                                               data-port="{{ $docker['port'] }}"
                                               class="btn btn-xs btn-default confirm-modal">
                                                <i class="fa fa-refresh"></i> Redo
                                            </a>
                                            <a href="{{ backpack_url('vps/server/docker/config'.'?server_id='.$server_id.'&docker_name='.$docker['name'].'&type=txt') }}" class="btn btn-xs btn-default">
                                                <i class="fa fa-cog"></i> Conf
                                            </a>
                                            <a href="{{ backpack_url('vps/server/docker/config'.'?server_id='.$server_id.'&docker_name='.$docker['name'].'&type=qrcode') }}" class="btn btn-xs btn-default">
                                                <i class="fa fa-qrcode"></i> QR
                                            </a>
                                            @if(is_null($docker['order']))
                                                <a href="{{ backpack_url('order/customer/create'.'?server_id='.$server_id.'&docker_name='.$docker['name']) }}" class="btn btn-xs btn-default">
                                                    <i class="fa fa-first-order"></i> Create
                                                </a>
                                            @endif
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

    <!-- Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h3 class="modal-title"></h3>
                </div>
                <form id="dockerActionForm" method="get">
                    <div class="modal-body">
                        <input type="hidden" name="server_id" value="{{ $server_id }}">
                        <input type="hidden" name="container_id" value="">
                        <input type="hidden" name="docker_name" value="">
                        <h4 class="padding-10"></h4>
                        <h5 style="padding-left: 30px;"></h5>
                        <div id="port-div">
                            <label style="margin: 10px; margin-top: 20px">Port: </label>
                            <input type="number" class="form-control" name="port" min="1024" max="65535" placeholder="1024 ~ 65535"
                                   style="display: inline-block; width: 200px;">
                            <span class="text-red m-l-5"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div id="loading" class="ld-over-full-inverse">
                            <div class="ld ld-ring ld-spin" style="font-size:3em"></div>
                        </div>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button id="modalSubmit" type="submit" class="btn btn-warning ">Yes</button>
                    </div>
                </form>
            </div><!-- /.modal-content -->
        </div><!-- /.modal -->
    </div>

@endsection

@section('after_styles')
    <!-- DATA TABLES -->
    <link href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.1/css/responsive.bootstrap.min.css">

    <link rel="stylesheet" href="{{ asset('vendor/backpack/crud/css/crud.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/backpack/crud/css/show.css') }}">
    <link rel="stylesheet" href="{{ asset('css/loading/loading.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/loading/loading-btn.css') }}"/>
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

            $(document).on("click", ".confirm-modal", function() {
                action = $(this).data('action');
                var url = "{{ backpack_url('vps/server/docker') }}";
                $(".modal-title").html("Docker " + capitalizeFirstLetter(action));
                $(".modal-body h4").html("Are you sure you want to " + capitalizeFirstLetter(action) + " the docker?");
                $("#modalSubmit").html(capitalizeFirstLetter(action));
                $("#dockerActionForm").attr('action', url + "/" + action);
                $('input[name="container_id"]').val($(this).data('container'));
                $('input[name="docker_name"]').val($(this).data('docker-name'));
                if (action == "redo") {
                    $(".modal-body h5").html("The associated user orders wiil be set to Disable and the config will be renewed.");
                    $("#port-div span").text("");
                    $('input[name="port"]').val($(this).data('port'));
                    $("#port-div").show();
                } else {
                    $(".modal-body h5").html("");
                    $("#port-div").hide();
                }
            });

            $("#modalSubmit").click(function (e) {
                e.preventDefault();
                if (action == "redo") {
                    var port = $('input[name="port"]').val();
                    if (port < 1024 || port > 65535) {
                        $("#port-div span").text("Port can only be set between 1024 ~ 65535.");
                        return;
                    }
                }
                $('#loading').toggleClass('running');
                $('#modalSubmit').attr('disabled', true);
                $('#dockerActionForm').submit();
            })
        });

        function capitalizeFirstLetter(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }

    </script>
@endsection
