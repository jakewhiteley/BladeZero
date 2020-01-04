@can('foo')
    passed
@elsecan('bar')
    failed
@endcan

@can('bar')
    failed
@endcan