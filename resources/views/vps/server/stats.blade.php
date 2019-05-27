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
                                        - %
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
                            Docker Stats
                        </h4>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                        </div>
                    </div>
                    <div class="box-body no-border">
                        <table class="table table-striped table-hover responsive">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Port</th>
                                <th>Status</th>
                                <th>CPU %</th>
                                <th>Mem / Limit</th>
                                <th>Net I/O</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($dockers as $docker)
                                <tr>
                                    <td>
                                        {{ $docker['name'] }}
                                    </td>
                                    <td>
                                        {{ $docker['port'] }}
                                    </td>
                                    <td>
                                        {{ $docker['status'] }}
                                    </td>
                                    <td>
                                        {{ $docker['cpu'] }}
                                    </td>
                                    <td>
                                        {{ $docker['mem'] }}
                                    </td>
                                    <td>
                                        {{ $docker['net'] }}
                                    </td>
                                    <td>

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
    <link rel="stylesheet" href="{{ asset('vendor/backpack/crud/css/crud.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/backpack/crud/css/show.css') }}">
@endsection

@section('after_scripts')
    <script src="{{ asset('vendor/backpack/crud/js/crud.js') }}"></script>
    <script src="{{ asset('vendor/backpack/crud/js/show.js') }}"></script>
@endsection
