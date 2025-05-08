@props(['url', 'banner_path'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ $banner_path }}" class="logo" alt="Sosland Logo" height="120" width="360" style="width: 350px;">
{{ $slot }}
</a>
</td>
</tr>
