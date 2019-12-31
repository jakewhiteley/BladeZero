@for ($i = 0; $i < 6; $i++)
  @continue($i == 1)

  @if($i == 4)
    @continue
  @endif

  {{ $i }}
@endfor