<!-- Sidebar user panel (optional) -->
<div class="user-panel"></div>
<?php $sidebar_section =  App\Module::where('section','=','modules')->groupBy('section')->get(); ?>

<ul class="sidebar-menu"> 
    <li class="treeview"><a href="{{ url('home') }}"><i class="fa fa-check-square-o"></i> <span> Dashboard</span></a></li>
    @foreach($sidebar_section as $section)
    <li class="treeview {{ $section->section }}"><a href="#"><i class="fa fa-check-square-o"></i> <span>{{ ucwords($section->section) }}</span>
        <i class="fa fa-angle-left pull-right"></i></a>
        <?php $sidebar_section =  App\Module::where('section', $section->section)->active()->get(); ?>
        @foreach($sidebar_section as $menu)
            <ul class="treeview-menu">
                <li id="{{$menu->section}}">
                    <a href="{{ url($menu->url) }}">
                        <span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ ucwords($menu->name) }}</span>
                    </a>
                </li>
            </ul>
        @endforeach
    </li>
    @endforeach
</ul> 
    
  
    