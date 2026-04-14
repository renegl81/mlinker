@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
    <img
        src="{{ asset('images/logo-name.png') }}"
        alt="{{ config('app.name') }}"
        style="height: 40px; max-width: 200px; object-fit: contain;"
    />
</a>
</td>
</tr>
