@php
    $name = $row->name ?? '';
    $slug = \Illuminate\Support\Str::slug($name);
@endphp
<button type="button" class="inline-flex items-center px-2 py-1 text-sm text-gray-400 hover:text-gray-200" title="{{ __('Preview in Aladin') }}" onclick="try{ if(typeof window.__dsl_emitAladinUpdated === 'function'){ window.__dsl_emitAladinUpdated({ objectSlug: '{{ $slug }}' }); } }catch(e){}">
    <i class="fa fa-eye" aria-hidden="true"></i>
</button>
