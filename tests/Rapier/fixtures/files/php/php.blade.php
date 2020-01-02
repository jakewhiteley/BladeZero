@php
  $foo = 'foo';
@endphp

{{ $foo }}

@php($foo = 'bar')

{{ $foo }}
