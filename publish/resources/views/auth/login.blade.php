@extends('layouts.global')

@section('content')
<form class="form-horizontal" role="form" method="POST" action="{{ url('/login') }}">
{{ csrf_field() }}
  <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }} has-feedback">
    <input type="text" class="form-control" name="name" placeholder="Username" value="{{ old('name') }}">
    <span class="glyphicon glyphicon-user form-control-feedback"></span>
    @if ($errors->has('name'))
        <span class="help-block">
            <strong>{{ $errors->first('name') }}</strong>
        </span>
    @endif
  </div>

  <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }} has-feedback">
    <input type="password" class="form-control" name="password" placeholder="Password">
    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
    @if ($errors->has('password'))
        <span class="help-block">
            <strong>{{ $errors->first('password') }}</strong>
        </span>
    @endif
  </div>

  <div class="row">
    <div class="col-xs-8">
      <div class="checkbox icheck">
        <label>
          <input type="checkbox"> Remember Me
        </label>
      </div>
    </div>
    <!-- /.col -->
    <div class="col-xs-4">
      <button type="submit" class="btn btn-primary btn-block btn-flat">Login</button>
    </div>
    <!-- /.col -->
  </div>
</form> 
<br><br>
<a href="{{ url('/register') }}" class="text-center">Register a new user</a>           
@endsection
