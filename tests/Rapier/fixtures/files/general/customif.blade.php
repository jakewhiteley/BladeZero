@env('local')
  is local
@elseenv('test')
  is testing
@else
  is neither
@endenv

@unlessenv('production')
  not production
@endenv