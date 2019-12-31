@forelse ($users as $user)
  <li>{{ $user }}</li>
@empty
  No users
@endforelse