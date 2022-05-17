@foreach($data as $datum)
  {{ (new ArrayObject($loop))->count() }}
    {{ $loop->count }}
    {{ $loop->depth }}
    {{ $loop->even ? 'even' : '' }}
    {{ $loop->odd ? 'odd' : '' }}
    {{ $loop->first ? 'first' : '' }}
    {{ $loop->last ? 'last' : '' }}
@endforeach
