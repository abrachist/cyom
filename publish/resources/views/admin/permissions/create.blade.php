@extends('layouts.app')

@section('content')
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Create Permission</div>
                    <div class="panel-body">

                        @if ($errors->any())
                            <ul class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif

                        {!! Form::open(['url' => '/admin/permissions', 'class' => 'form-horizontal']) !!}

                        @include ('admin.permissions.form')

                        {!! Form::close() !!}

                    </div>
                </div>
            </div>
@endsection