@extends('layouts.global')

@section('content')
<form class="form-horizontal" role="form" method="POST" action="{{ url('/register') }}">
{{ csrf_field() }}
  <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }} has-feedback">
    <input type="text" class="form-control" placeholder="username" name="name" value="{{ old('name') }}">
    <span class="glyphicon glyphicon-user form-control-feedback"></span>
    @if ($errors->has('name'))
        <span class="help-block">
            <strong>{{ $errors->first('name') }}</strong>
        </span>
    @endif
  </div>
  <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }} has-feedback">
    <input type="email" class="form-control" placeholder="Email" name="email" value="{{ old('email') }}">
    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
    @if ($errors->has('email'))
        <span class="help-block">
            <strong>{{ $errors->first('email') }}</strong>
        </span>
    @endif
  </div>
  <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }} has-feedback">
    <input type="password" class="form-control" placeholder="Password" name="password">
    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
    @if ($errors->has('password'))
        <span class="help-block">
            <strong>{{ $errors->first('password') }}</strong>
        </span>
    @endif
  </div>
  <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }} has-feedback">
    <input type="password" class="form-control" placeholder="Retype password" name="password_confirmation">
    <span class="glyphicon glyphicon-log-in form-control-feedback"></span>
    @if ($errors->has('password_confirmation'))
        <span class="help-block">
            <strong>{{ $errors->first('password_confirmation') }}</strong>
        </span>
    @endif
  </div>
  <div class="row">
    <div class="col-xs-8">
      <div class="checkbox icheck">
        <label>
          <input type="checkbox"> I agree to the <a href="#">terms</a>
        </label>
      </div>
    </div>
    <!-- /.col -->
    <div class="col-xs-4">
      <button type="submit" class="btn btn-primary btn-block btn-flat">Register</button>
    </div>
    <!-- /.col -->
  </div>
</form>
@endsection
