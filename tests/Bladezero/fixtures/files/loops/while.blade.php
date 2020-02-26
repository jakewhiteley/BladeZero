@while($foo < 6)
  @break($foo == 5)
  {{ $foo }}
  @php($foo++)
@endwhile