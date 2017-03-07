@extends('layouts.app')

@section('content')
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Edit User</div>
                    <div class="panel-body">

                        @if ($errors->any())
                            <ul class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif

                        {!! Form::model($user, [
                            'method' => 'PATCH',
                            'url' => ['/admin/users', $user->id],
                            'class' => 'form-horizontal'
                        ]) !!}

                        @include ('admin.users.form', ['submitButtonText' => 'Update'])

                        {!! Form::close() !!}

                    </div>
                </div>
            </div>
@endsection