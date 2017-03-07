@extends('layouts.app')

@section('content')
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Edit Permission</div>
                    <div class="panel-body">

                        @if ($errors->any())
                            <ul class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif

                        {!! Form::model($permission, [
                            'method' => 'PATCH',
                            'url' => ['/admin/permissions', $permission->id],
                            'class' => 'form-horizontal'
                        ]) !!}

                        @include ('admin.permissions.form', ['submitButtonText' => 'Update'])

                        {!! Form::close() !!}

                    </div>
                </div>
            </div>
@endsection