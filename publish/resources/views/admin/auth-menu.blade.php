@extends('layouts.app')

@section('content')
<div class="row">
	<?php $sidebar_section =  App\Module::where('section', 'authorization')->active()->get(); ?>
	@foreach($sidebar_section as $menu)
	    <div class="col-lg-3 col-xs-6">
	          <div class="small-box bg-primary">
	            <div class="inner" >
	              <a href="{{ url($menu->url) }}" style="color: white;">
	                <p>{{ ucwords($menu->name) }}</p>
	              </a>
	            </div>
	          </div>
	        </div>
	@endforeach
</div>
@endsection