@switch($foo)
  @case('foo')
    the var was foo
    @break

  @case('bar')
    the var was bar
    @break

  @default
    the var was neither foo nor bar
@endswitch
