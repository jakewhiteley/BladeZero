@auth('subscriber')
  logged in but not admin
@elseauth('admin')
  logged in
@endauth

@guest
  not guest
@elseguest('subscriber')
  is admin
@else
  is logged in
@endguest